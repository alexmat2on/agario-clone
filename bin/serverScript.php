<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Acloneio\ConnectionClass;
use Acloneio\Player;
use Acloneio\World;
use Acloneio\PlayersContainer;
use Ratchet\ConnectionInterface;

    require dirname(__DIR__) . '/vendor/autoload.php';

    
    
    $pc = new ArrayObject();
    asdasdsasdad c123q2156
    //$world = new World($pc);
    //$con_class = new ConnectionClass($pc);
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                 //$con_class
                new ConnectionClass($pc)
            )
        ),
        8080
    );

    $server->run();
