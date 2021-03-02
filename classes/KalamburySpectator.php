<?php

require_once('Spectator.php');

class KalamburySpectator extends Spectator
{
    public function __construct()
    {
        parent::__construct();
        $this->gameName = "kalambury";
        $this->tableName = $this->gameName."_spectators";
    }
}

?>