<?php

class Spectator
{
    protected $connection;
    private $room_id;
    private $spectator_id;
    private $connection_id;
    protected $tableName;

    public function __construct()
    {
        require_once('DatabaseConnection.php');
        $db_connection = new DatabaseConnection;
        $this->connection = $db_connection->connect();
        $this->tableName = "test";
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
}

?>