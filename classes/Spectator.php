<?php

class Room
{
    protected $connection;
    private $room_id;
    private $spectator_id;

    public function __construct()
    {
        require_once('DatabaseConnection.php');
        $db_connection = new DatabaseConnection;
        $this->connection = $db_connection->connect();
    }

    function getRoomId() { return $this->room_id; }

    function setRoomId($room_id) { $this->room_id = $room_id; }

    function getSpectatorId() { return $this->spectator_id; }

    function setSpectatorId($spectator_id) { $this->spectator_id = $spectator_id; }

    function updateSpectator($tableName, $roomId, $spectatorId)
    {
        $sql = "
        UPDATE ".$tableName."
        SET spectator_id = ".$this->spectator_id."
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
        INSERT INTO ".$tableName." (room_id, spectator_id)
        VALUES (".$room_id.",".$spectator_id.")
        ";

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
            return true;
        else
            return false;
    }
}

?>