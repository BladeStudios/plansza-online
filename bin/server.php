<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__)."/classes/DatabaseConnection.php";

    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Chat()
            )
        ),
        8080
    );
    $conn = new DatabaseConnection;
    $connection = $conn->connect();

    $tables_to_clear = array('kalambury_rooms', 'kalambury_spectators', 'kalambury_players');

    foreach($tables_to_clear as $table_name)
    {
        $sql = "TRUNCATE TABLE ".$table_name;
        $statement = $connection->prepare($sql);
        if($statement->execute())
        {
            echo date("Y-m-d H:i:s")." Deleted all rows from table: ".$table_name."\n";
        }
    }

    echo date("Y-m-d H:i:s")." Server started successfully.\n";
    $server->run();

?>