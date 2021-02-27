<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

    require dirname(__DIR__) . '/vendor/autoload.php';

    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Chat()
            )
        ),
        8080
    );

    echo date("Y-m-d H:i:s")." Server started successfully.\n";
    $server->run();

?>