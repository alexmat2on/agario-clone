<?php
namespace Acloneio;
use Thread;
use Acloneio\Player;
//use Acloneio\PlayersContainer;

class World extends Thread { 

    private $pc;

    public function __construct(ArrayObject $PC) {

        $this->pc = $PC;  
        $this->start(); 
    }

    public function run() {
        while(true){
            //echo sprintf("ok");
            //$players = $this->players;
           // $temp = $this->pc->get();
          //  foreach ($temp as $player) 
          //      $player->move();
           // $this->pc->set($temp);
            usleep(30000);
        }
    }

}