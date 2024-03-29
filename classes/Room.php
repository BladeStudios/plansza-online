<?php

class Room
{
    protected $connection;
    private $room_id;
    private $creator_id;
    protected $tableName;
    protected $gameName;

    public function __construct()
    {
        require_once('DatabaseConnection.php');
        $db_connection = new DatabaseConnection;
        $this->connection = $db_connection->connect();
    }

    function getRoomId() { return $this->room_id; }

    function setRoomId($room_id) { $this->room_id = $room_id; }

    function getCreatorId() { return $this->creator_id; }

    function setCreatorId($creator_id) { $this->creator_id = $creator_id; }

    function getGameName() { return $this->gameName; }

    function setGameName($game_name) { $this->gameName = $game_name; }

    function getTableName() { return $this->tableName; }

    function setTableName() { $this->tableName = $this->gameName."_rooms"; }

    function updateCreator()
    {
        $sql = "
        UPDATE ".$this->tableName."
        SET creator_id = ".$this->creator_id."
        WHERE room_id = ".$this->room_id."
        ";

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
            return true;
        else
            return false;
    }

    function addRoom()
    {
        $sql = "
        INSERT INTO ".$this->tableName." (room_id, creator_id)
        VALUES (".$this->room_id.",".$this->creator_id.")
        ";

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
        {
            //echo date("Y-m-d H:i:s")." User ".$this->creator_id." created Room ".$this->room_id."\n";
            return true;
        }
        else
            return false;
    }

    function getEmptyRoomId()
    {
        $sql = "SELECT room_id FROM ".$this->tableName;

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
        {
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        $array = array();

        foreach($result as $element)
            array_push($array, $element['room_id']);

        $empty = 1;
        for($i=0; $i<count($array); $i++)
        {
            if($empty==$array[$i])
                $empty++;
            else
                break;
        }

        return $empty;
    }

    //returns true if room with certain ID exists in rooms table in database, otherwise returns false
    function isRoomCreated()
    {
        $sql = "SELECT room_id FROM ".$this->tableName;

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
        {
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        $array = array();

        foreach($result as $element)
            array_push($array, $element['room_id']);

        $exist = false;
        for($i=0; $i<count($array); $i++)
        {
            if($this->room_id==$array[$i])
            {
                $exist = true;
                break;
            }
        }

        return $exist;
    }

    function getSpectatorsIds() //returns array of all spectators from current room
    {
        $sql = "SELECT spectator_id FROM ".$this->gameName."_spectators
                WHERE room_id = ".$this->room_id;

        $statement = $this->connection->prepare($sql);
        if($statement->execute())
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $arr = array();
        foreach($result as $el)
        {
            array_push($arr,$el['spectator_id']);
        }
        return $arr;
    }
    
    function deleteRoom()
    {
        $sql = "DELETE FROM ".$this->tableName."
                WHERE room_id = ".$this->room_id;
        
        $statement = $this->connection->prepare($sql);
        if($statement->execute())
            return true;
        else
            return false;    
    }

    function onPlayerLeave()
    {
        //check if any other players are in room
        $spectators_ids = $this->getSpectatorsIds();
        //$how_many = count($spectators_ids);

        //if($how_many==0)
        if(empty($spectators_ids))
        {
            $this->deleteRoom();
            echo date("Y-m-d H:i:s")." Deleted room ".$this->room_id." (".$this->gameName.").\n";
            return true;
        }
        else if(!in_array($this->getCreatorById(),$spectators_ids)) //if room creator left the room
        {
            $sql = "UPDATE ".$this->tableName."
            SET creator_id = ".$spectators_ids[0]."
            WHERE room_id = ".$this->room_id;
            $statement = $this->connection->prepare($sql);
            if($statement->execute())
            {
                echo date("Y-m-d H:i:s")." Changed creator to User ".$spectators_ids[0]." in room ".$this->room_id.".\n";
                return true;
            }
        }
        return false;
    }

    function getRoomsList() //returns array of small arrays with data like (1,5) where 1 is room_id and 5 is amount of spectators
    {
        $sql = "SELECT room_id FROM ".$this->tableName;
        $statement = $this->connection->prepare($sql);
        if($statement->execute())
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        $rooms_list = array();
        foreach($result as $data)
            array_push($rooms_list, $data['room_id']);
        print_r($rooms_list);
    }

    function getCreatorById()
    {
        $sql = "SELECT creator_id FROM ".$this->tableName." WHERE room_id=".$this->room_id;
        $statement = $this->connection->prepare($sql);
        if($statement->execute())
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result[0]['creator_id'];
    }
}

?>