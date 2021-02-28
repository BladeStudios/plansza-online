<?php

require_once('Spectator.php');

class KalamburySpectator extends Spectator
{
    public function __construct()
    {
        parent::__construct();
        $this->tableName = "kalambury_spectators";
    }
}

?>