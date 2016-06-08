<?php
namespace Acloneio;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Thread;

class Vector {
    public $th, $x, $y, $mag;
    public $normalized;
    
    private function calMag() {
        $this->mag = sqrt( ($this->x)*($this->x) + ($this->y)*($this->y) );
    }
    
    private function calTh() {
        $this->th = atan2($this->y, $this->x);
    }
    
    private function calCartesian() {
        $this->x = ($this->mag*cos($this->th));
        $this->y =  ($this->mag*sin($this->th));
    }
    
    public function __construct($dir, $Mag) {
        $this->th = $dir;
        $this->mag = $Mag;
        calCartesian();
    }

/*  public function __construct($xi, $yi, $xf, $yf){
        $this->x = $xf-$xi; $this->y = $yf-$yi;
        calMag();
        calTh();
    }*/
  
    public function invert() {
        Scale(-1);
        calMag();
        calTh();
        Normalize();
    }

    private function Normalize() {
        calMag();
        $this->normalized = new Vector($this->x/$this->mag, $this->y/$this->mag);
    }

    public function Scale($c) {
        $this->x = $this->x*$c;
        $this->y = $this->y*$c;
        calMag();
        calTh();
    }
    
    public function getNormalized() {
        Normalize();
        return $this->normalized;
    }

    public function getMag() {
        return $this->mag;
    }
    
    public function add($X, $Y) {
        $this->x += $X;
        $this->y += $Y;
        
        calMag();
        calTh();
    }   
}

class Player {
    public $ci;     // connection inteface
    public $pos, $speed;        // Vectors
    public $size;
    public $name;
    public $player_id;      // same as $ci->resourceId

    public function __construct(ConnectionInterface $conn, $initx, $inity) {
        $this->ci = $conn;
        $this->player_id = $conn->resourceId;
        $this->pos = new Vector($initx, $inity);
        $this->size = 1;
    }

    public function send($data) {    // send $data via $ci
        $this->ci->send($data);
    }

    public function move() {  
        $this->pos->add($this->speed);
    }

    public function updateDirection(Vector $dir) { // dir should be already normalized
        $this->speed = $dir->Scale($this->speed->getMag());
    }

    public function getData() {     // encode the player's fields as a string
        $data_string = strval($this->player_id).','.strval($this->pos->x).','
                      .strval($this->pos->y).','.strval($this->size);
        return $data_string;
    }

/*    public function jsonSerialize() {
            $properties = array($this->x, $this->y);
            array_push($properties, $this->arr);
            return $properties;
    }*/
}

class BroadcastThread extends Thread {   // using this class as timer to broadcast updates to players
    private $players;   // SplObjectStorage

    public function __construct(&$plyrs) {
        $this->players = $plyrs;
    }

    public function run() {
            $data = array();
            foreach ($this->players as $player) 
                array_push($data, $player->getData());
            $data_string = implode('-', $data);
            foreach ($this->players as $player) 
                $player->send($data_string);
            usleep(100);            // wait 100 ms before next call
    }
}

class ConnectionClass extends Thread implements MessageComponentInterface {
    protected $players;
    private $bcast_thread;

    public function __construct() {
        $this->players = new \SplObjectStorage;            
       // $timer = EvTimer::createStopped(0,100, broadcast);
        $this->bcast_thread = new BroadcastThread($this->players);
        $this->bcast_thread->start();           // start the broadcasting thread
        $this->start();                         // start the thread associatied with this class (physics thread)
    }

    public function onOpen(ConnectionInterface $conn) {     // A new plyer/connection is established
        //  initial player position in the world
        $initx = rand(-500,500);  $inity = rand(-500,500);         
        $new_player = new Player($conn,$initx,$inity);
        $this->players->attach($new_player);

        $conn->send(strval($conn->resourceId).','       // first thing to send is id,initx,inity
              .strval(rand($initx)).",".strval(rand($inity)));  

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {   // a message has arrived from player with connection inteface $from
        $numRecv = max(count($this->players) - 1, 0);
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $dir = explode($msg, ',');
        foreach ($this->players as $player) {
            if($player->player_id == $from->resourceId)
                 $player->updateDirection(new Vector($dir[0], $dir[1]));
        }
    }

    public function onClose(ConnectionInterface $conn) {    // connection with player is lost/closed
        foreach ($this->players as $player) 
            if($player->player_id == $conn->resourceId)
                 $this->players->detach($player);
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }


    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function run() { // this class's thread: update world, physics...
        foreach ($this->players as $player) 
            $player->move();
    }

    /*private function broadcast() { // send data to clients
        $data = array();
        foreach ($this->players as $player) 
            array_push($data, $player->getData());
        $data_string = implode('-', $data);
        foreach ($this->players as $player) 
            $player->send($data_string); 
    }*/

}
