<?php

class Room
{
    protected $connection;
    private $room_id;
    private $creator_id;
    protected $tableName;

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

    function updateCreator()
    {
        $sql = "
        UPDATE ".$this->$tableName."
        SET creator_id = ".$this->creator_id."
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
        INSERT INTO ".$this->$tableName." (room_id, creator_id)
        VALUES (".$this->room_id.",".$this->creator_id.")
        ";

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
            return true;
        else
            return false;
    }
}

?>