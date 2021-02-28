<?php

class Player
{
    protected $connection;
    private $room_id;
    private $player_id;
    protected $tableName;

    public function __construct()
    {
        require_once('DatabaseConnection.php');
        $db_connection = new DatabaseConnection;
        $this->connection = $db_connection->connect();
    }

    function getRoomId() { return $this->room_id; }

    function setRoomId($room_id) { $this->room_id = $room_id; }

    function getPlayerId() { return $this->player_id; }

    function setPlayerId($player_id) { $this->player_id = $player_id; }

    function updatePlayer()
    {
        $sql = "
        UPDATE ".$this->tableName."
        SET player_id = ".$this->player_id."
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
        INSERT INTO ".$this->tableName." (room_id, player_id)
        VALUES (".$this->room_id.",".$this->player_id.")
        ";

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
            return true;
        else
            return false;
    }
}

?>