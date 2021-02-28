<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require dirname(__DIR__)."/classes/KalamburySpectator.php";

class Chat implements MessageComponentInterface {
    protected $clients;
    private $roomId;
    private $userId;
    private $login;
    private $page;
    private $spectator_object;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $data = json_decode($msg, true);
        $this->roomId = $data['roomid'];
        $this->userId = $data['userid'];
        $this->login = $data['login'];
        $this->page = $data['page'];

        if($data['type']=='pagejoin' && $data['roomid']!=0)
        {
            echo "Adding data for room ".$this->roomId." and spectator ".$this->userId."\n";
            $this->spectator_object = new \KalamburySpectator;
            $this->spectator_object->setRoomId($data['roomid']);
            $this->spectator_object->setSpectatorId($data['userid']);
            $this->spectator_object->setConnectionId($from->resourceId);
            $this->spectator_object->insertData();
        }
        else if($data['type']=='pageleave' && $data['roomid']!=0)
        {
            echo "leaving room";
        }
        foreach ($this->clients as $client) {
            //if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            //}
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        if($this->roomId != 0)
        {
            $this->spectator_object->setConnectionId($conn->resourceId);
            $res = $this->spectator_object->getLoginByConnectionId($conn->resourceId);
            echo "Spectator ID to delete: ".$this->spectator_object->getConnectionId()."\n";
            echo "Deleting data for connection ".$conn->resourceId."\n";
            $this->spectator_object->deleteData();

            //echo $res[0]['login'];
            $data = array("type"=>"pageleave", "login"=>$res[0]['login']);
            $jsonData = json_encode($data);
            
            foreach ($this->clients as $client) {
                    $client->send($jsonData);
            }
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}

?>