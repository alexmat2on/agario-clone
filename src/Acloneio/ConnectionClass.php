<?php
namespace Acloneio;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Thread;


class Vector{
    public $th , $x, $y, $mag;
    public $normalized;
    
    private function calMag(){
        $this->mag = sqrt( ($this->x)*($this->x) + ($this->y)*($this->y) );
    }
    
    private function calTh(){
        $this->th = atan2($this->y, $this->x);
    }
    
    private function calCartesian() {
        $this->x = ($this->mag*cos($this->th));
        $this->y =  ($this->mag*sin($this->th));
    }
    
    public function __construct($dir, $Mag){
        $this->th = $dir;
        $this->mag = $Mag;
        calCartesian();
    }

/*
    public function __construct($xi, $yi, $xf, $yf){
        $this->x = $xf-$xi; $this->y = $yf-$yi;
        calMag();
        calTh();
    }
  */  
    public function invert(){
        Scale(-1);
        calMag();
        calTh();
        Normalize();
    }

    private function Normalize(){
        calMag();
        $this->normalized = new Vector($this->x/$this->mag, $this->y/$this->mag);
    }

    public function Scale($c){
        $this->x = $this->x*$c;
        $this->y = $this->y*$c;
        calMag();
        calTh();
    }
    
    public function getNormalized(){
        Normalize();
        return $this->normalized;
    }

    public function getMag(){
        return $this->mag;
    }
    
    public function add($X, $Y){
        $this->x += $X;
        $this->y += $Y;
        
        calMag();
        calTh();
    }   
}

class Player {
    public $ci;
    public $pos, $speed;
    public $size;
    public $name;
    public $player_id;

    public function __construct(ConnectionInterface $conn) {
        $this->ci = $conn;
        $this->player_id = $conn->resourceId;
        $this->size = 1;
    }

    public function send($data_string) {
        $this->ci->send($data_string);
    }

    public function move() {  
        $this->pos->add($this->speed);
    }

    public function updateDirection(Vector $dir) { // dir should be already normalized
        $this->speed = $dir->Scale($this->speed->getMag());
    }
/*
    public function jsonSerialize() {
            $properties = array($this->x, $this->y);
            array_push($properties, $this->arr);
            return $properties;
    }*/

    public function getData() {
        $data_string = strval($this->player_id).','.strval($this->pos->x).','
                      .strval($this->pos->y).','.strval($this->size);
        return $data_string;
    }

}


class ConnectionClass extends Thread implements MessageComponentInterface {
    protected $players;
    private $timer;

    public function __construct() {
        $this->players = new \SplObjectStorage;
        $timer = EvTimer::createStopped(0,100, broadcast);
        $this->start();
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $new_player = Player($conn);
        $this->players->attach($new_player);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->players) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $dir = explode($msg, ',');
        foreach ($this->players as $player) {
            if($player->player_id == $from->resourceId)
                 $player->updateDirection(new Vector($dir[0], $dir[1]));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        foreach ($this->players as $player) {
            if($player->player_id == $conn->resourceId)
                 $this->players->detach($player);
        }
        echo "Connection {$conn->resourceId} has disconnected\n";
    }


    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function broadcast() { // send data to clients
        $data = array();
        foreach ($this->players as $player) 
            array_push($data, $player->getData());
        $data_string = implode('-', $data);
        foreach ($this->players as $player) 
            $player->send($data_string);
        
    }

    public function run() { // update world, players ...
        foreach ($this->players as $player)
            $player->move();
    }
}
