<?php

require_once('Gamestate.php');

class KalamburyGamestate extends Gamestate
{
    private $p0_id;
    private $p1_id;
    private $p2_id;
    private $p3_id;
    private $p4_id;
    private $p5_id;
    private $p6_id;
    private $p7_id;
    private $p8_id;
    private $p9_id;
    private $p0_points;
    private $p1_points;
    private $p2_points;
    private $p3_points;
    private $p4_points;
    private $p5_points;
    private $p6_points;
    private $p7_points;
    private $p8_points;
    private $p9_points;
    private $drawer_id; //id aktualnie rysujacego
    private $current_word;
    private $choose_timestamp;
    private $drawing_timestamp;
    private $winner_id;

    private $players; //tablica wszystkich graczy z pokoju

    public function __construct()
    {
        parent::__construct();
        $this->gameName = "kalambury";
        $this->tableName = $this->gameName."_gamestate";
        $this->p0_id = 0;
        $this->p1_id = 0;
        $this->p2_id = 0;
        $this->p3_id = 0;
        $this->p4_id = 0;
        $this->p5_id = 0;
        $this->p6_id = 0;
        $this->p7_id = 0;
        $this->p8_id = 0;
        $this->p9_id = 0;
        $this->p0_points = 0;
        $this->p1_points = 0;
        $this->p2_points = 0;
        $this->p3_points = 0;
        $this->p4_points = 0;
        $this->p5_points = 0;
        $this->p6_points = 0;
        $this->p7_points = 0;
        $this->p8_points = 0;
        $this->p9_points = 0;
        $this->drawer_id = 0;
        $this->current_word = "";
        $this->choose_timestamp = 0;
        $this->drawing_timestamp = 0;
        $this->winner_id = 0;
    }

    function getDrawerId()
    {
        return $this->drawer_id;
    }

    function setDrawerId($id)
    {
        $this->drawer_id = $id;
    }

    function getPlayers()
    {
        return $this->players;
    }

    function setPlayers()
    {
        $this->players = array();

        $sql = "SELECT spectator_id FROM ".$this->gameName."_spectators WHERE room_id=".$this->room_id;

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
        {
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        foreach($result as $element)
            array_push($this->players, $element['spectator_id']);
    }

    function saveDraverId()
    {
        $sql = "UPDATE ".$this->gameName."_gamestate SET drawer_id=".$this->drawer_id." WHERE game_id=".$this->game_id;

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
            return true;
        else
            return false;
    }

    function createGame()
    {
        $this->setPlayers();
        $next = true;
        if(!empty($this->players[0]) && $next)
        {
            $this->p0_id = $this->players[0];
            $this->drawer_id = $this->players[0];
        } else { $next = false; }
        if(!empty($this->players[1]) && $next) $this->p1_id = $this->players[1]; else $next = false;
        if(!empty($this->players[2]) && $next) $this->p2_id = $this->players[2]; else $next = false;
        if(!empty($this->players[3]) && $next) $this->p3_id = $this->players[3]; else $next = false;
        if(!empty($this->players[4]) && $next) $this->p4_id = $this->players[4]; else $next = false;
        if(!empty($this->players[5]) && $next) $this->p5_id = $this->players[5]; else $next = false;
        if(!empty($this->players[6]) && $next) $this->p6_id = $this->players[6]; else $next = false;
        if(!empty($this->players[7]) && $next) $this->p7_id = $this->players[7]; else $next = false;
        if(!empty($this->players[8]) && $next) $this->p8_id = $this->players[8]; else $next = false;
        if(!empty($this->players[9]) && $next) $this->p9_id = $this->players[9]; else $next = false;

        $sql = "INSERT INTO ".$this->tableName." (game_id, room_id, game_state, p0_id, p1_id, p2_id, p3_id, p4_id, p5_id, p6_id, p7_id, p8_id, p9_id,
        p0_points, p1_points, p2_points, p3_points, p4_points, p5_points, p6_points, p7_points, p8_points, p9_points, drawer_id, current_word, choose_timestamp, drawing_timestamp, winner_id)
        VALUES (NULL, ".$this->room_id.", 1, ".$this->p0_id.", ".$this->p1_id.", ".$this->p2_id.", ".$this->p3_id.", ".$this->p4_id.", ".$this->p5_id.", ".$this->p6_id."
        , ".$this->p7_id.", ".$this->p8_id.", ".$this->p9_id.", ".$this->p0_points.", ".$this->p1_points.", ".$this->p2_points.", ".$this->p3_points.", ".$this->p4_points."
        , ".$this->p5_points.", ".$this->p6_points.", ".$this->p7_points.", ".$this->p8_points.", ".$this->p9_points.", ".$this->drawer_id.", '".$this->current_word."'
        , ".$this->choose_timestamp.", ".$this->drawing_timestamp.", ".$this->winner_id.");";

        $statement = $this->connection->prepare($sql);

        if($statement->execute())
            return true;
        else
            return false;

        //clean plansze

            //losuj 3 slowa
            //pokaz 3 slowa dla usera do wyboru
            //uruchom czas na wybor
            //uruchom czas na rysowanie
        //wyswietl dla rysujacego wybrane przez niego slowo
        //wyswietl dla zgadujacych slowo do odgadniecia ale bez liter (czyli _____)
    }
}

?>