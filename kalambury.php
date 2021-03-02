<!-- HEADER -->

<?php
    require_once('polish.php');
    require_once('connect.php');
    $title = "Plansza.online - strona w budowie";
    $pagetitle = $lang["kalambury"];
    include "header.php";
?>

<!-- BODY -->

<?php

    require_once('classes/KalamburyRoom.php');
    require_once('classes/KalamburySpectator.php');

    if($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST['create']))
    {
        if(!isset($_SESSION['id']))
        {
            $_SESSION['error'] = $lang["e_login_to_create"];
            header('location: login.php');
        }
        createRoom();
    }
    
    function createRoom()
    {
        $room_object = new KalamburyRoom;
        $newRoomId = $room_object->getEmptyRoomId();
        $room_object->setRoomId($newRoomId);
        $room_object->setCreatorId($_SESSION['id']);
        $room_object->addRoom();

        header('location: kalambury.php?room='.$newRoomId);
    }

?>

<div id="container">
    <br>

    <!--<div id="canvasDiv" style="border: solid 1px #000000"></div>-->

    <div id="playersList">
    <?php
        if(isset($_GET['room']))
        {
            //check if room exists in database to prevent people from joining rooms by typing room address in browser
            $room_object = new KalamburyRoom;
            $room_object->setRoomId($_GET['room']);
            if($room_object->isRoomCreated() == false)
                header('location: kalambury.php');

            //Room exists in database
            $roomId = $_GET['room'];

            $spectators_object = new KalamburySpectator;
            $spectators_object->setRoomId($roomId);
            $spectators_object->setSpectatorId($_SESSION['id']);

            $spectators = $spectators_object->getSpectators();
            echo '<table border="1" id="spectator_list" class="spectator_list"><tr><th>Spectators</th></tr>';

            foreach($spectators as $spectator)
            {
                echo '<tr id="'.$spectator.'"><td>';
                echo($spectator);
                echo '</td></tr>';
            }
            echo '</table>';
        }
        else
        {
            $roomId = 0;
            echo '
            <div class="inline"><form method="post">
            <input type="hidden" name="create" value="yes" />
            <input type="submit" name="createRoom" class="btn btn-success" value="'.$lang['unranked_game'].'" />
            </form></div>
            <div class="inline">
                <a href="#" class="btn btn-primary disabled button">'.$lang["ranked_game"].'</a>
            </div>

            <div class="inline">
                <a href="index.php" class="btn btn-danger button">'.$lang["back"].'</a>
            </div>
            ';
        }
    ?>

    <input type="hidden" name="roomId" id="roomId" value="<?php echo $roomId;?>"/>
    <input type="hidden" name="userId" id="userId" value="<?php if(isset($_SESSION['id'])) echo $_SESSION['id']; ?>"/>
    <input type="hidden" name="login" id="login" value="<?php if(isset($_SESSION['login'])) echo $_SESSION['login'];?>"/>

    </div>
</div>

<!-- FOOTER -->

<?php
    include "footer.php"
?>

<!-- rysowanie okna kalamburow -->
<script>
    var canvasWidth = 500;
    var canvasHeight = 300;

    var canvasDiv = document.getElementById('canvasDiv');
    canvas = document.createElement('canvas');
    canvas.setAttribute('width', canvasWidth);
    canvas.setAttribute('height', canvasHeight);
    canvas.setAttribute('id', 'canvas');
    canvasDiv.appendChild(canvas);
    if(typeof G_vmlCanvasManager != 'undefined') {
        canvas = G_vmlCanvasManager.initElement(canvas);
    }
    context = canvas.getContext("2d");

    var clickX = new Array();
    var clickY = new Array();
    var clickDrag = new Array();
    var paint;

    $('#canvas').mousedown(function(e){
        var mouseX = e.pageX - this.offsetLeft;
        var mouseY = e.pageY - this.offsetTop;
                
        paint = true;
        addClick(e.pageX - this.offsetLeft, e.pageY - this.offsetTop);
        redraw();
    });

    $('#canvas').mousemove(function(e){
        if(paint){
            addClick(e.pageX - this.offsetLeft, e.pageY - this.offsetTop, true);
            redraw();
        }
    });

    $('#canvas').mouseup(function(e){
        paint = false;
    });

    $('#canvas').mouseleave(function(e){
        paint = false;
    });

    function addClick(x, y, dragging)
    {
        clickX.push(x);
        clickY.push(y);
        clickDrag.push(dragging);
    }

    function redraw(){
        context.clearRect(0, 0, context.canvas.width, context.canvas.height); // Clears the canvas
        
        context.strokeStyle = "#df4b26";
        context.lineJoin = "round";
        context.lineWidth = 5;
                    
        for(var i=0; i < clickX.length; i++) {		
            context.beginPath();
            if(clickDrag[i] && i){
            context.moveTo(clickX[i-1], clickY[i-1]);
            }else{
            context.moveTo(clickX[i]-1, clickY[i]);
            }
            context.lineTo(clickX[i], clickY[i]);
            context.closePath();
            context.stroke();
    }
}

</script>

<script type="text/javascript">
    $(document).ready(function(){

        var conn = new WebSocket('ws://localhost:8080');
        var typeInfo = "pagejoin";
        var pageInfo = "kalambury";
        var roomInfo = $('#roomId').val();
        var userInfo = $('#userId').val();
        var loginInfo = $('#login').val();

        conn.onopen = function(e) {
            console.log("Connection established!");

            var data = {
                type: typeInfo,
                page: pageInfo,
                roomid: roomInfo,
                userid: userInfo,
                login: loginInfo
            }

            conn.send(JSON.stringify(data));
        };

        conn.onmessage = function(e) {
            console.log(e.data);

            var data = JSON.parse(e.data);

            if(data.type == 'pagejoin' && data.roomid != 0 && data.roomid == roomInfo)
            {
                var html_data = '<tr id="'+data.login+'"><td>'+data.login+'</td></tr>';
                $('#spectator_list').append(html_data);
            }
            else if(data.type == 'pageleave' && data.roomid != 0 && data.roomid == roomInfo)
            {
                $('#'+data.login).remove();
            }
        };

    });
</script>