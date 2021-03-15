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
    /*  zmienna globalna przechowująca aktualny stan serwisu.
    /   Struktura:
    /   state = {
    /            [gameName] = {
    /                            [roomId] = {
    /                                            [players] = {
    /                                                            [key] = {
    /                                                                        [id]
    /                                                                        [login]
    /                                                                        [connection_id]
    /                                                                    }
    /                                                        }
    /                                           [game_status]
    /                                        }
    /                         }
    /            }
    /
    */

    public function __construct() {
        //$this->clients = new \SplObjectStorage;
        $this->clients = [];
        $this->spectator_object = new \KalamburySpectator;
        $this->room_object = new \Room;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        //$this->clients->attach($conn);
        $this->clients[$conn->resourceId] = $conn;

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
            //$userData['id'] = $this->userId;
            //$userData['login'] = $this->login;
            //$userData['connection_id'] = $from->resourceId;
            //array_push($this->state[$this->page][$this->roomId]['players'], $userData);
            $this->state[$this->page][$this->roomId]['players'][$this->userId]['login'] = $this->login;
            $this->state[$this->page][$this->roomId]['players'][$this->userId]['connection_id'] = $from->resourceId;
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
            //redirect
            if($this->userId == "" || $this->userId == 0)
            {
                $_SESSION['error'] = "test";//$lang['e_login_to_create'];
                $red = array("type"=>"redirect", "page"=>$this->page, "pageto"=>"login");
            }
            else
            {
                //dodanie do bazy nowego rooma
                $newRoomId = $this->room_object->getEmptyRoomId();
                $this->room_object->setRoomId($newRoomId);
                $this->room_object->setCreatorId($this->userId);
                $this->room_object->addRoom();

                //tworzenie pustej tablicy w zmiennej state
                $this->state[$this->page][$newRoomId]['players'] = array();
                $red = array("type"=>"redirect", "roomid"=>$newRoomId, "page"=>$this->page);
            }
            $jsonData = json_encode($red);
            $from->send($jsonData);
        }
        else if($data['type']=='action')
        {
            if($data['action']=='startGame')
            {
                if($this->getGameStatus($this->page, $this->roomId)!="started")
                {
                    echo date("Y-m-d H:i:s")." Successfully started game of ".$data['page']." in room ".$data['roomid'].".\n";
                    $this->onKalamburyStart($this->roomId);
                }
                else
                {
                    echo date("Y-m-d H:i:s")." Error: Game of ".$data['page']." in room ".$data['roomid']." already started.\n";
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        //$this->clients->detach($conn);
        unset($this->clients[$conn->resourceId]);
        
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

    /* -------------------------------------------------------------------
                        GETTERY I SETTERY ZMIENNEJ STATE
    ------------------------------------------------------------------- */

    public function setGameStatus($gameName, $roomId, $status)
    {
        //$status = not_started/started/finished
        $this->state[$gameName][$roomId]['game_status'] = $status;
    }

    public function getGameStatus($gameName, $roomId)
    {
        if(isset($this->state[$gameName][$roomId]['game_status']))
            return $this->state[$gameName][$roomId]['game_status'];
        else
            return false;
    }

    public function setPoints($gameName, $roomId, $playerId, $points)
    {
        $this->state[$gameName][$roomId]['players'][$playerId]['points'] = $points;
    }

    public function getPoints($gameName, $roomId, $playerId)
    {
        if(isset($this->state[$gameName][$roomId]['players'][$playerId]['points']))
            return $this->state[$gameName][$roomId]['players'][$playerId]['points'];
        else
            return false;
    }

    public function addPoints($gameName, $roomId, $playerId, $points)
    {
        $this->state[$gameName][$roomId]['players'][$playerId]['points'] += $points;
    }

    public function getUserIdByConnectionId($gameName, $roomId, $connectionId)
    {
        foreach($this->state[$gameName][$roomId]['players'] as $key => $element)
        {
            if($element['connection_id']==$connectionId)
            {
                return $key;
                break;
            }
        }
        return false;
    }

    public function getConnectionIdByUserId($gameName, $roomId, $userId)
    {
        foreach($this->state[$gameName][$roomId]['players'] as $key => $element)
        {
            if($key==$userId)
            {
                return $element['connection_id'];
                break;
            }
        }
        return false;
    }

    /* -------------------------------------------------------------------
                            SILNIK KALAMBURY
    ------------------------------------------------------------------- */

    public function onKalamburyStart($roomId)
    {
        $this->setGameStatus("kalambury",$roomId, "started");

        //ustawienie wszystkim graczom w pokoju po 0 pkt
        foreach($this->state["kalambury"][$roomId]['players'] as $key => $element)
        {
            $this->setPoints("kalambury", $roomId, $key, 0);
        }

        //set word
        $this->setWord($roomId);

        //select and set drawer
        $drawers = $this->getDrawersList($roomId);
        $currentDrawer = $drawers[0];
        $this->setDrawer($roomId, $currentDrawer);

        print_r($this->state);

        //send word to drawer
        $this->sendWordToDrawer($roomId, $this->getWord($roomId), $currentDrawer);
    }

    public function sendWordToDrawer($roomId, $word, $drawerId)
    {
        $connectionId = $this->getConnectionIdByUserId("kalambury", $roomId, $drawerId);

        $json = array("type"=>"action", "action"=>"wordToDraw" ,"roomid"=>$roomId, "page"=>$this->page, "word"=>$word);
        $jsonData = json_encode($json);
        $this->clients[$connectionId]->send($jsonData);
    }

    public function setDrawer($roomId, $drawerId)
    {
        $this->state['kalambury'][$roomId]['drawer'] = $drawerId;
    }

    public function getDrawer($roomId)
    {
        if(isset($this->state['kalambury'][$roomId]['drawer']))
            return $this->state['kalambury'][$roomId]['drawer'];
        else
            return false;
    }

    public function getDrawersList($roomId)
    {
        $list = array();
        foreach($this->state['kalambury'][$roomId]['players'] as $key => $element)
        {
            array_push($list, $key);
        }
        return $list;
    }

    public function setWord($roomId)
    {
        $words = array("kasztan", "jabłko", "banan", "gruszka", "samochód", "lalka", "szalik", "bałwan", "dom", "banknot");
        $word = $words[rand(0,9)];
        $this->state['kalambury'][$roomId]['word'] = $word;
    }

    public function getWord($roomId)
    {
        if(isset($this->state['kalambury'][$roomId]['word']))
            return $this->state['kalambury'][$roomId]['word'];
        else
            return false;
    }
}

?>