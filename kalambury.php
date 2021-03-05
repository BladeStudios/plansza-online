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

<div id="kalambury-container">
    <div id="kalambury-left">left
    </div>
    <div id="kalambury-right">right
    </div>
</div>
<br/><br/>

<div id="playersList">
    <?php
        $room_object = new KalamburyRoom;
        $spectators_object = new KalamburySpectator;

        if(isset($_GET['room']))
        {
            //check if room exists in database to prevent people from joining rooms by typing room address in browser
            $room_object->setRoomId($_GET['room']);
            if($room_object->isRoomCreated() == false)
                createRoom();
            
            echo '<div id="canvasDiv" style="background-color: white; border: solid 1px #000000"></div><br/>';

            //Room exists in database
            $roomId = $_GET['room'];

            $spectators_object->setRoomId($roomId);
            $spectators_object->setSpectatorId($_SESSION['id']);

            $spectators = $spectators_object->getSpectators();
            echo '<table border="1" id="spectator_list" class="list"><tr><th>Spectators</th></tr>';

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
                <a href="#" class="btn btn-secondary disabled button">'.$lang["ranked_game"].'</a>
            </div>

            <div class="inline">
                <a href="index.php" class="btn btn-danger button">'.$lang["back"].'</a>
            </div>
            ';
            
            $rooms_list = $spectators_object->getRoomsList();
            echo '<br/>Gry nierankingowe<br/><br/>
            <table border="1" id="rooms_list" class="list"><tr><th>Room</th><th>Players</th><th>Game Type</th><th>Join</th></tr>';
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

<!-- rysowanie okna kalamburow -->
<script type="text/javascript">

    var pageInfo = "kalambury";
    var roomInfo = $('#roomId').val();
    var userInfo = $('#userId').val();
    var loginInfo = $('#login').val();

    var canvasWidth = 500;
    var canvasHeight = 350;

    

    $(document).ready(function(){

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
        //addClick(e.pageX - this.offsetLeft, e.pageY - this.offsetTop);
        sendDrawingToOthers(e.pageX - this.offsetLeft, e.pageY - this.offsetTop);
        draw(e.pageX - this.offsetLeft, e.pageY - this.offsetTop);
    });

    $('#canvas').mousemove(function(e){
        if(paint){
            //addClick(e.pageX - this.offsetLeft, e.pageY - this.offsetTop, true);
            sendDrawingToOthers(e.pageX - this.offsetLeft, e.pageY - this.offsetTop, true);
            draw(e.pageX - this.offsetLeft, e.pageY - this.offsetTop, true);
        }
    });

    $('#canvas').mouseup(function(e){
        paint = false;
    });

    $('#canvas').mouseleave(function(e){
        paint = false;
    });

    function sendDrawingToOthers(x, y, dragging)
    {
        typeInfo = "mousemove";
        var data = {
                type: typeInfo,
                page: pageInfo,
                roomid: roomInfo,
                userid: userInfo,
                posx: x,
                posy: y,
                drag: dragging
            }

        conn.send(JSON.stringify(data));
    }

    function addClickAndDraw(x, y, dragging)
    {
        //addClick(x, y, dragging);
        draw(x,y, dragging);
    }

    function addClick(x, y, dragging)
    {
        clickX.push(x);
        clickY.push(y);
        clickDrag.push(dragging);
    }

    /* old drawing
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
    } */

    var lastX;
    var lastY;

    function draw(x,y,dragging){
        context.strokeStyle = "#df4b26";
        context.lineJoin = "round";
        context.lineWidth = 5;
        if(dragging)
        {
            context.beginPath();
            context.moveTo(lastX, lastY);
            context.lineTo(x, y);
            context.closePath();
        }
        else
        {
            context.fillStyle = "#df4b26";
            context.fillRect(x-2,y-2,5,5);
            //console.log("rect");
        }
        context.stroke();
        lastX = x;
        lastY = y;
    }

        var conn = new WebSocket('ws://localhost:8080');

        conn.onopen = function(e) { //czyli co ma się dziać u usera, który się połączył do socketa
            console.log("Connection established!");

            var typeInfo = "pagejoin";

            var data = {
                type: typeInfo,
                page: pageInfo,
                roomid: roomInfo,
                userid: userInfo,
                login: loginInfo
            }

            conn.send(JSON.stringify(data));
        };

        conn.onmessage = function(e) { //czyli co ma się dziać u usera, który otrzymał wiadomość

            var data = JSON.parse(e.data);
            //console.log(data);

            if(data.page == 'kalambury')
            {
                if(data.type == 'pagejoin' && data.roomid != 0 && data.roomid == roomInfo && ! $('#'+data.login).length)
                {
                    var html_data = '<tr id="'+data.login+'"><td>'+data.login+'</td></tr>';
                    $('#spectator_list').append(html_data);
                }
                else if(data.type == 'pageleave' && data.roomid != 0 && data.roomid == roomInfo)
                {
                    $('#'+data.login).remove();
                }
                else if(data.type == 'pagejoin' && roomInfo == 0)
                {
                    $('#players'+data.roomid).text($('#players'+data.roomid).text()*1+1);
                }
                else if(data.type == 'pageleave' && roomInfo == 0)
                {
                    $('#players'+data.roomid).text($('#players'+data.roomid).text()*1-1);
                }
                else if(data.type == 'mousemove' && data.roomid != 0 && data.roomid == roomInfo)
                {
                    addClickAndDraw(data.posx, data.posy, data.drag);
                }
            }
        };

    });
</script>