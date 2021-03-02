<?php

require_once('Room.php');

class KalamburyRoom extends Room
{
    public function __construct()
    {
        parent::__construct();
        $this->gameName = "kalambury";
        $this->tableName = $this->gameName."_rooms";
    }
}

?>