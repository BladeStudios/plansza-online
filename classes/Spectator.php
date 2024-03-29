<?php

class Spectator
{
    protected $connection;
    private $room_id;
    private $spectator_id;
    private $connection_id;
    protected $tableName;
    protected $gameName;

    public function __construct()
    {
        require_once('DatabaseConnection.php');
        $db_connection = new DatabaseConnection;
        $this->connection = $db_connection->connect();
        //$this->tableName = "";
    }

    function getRoomId() { return $this->room_id; }

    function setRoomId($room_id) { $this->room_id = $room_id; }

    function getSpectatorId() { return $this->spectator_id; }

    function setSpectatorId($spectator_id) { $this->spectator_id = $spectator_id; }

    function getConnectionId() { return $this->connection_id; }

    function setConnectionId($connection_id) { $this->connection_id = $connection_id; }

    function updateSpectator()
    {
        $sql = "
        UPDATE ".$this->tableName."
        SET spectator_id = ".$this->spectator_id.", connection_id = ".$this->connection_id."
        WHERE room_id = ".$this->room_id."
        ";

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
            return true;
        else
            return false;
    }

    function insertData()
    {
        $sql = "
        INSERT INTO ".$this->tableName." (room_id, spectator_id, connection_id)
        VALUES (".$this->room_id.",".$this->spectator_id.",".$this->connection_id.")
        ";

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
            return true;
        else
            return false;
    }

    function deleteData()
    {
        $sql = "
        DELETE FROM ".$this->tableName."
        WHERE connection_id = ".$this->connection_id."
        ";

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
            return true;
        else
            return false;
    }

    function getLoginByConnectionId($connection_id)
    {
        $sql = "
        SELECT * FROM users WHERE id = 
        (SELECT spectator_id FROM ".$this->tableName."
        WHERE connection_id = ".$connection_id.")
        ";

        $statement = $this->connection->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    function getSpectators() //returns array of all spectators from current room
    {
        $sql = "SELECT login FROM users
                WHERE id IN
                (SELECT spectator_id FROM ".$this->tableName."
                WHERE room_id = ".$this->room_id.")";

        $statement = $this->connection->prepare($sql);
        if($statement->execute())
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $spectators = array();

        foreach($result as $key => $data)
        {
            array_push($spectators, $data['login']);
        }
        
        return $spectators;
    }

    function getRoomsList()
    {
        $sql = "SELECT room_id, count(room_id) AS spectators FROM ".$this->tableName."
        GROUP BY room_id ASC";

        $statement = $this->connection->prepare($sql);
        if($statement->execute())
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        return $result;
    }

    function getRoomIdByConnectionId()
    {
        $sql = "SELECT room_id FROM ".$this->tableName." WHERE connection_id=".$this->connection_id;
        $statement = $this->connection->prepare($sql);
        if($statement->execute())
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        return $result;
    }
}

?>