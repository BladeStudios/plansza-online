<?php

class User
{
    protected $connection;
    private $id;
    private $login;
    private $password;
    private $created;
    private $banned_until;
    private $bans;
    private $email;
    private $name;
    private $city;
    private $description;
    private $last_ip;
    private $cur_ip;
    private $cur_game_id;
    private $online;
    private $online_from;
    private $last_activity;
    private $country;
    private $lang;
    private $system;
    private $browser;

    public function __construct()
    {
        require_once('DatabaseConnection.php');
        $db_connection = new DatabaseConnection;
        $this->connection = $db_connection->connect();
    }

    function getId() { return $this->id; }

    function setId($id) { $this->id = $id; }

    function getLogin() { return $this->login; }

    function setLogin($login) { $this->login = $login; }

    function getPassword() { return $this->password; }

    function setPassword($password) { $this->password = $password; }

    function getCreated() { return $this->created; }

    function setCreated($created) { $this->created = $created; }

    function getBannedUntil() { return $this->banned_until; }

    function setBannedUntil($banned_until) { $this->banned_until = $banned_until; }

    function getBans() { return $this->bans; }

    function setBans($bans) { $this->bans = $bans; }

    function getEmail() { return $this->email; }

    function setEmail($email) { $this->email = $email; }

    function getName() { return $this->name; }

    function setName($name) { $this->name = $name; }

    function getCity() { return $this->city; }

    function setCity($city) { $this->city = $city; }

    function getDescription() { return $this->description; }

    function setDescription($description) { $this->description = $description; }

    function getLastIp() { return $this->last_ip; }

    function setLastIp($last_ip) { $this->last_ip = $last_ip; }

    function getCurIp() { return $this->cur_ip; }

    function setCurIp($cur_ip) { $this->cur_ip = $cur_ip; }

    function getOnline() { return $this->online; }

    function setOnline($online) { $this->online = $online; }

    function getOnlineFrom() { return $this->online_from; }

    function setOnlineFrom($online_from) { $this->online_from = $online_from; }

    function getLastActivity() { return $this->last_activity; }

    function setLastActivity($last_activity) { $this->last_activity = $last_activity; }

    function getCountry() { return $this->country; }

    function setCountry($country) { $this->country = $country; }

    function getLang() { return $this->lang; }

    function setLang($lang) { $this->lang = $lang; }

    function getSystem() { return $this->system; }

    function setSystem($system) { $this->system = $system; }

    function getBrowser() { return $this->browser; }

    function setBrowser($browser) { $this->browser = $browser; }
}

?>