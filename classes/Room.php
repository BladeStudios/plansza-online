<?php

class Room
{
    protected $connection;
    private $room_id;
    private $creator_id;

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

    function updateCreator($tableName, $roomId, $creatorId)
    {
        $sql = "
        UPDATE ".$tableName."
        SET creator_id = ".$this->creator_id."
        WHERE room_id = ".$this->room_id."
        ";

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
            return true;
        else
            return false;
    }

    function insertData($tableName)
    {
        $sql = "
        INSERT INTO ".$tableName." (room_id, creator_id)
        VALUES (".$room_id.",".$creator_id.")
        ";

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
            return true;
        else
            return false;
    }
}

?>