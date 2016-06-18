<?php
namespace Acloneio;
use Ratchet\ConnectionInterface;
use Acloneio\Vector;

class Player {
    public $ci;     // connection inteface
    public $pos, $speed;        // Vectors
    public $size;
    public $name;
    public $pid;      // same as $ci->resourceId

    public function __construct(ConnectionInterface $conn, $initx, $inity) {
        $this->ci = $conn;
        $this->pid = $conn->resourceId;
        $this->pos = new Vector($initx, $inity);
        $this->speed = new Vector(1,1);
        $this->size = 1;
    }

    public function send($data) {    // send $data via $ci
        $this->ci->send($data);
    }

    public function move() {  
        $this->pos->add($this->speed);
    }

    public function updateDirection(Vector $dir) { // dir should be already normalized
        echo "\tpid ".$this->pid."\n";
        $scalar = $this->speed->getMag();
        $this->speed = new Vector($dir->x, $dir->y);
        $this->speed->Scale($scalar);
       // echo sprintf($this->ci->resourceId." pos: ".strval($this->pos->x).", ".strval($this->pos->x)."\n");
    }

    public function getData() {     // encode the player's fields as a string
        $data_string = strval($this->pid).','.strval($this->pos->x).','
                      .strval($this->pos->y).','.strval($this->size);
        return $data_string;
    }
}