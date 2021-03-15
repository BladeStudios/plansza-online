<?php

class Game
{
    $points = array();
    public function __construct()
    {
    }

    function setPoints($index, $points)
    {
        $this->points[$index] = $points;
    }
}

?>