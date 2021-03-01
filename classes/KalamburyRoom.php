<?php

require_once('Room.php');

class KalamburyRoom extends Room
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = "kalambury_rooms";
    }
}

?>