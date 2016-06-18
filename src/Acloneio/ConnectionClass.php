<?php
namespace Acloneio;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Acloneio\Player;
use Acloneio\Vector;

use Thread;



class ConnectionClass /*extends Thread*/ implements MessageComponentInterface {
    private $pc;
    private $mn = 0;
    public function __construct(\ArrayObject $PC) {
        $this->pc = $PC;     
       // $this->start();
    }

    public function onOpen(ConnectionInterface $conn) {     // A new plyer/connection is established
        //  initial player position in the world
        $initx = rand(0,200);  $inity = rand(0,100);         

        $this->pc->append(new Player($conn, $initx, $inity));
        
        $conn->send(strval($conn->resourceId).','       // first thing to send is id,initx,inity
              .strval($initx).','.strval($inity));  

       // echo "New connection! ({$conn->resourceId})\n";
        //echo "# of players: ".$this->pc->count();
    }

    public function onMessage(ConnectionInterface $from, $msg) {   // a message has arrived from player with connection inteface $from
        //$numRecv = max(count($this->players) - 1, 0);
        //echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            // , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
        $this->mn++;
        echo "------ MSG ".$this->mn." ------\n";

        $dir = explode($msg, ',');

        for($ii = 0; $ii < $this->pc->count(); $ii++) {
            echo "begin \$ii = ".$ii."\n";
            if($this->pc->offsetGet($ii)->pid == $from->resourceId) {
                 echo sprintf("\t".$this->pc->offsetGet($ii)->ci->resourceId.": ".$msg."\n");
                 $this->pc->offsetGet($ii)->updateDirection(new Vector((double)$dir[0], (double)$dir[1]));
             }
            echo "end   \$ii = ".$ii."\n";
        }

        echo "-------------------\n\n";

    }

    public function onClose(ConnectionInterface $conn) {    // connection with player is lost/closed
       /* foreach ($this->pc as $player) 
            if($player->player_id == $conn->resourceId)
                 $this->pc->unset(var)($player);*/
        
        for($i = 0; $i < $this->pc->count(); $i++) {
            if($this->pc->offsetGet($i)->pid == $conn->resourceId)
                 $this->pc->offsetUnset($i);
        }
        echo "Connection {$conn->resourceId} has disconnected\n";
    }


    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }


    public function run() { 
       // while(true) {
            //$data = array();
            /*
            foreach ($this->pc->get() as $player) {
                array_push($data, $player->getData());
                echo sprintf($player->getData()."\n");
            }
            

            for($i =0; $i < $this->pc->count(); $i++) {
               ;// array_push($data, $this->pc->offsetGet($i)->getData());
            }

            $data_string = implode('-', $data);
            /*foreach ($this->pc->get() as $player)
                $player->send($data_string);
            


            for($i =0; $i < $this->pc->count(); $i++) {
               // $this->pc->offsetGet($i)->send($data_string);

            }
            echo var_dump($this->pc->count());*/
           // usleep(1000000);            
      //  }
            ;
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

