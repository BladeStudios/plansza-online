<?php

class Gamestate
{
    protected $connection;
    protected $tableName;
    protected $gameName;

    protected $game_id;
    protected $room_id;
    protected $game_state;

    public function __construct()
    {
        require_once('DatabaseConnection.php');
        $db_connection = new DatabaseConnection;
        $this->connection = $db_connection->connect();
    }

    function getRoomId() { return $this->room_id; }

    function setRoomId($room_id) { $this->room_id = $room_id; }

    function getGameId() { return $this->game_id; }

    function setGameId($id) { $this->game_id = $id; }

    function getGameState() { return $this->game_state; }

    function setGameState($gamestate) { $this->game_state = $gamestate; }

    function getActiveGameIdByRoomId($room_id)
    {
        $sql = "SELECT game_id FROM ".$this->tableName." WHERE room_id=".$room_id." AND game_state = 1";

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
        {
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result[0]['game_id'];
    }

    //function saveGameState
}

?>