<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require dirname(__DIR__)."/classes/KalamburySpectator.php";
require dirname(__DIR__)."/classes/KalamburyRoom.php";

class Engine implements MessageComponentInterface {
    protected $clients;
    private $roomId;
    private $userId;
    private $login;
    private $page;
    private $spectator_object;
    private $room_object;

    private $state = array();

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->spectator_object = new \KalamburySpectator;
        $this->room_object = new \Room;
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

        $data = json_decode($msg, true);
        $this->roomId = $data['roomid'];
        $this->userId = $data['userid'];
        $this->page = $data['page'];
        if(!empty($data['login']))
            $this->login = $data['login'];

        $this->room_object->setRoomId($this->roomId);
        $this->room_object->setGameName($this->page);
        $this->room_object->setTableName();

        if($data['type']=='pagejoin' && $data['roomid']!=0)
        {
            echo date("Y-m-d H:i:s")." User ".$this->userId." has joined the Room ".$this->roomId." (".$this->page.").\n";
            $this->spectator_object->setRoomId($data['roomid']);
            $this->spectator_object->setSpectatorId($data['userid']);
            $this->spectator_object->setConnectionId($from->resourceId);
            $this->spectator_object->insertData();

            //dodanie nowego rooma do state
            $userData['id'] = $this->userId;
            $userData['login'] = $this->login;
            $userData['connection_id'] = $from->resourceId;
            array_push($this->state[$this->page][$this->roomId]['players'], $userData);
            print_r($this->state);
            
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

            $this->state['kalambury'][$newRoomId]['players'] = array();
            
            foreach ($this->clients as $client) {
                $client->send($msg);
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
        else if($data['type']=='message' && $data['roomid']!=0 && $data['page']=='kalambury')
        {
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send($msg);
                }
            }
        }
        else if($data['type']=='createRoom')
        {
            //dodanie do bazy nowego rooma
            $newRoomId = $this->room_object->getEmptyRoomId();
            $this->room_object->setRoomId($newRoomId);
            $this->room_object->setCreatorId($this->userId);
            $this->room_object->addRoom();

            //tworzenie pustej tablicy w zmiennej state
            $this->state[$this->page][$newRoomId]['players'] = array();

            //redirect
            $red = array("type"=>"redirect", "roomid"=>$newRoomId, "page"=>$this->page);
            $jsonData = json_encode($red);
            $from->send($jsonData);
        }
        else if($data['type']=='kalambury_gamestate')
        {
            if($data['action']=='createGame')
            {
                $this->gamestate_object->setRoomId($this->roomId);
                if($this->gamestate_object->createGame())
                    echo date("Y-m-d H:i:s")." Successfully started game of ".$data['page']." in room ".$data['roomid'].".\n";
                else
                    echo date("Y-m-d H:i:s")." Error: Couldn't start game of ".$data['page']." in room ".$data['roomid'].".\n";
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        
        $this->spectator_object->setConnectionId($conn->resourceId);
        $room_by_conn = $this->spectator_object->getRoomIdByConnectionId();
        if(empty($room_by_conn))
            $this->roomId = 0;
        else
            $this->roomId = $room_by_conn[0]['room_id'];

        if($this->roomId != 0)
        {
            $res = $this->spectator_object->getLoginByConnectionId($conn->resourceId);
            echo date("Y-m-d H:i:s")." Deleting data for connection ".$conn->resourceId.".\n";
            $this->spectator_object->deleteData();

            //deleting room if nobody left or changing creator to another player
            $this->room_object->setRoomId($this->roomId);
            $this->room_object->onPlayerLeave();

            foreach($this->state['kalambury'][$this->roomId]['players'] as $key => $element)
            {
                if($element['connection_id']==$conn->resourceId)
                {
                    unset($this->state['kalambury'][$this->roomId]['players'][$key]);
                    if(count($this->state['kalambury'][$this->roomId]['players'])==0)
                        unset($this->state['kalambury'][$this->roomId]);
                    if(count($this->state['kalambury'])==0)
                        unset($this->state['kalambury']);
                    print_r($this->state);
                    break;
                }
            }

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