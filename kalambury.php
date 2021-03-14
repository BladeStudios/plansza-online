<!-- HEADER -->

<?php
    require_once('polish.php');
    require_once('connect.php');
    $title = "Plansza.online - strona w budowie";
    $pagetitle = $lang["kalambury"];
    include "header.php";
?>

<!-- BODY -->

<div id="playersList">
    <?php
        require_once('classes/KalamburyRoom.php');
        require_once('classes/KalamburySpectator.php');
        $room_object = new KalamburyRoom;
        $spectators_object = new KalamburySpectator;

        if(isset($_GET['room']))
        {
            if(!isset($_SESSION['id']))
            {
                $_SESSION['error'] = $lang["e_login_to_join"];
                header('location: login.php');
            }
            //check if room exists in database to prevent people from joining rooms by typing room address in browser
            $room_object->setRoomId($_GET['room']);
            if($room_object->isRoomCreated() == false)
                header('location: kalambury.php');

            //Room exists in database
            $roomId = $_GET['room'];

            $spectators_object->setRoomId($roomId);
            $spectators_object->setSpectatorId($_SESSION['id']);

            $spectators = $spectators_object->getSpectators();

            echo '<div id="kalambury-container">
                <div id="kalambury-left">
                    <div id="kalambury-word">word</div>
                    <div id="kalambury-canvas">
                        <div id="canvasDiv"></div>
                    </div>
                    <div id="kalambury-tools">
                        <div id="start-game-div"><input type="button" class="btn btn-success" id="start-game" value="START GAME"></div>
                    </div>
                </div>
                <div id="kalambury-right">
                    <div id="kalambury-players">
                        <table id="spectator-list" class="spectator-list"><tr><th class="kalambury-player-th">Player</th><th class="kalambury-points-th">Points</th></tr>';
                        foreach($spectators as $spectator)
                        {
                            echo '<tr id="'.$spectator.'"><td id="'.$spectator.'-login" class="kalambury-login">';
                            echo($spectator);
                            echo '</td><td id="'.$spectator.'-points" class="kalambury-points">0</td></tr>';
                        }
                        echo '</table>
                    </div>
                    <div id="kalambury-chat"></div>
                    <div id="kalambury-message">
                        <div id="message-div"><input type="text" class="form-control" id="message-input" maxlength="50"></div>
                        <div id="send-message-div"><input type="button" class="btn btn-primary" id="message-button" value=">"></div>
                    </div>
                </div>
            </div>';
            
        }
        else
        {
            $roomId = 0;
            echo '
            <div class="inline">
                <button id="createRoom" class="btn btn-success button">'.$lang['unranked_game'].'</button>
            </div>
            <div class="inline">
                <a href="#" class="btn btn-secondary disabled button">'.$lang["ranked_game"].'</a>
            </div>

            <div class="inline">
                <a href="index.php" class="btn btn-danger button">'.$lang["back"].'</a>
            </div>
            ';
            
            $rooms_list = $spectators_object->getRoomsList();
            echo '<br/>Gry nierankingowe<br/><br/>
            <table id="rooms-list"><tr><th>Room</th><th>Players</th><th>Game Type</th><th>Join</th></tr>';
            foreach($rooms_list as $room)
            {
                echo '<tr id="room'.$room['room_id'].'">';
                echo '<td>Room '.$room['room_id'].'</td>';
                echo '<td class="text-right" id="players'.$room['room_id'].'">'.$room['spectators'].'</td>';
                echo '<td>Unranked</td>';
                echo '<td><a href="kalambury.php?room='.$room['room_id'].'" class="btn btn-success">JOIN</a></td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    ?>

    <input type="hidden" name="roomId" id="roomId" value="<?php echo $roomId;?>"/>
    <input type="hidden" name="userId" id="userId" value="<?php if(isset($_SESSION['id'])) echo $_SESSION['id']; ?>"/>
    <input type="hidden" name="login" id="login" value="<?php if(isset($_SESSION['login'])) echo $_SESSION['login'];?>"/>

</div>

<!-- FOOTER -->

<?php
    include "footer.php"
?>

<!--JS-->
<script src="js/kalambury.js"></script>