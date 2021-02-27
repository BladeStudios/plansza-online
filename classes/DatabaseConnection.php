<?php

class DatabaseConnection
{
    function connect()
    {
        $root = "root";
        $password = "";
        $connection = new PDO("mysql:host=localhost; dbname=plansza", $root, $password);

        return $connection;
    }
}

?>