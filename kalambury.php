<!-- HEADER -->

<?php
    require_once('polish.php');
    require_once('connect.php');
    $title = "Plansza.online - strona w budowie";
    $pagetitle = $lang["kalambury"];
    include "header.php";
?>

<!-- BODY -->

<div id="container">
    <br>
    <div id="games">
        <a href="index.php"><?php echo $lang["powrot"] ?></a>
    </div>

    <!--<div id="canvasDiv" style="border: solid 1px #000000"></div>-->

    <a href="kalambury.php?room=1">test</a>
    <div id="playersList">
    <?php
        if(isset($_GET['room']))
            $roomId = $_GET['room'];
        else
            $roomId = 0;

        $connection = @new mysqli($host, $db_user, $db_password, $db_name);

        if($connection->connect_errno!=0)
        {
            echo "Error: ".$connection->connect_errno;
        }
        else
        {
            $sql = "SELECT spectator_id FROM kalambury_spectators WHERE room_id='$roomId'";

            if(!$result = $connection->query($sql))
            {
                $_SESSION['error']="Failed to execute query.";
                header('Location: index.php');
            }
            
            $spectators = array();
                
            while($data = $result->fetch_assoc())
            {
                array_push($spectators, $data['spectator_id']);
            }

            $spectatorsStr = implode("', '", $spectators);
            $sql = "SELECT login FROM users WHERE id IN ('$spectatorsStr')";

            echo '<table border="1"><tr><th>Spectators</th></tr>';

            if(!$result = $connection->query($sql))
            {
                $_SESSION['error']="Failed to execute query.";
                header('Location: index.php');
            }
            
            while($data = $result->fetch_assoc())
            {
                echo '<tr><td>';
                echo($data['login']);
                echo '</td></tr>';
            }

            echo '<tr><th>Players</th></tr>';

            $sql = "SELECT player_id FROM kalambury_players WHERE room_id='$roomId'";

            if(!$result = $connection->query($sql))
            {
                $_SESSION['error']="Failed to execute query.";
                header('Location: index.php');
            }
            
            $players = array();
                
            while($data = $result->fetch_assoc())
            {
                array_push($players, $data['player_id']);
            }

            $playersStr = implode("', '", $players);
            $sql = "SELECT login FROM users WHERE id IN ('$playersStr')";

            //echo '<table border="1"><tr><th>Spectators</th></tr>';

            if(!$result = $connection->query($sql))
            {
                $_SESSION['error']="Failed to execute query.";
                header('Location: index.php');
            }
            
            while($data = $result->fetch_assoc())
            {
                echo '<tr><td>';
                echo($data['login']);
                echo '</td></tr>';
            }

            echo '</table>';
        }

    ?>
    </div>
</div>

<!-- FOOTER -->

<?php
    include "footer.php"
?>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
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

<script>
    var conn = new WebSocket('ws://localhost:8080');
    conn.onopen = function(e) {
        console.log("Connection established!");
    };

    conn.onmessage = function(e) {
        console.log(e.data);
    };
</script>