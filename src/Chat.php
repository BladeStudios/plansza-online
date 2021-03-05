<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require dirname(__DIR__)."/classes/KalamburySpectator.php";
require dirname(__DIR__)."/classes/KalamburyRoom.php";

class Chat implements MessageComponentInterface {
    protected $clients;
    private $roomId;
    private $userId;
    private $login;
    private $page;
    private $spectator_object;
    private $room_object;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo date("Y-m-d H:i:s")." Connection {$conn->resourceId} has connected.\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo date("Y-m-d H:i:s")."".sprintf(' Connection %d sending message "%s" to %d other connection%s.' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $this->spectator_object = new \KalamburySpectator;

        $data = json_decode($msg, true);
        $this->roomId = $data['roomid'];
        $this->userId = $data['userid'];
        $this->page = $data['page'];
        if(!empty($data['login']))
            $this->login = $data['login'];

        if($data['type']=='pagejoin' && $data['roomid']!=0)
        {
            echo date("Y-m-d H:i:s")." User ".$this->userId." has joined the Room ".$this->roomId." (kalambury).\n";
            $this->spectator_object->setRoomId($data['roomid']);
            $this->spectator_object->setSpectatorId($data['userid']);
            $this->spectator_object->setConnectionId($from->resourceId);
            $this->spectator_object->insertData();

            $this->room_object = new \KalamburyRoom;
            $this->room_object->setRoomId($data['roomid']);
            
            foreach ($this->clients as $client) {
                //if ($from !== $client) {
                    // The sender is not the receiver, send to each client connected
                    $client->send($msg);
                //}
            }
        }
        else if($data['type']=='pageleave' && $data['roomid']!=0)
        {
            echo "Leaving room.";
            
            foreach ($this->clients as $client) {
                //if ($from !== $client) {
                    // The sender is not the receiver, send to each client connected
                    $client->send($msg);
                //}
            }
        }
        else if ($data['type']=='mousemove' && $data['roomid']!=0 && $data['page']=='kalambury')
        {
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send($msg);
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        
        $this->spectator_object->setConnectionId($conn->resourceId);
        $room_by_conn = $this->spectator_object->getRoomIdByConnectionId();
        echo " ROOM PRZED: ".$this->roomId."\n";
        if(empty($room_by_conn))
            $this->roomId = 0;
        else
            $this->roomId = $room_by_conn[0]['room_id'];
        echo " ROOM PO: ".$this->roomId."\n";

        if($this->roomId != 0)
        {
                $res = $this->spectator_object->getLoginByConnectionId($conn->resourceId);
                echo date("Y-m-d H:i:s")." Deleting data for connection ".$conn->resourceId.".\n";
                $this->spectator_object->deleteData();

                //deleting room if nobody left or changing creator to another player
                $this->room_object->setRoomId($this->roomId);
                $this->room_object->onPlayerLeave();

            $data = array("type"=>"pageleave", "login"=>$res[0]['login'], "roomid"=>$this->roomId, "page"=>$this->page);
            $jsonData = json_encode($data);
            
            foreach ($this->clients as $client) {
                    $client->send($jsonData);
            }
        }

        echo date("Y-m-d H:i:s")." Connection {$conn->resourceId} has disconnected.\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}

?>