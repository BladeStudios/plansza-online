var pageInfo = "kalambury";
var roomInfo = $('#roomId').val();
var userInfo = $('#userId').val();
var loginInfo = $('#login').val();

var canvasWidth = 650;
var canvasHeight = 450;

$(document).ready(function(){

    /* ------------------------------------------------------------------------------------------------------
                                                    WEBSOCKET
    -------------------------------------------------------------------------------------------------------- */

    var conn = new WebSocket('ws://localhost:8080');

    conn.onopen = function(e) //czyli co ma się dziać u usera, który się połączył do socketa
    {
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

        if(data.page == 'kalambury')
        {
            if(data.type == 'pagejoin' && data.roomid != 0 && data.roomid == roomInfo && $('#'+data.login).length == 0)
            {
                var html_data = '<tr id="'+data.login+'"><td id="'+data.login+'-login" class="kalambury-login">'+data.login+'</td><td id="'+data.login+'-points" class="kalambury-points">0</td></tr>';
                $('#spectator-list').append(html_data);
                if(userInfo == data.userid)
                    $('#'+data.login).addClass("kalambury-me");
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
            else if(data.type == 'message' && data.roomid != 0 && data.roomid == roomInfo)
            {
                var msg = '<span style="font-weight: bold; color: black">' + data.login + ": </span>" + data.msg + "<br/>";
                var chat = $('#kalambury-chat');
                chat.append(msg);
                $('#kalambury-chat').scrollTop($('#kalambury-chat')[0].scrollHeight);
            }
            else if(data.type == 'redirect')
            {
                if(data.pageto != 'login')
                {
                    console.log("nie login");
                    window.location.href = data.page + ".php?room=" + data.roomid;
                    $('#'+loginInfo).addClass("kalambury-me"); //ze wzgledu na google chrome, bo on widzi ten element po refreshu zanim jeszcze zostanie on dodany przez js
                }
                else if (data.pageto == 'login')
                {
                    console.log("login");
                    window.location.href = data.pageto + ".php?error=1";
                }
            }
        }
    };

    /* ------------------------------------------------------------------------------------------------------
                                                    CANVAS
    -------------------------------------------------------------------------------------------------------- */
    
    var clickX = new Array();
    var clickY = new Array();
    var clickDrag = new Array();
    var paint;
    var lastX;
    var lastY;

    var canvasDiv = document.getElementById('canvasDiv');
    canvas = document.createElement('canvas');
    canvas.setAttribute('width', canvasWidth);
    canvas.setAttribute('height', canvasHeight);
    canvas.setAttribute('id', 'canvas');
    canvas.style.border = "none";
    if(canvasDiv != null)
        canvasDiv.appendChild(canvas);
    
    if(typeof G_vmlCanvasManager != 'undefined') {
        canvas = G_vmlCanvasManager.initElement(canvas);
    }
    context = canvas.getContext("2d");

    $('#canvas').mousedown(function(e){
        if(event.which == 1) //left mouse
        {
            var mouseX = e.pageX - this.offsetLeft;
            var mouseY = e.pageY - this.offsetTop;
                    
            paint = true;
            sendDrawingToOthers(e.pageX - this.offsetLeft, e.pageY - this.offsetTop);
            draw(e.pageX - this.offsetLeft, e.pageY - this.offsetTop);
        }
    });

    $('#canvas').mousemove(function(e){
        if(paint){
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
        draw(x,y, dragging);
    }

    function addClick(x, y, dragging)
    {
        clickX.push(x);
        clickY.push(y);
        clickDrag.push(dragging);
    }

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

    /* ------------------------------------------------------------------------------------------------------
                                                    CHAT
    -------------------------------------------------------------------------------------------------------- */

    function sendMessage()
    {
        var messageInfo = $('#message-input').val();
        var msg = '<span style="font-weight: bold; color: blue">' + loginInfo + ": </span>" + messageInfo + "<br/>";
        var chat = $('#kalambury-chat');
        chat.append(msg);
        $('#kalambury-chat').scrollTop($('#kalambury-chat')[0].scrollHeight);
        $('#message-input').val("");
        
        typeInfo = "message";
        var data = {
                type: typeInfo,
                page: pageInfo,
                roomid: roomInfo,
                userid: userInfo,
                login: loginInfo,
                msg: messageInfo
            }

        conn.send(JSON.stringify(data));
    }

    $('#message-button').click(function() {
        sendMessage();
    });

    $('#message-input').keydown(function(e) {
        var key = e.which;
        if(key == 13)
            sendMessage();
    });

    /* ------------------------------------------------------------------------------------------------------
                                                    GAME ENGINE
    -------------------------------------------------------------------------------------------------------- */

    $('#start-game').click(function() {
        onStart();
    });

    function onStart()
    {
        var data = {
            type: "kalambury_gamestate",
            action: "createGame",
            page: pageInfo,
            roomid: roomInfo,
            userid: userInfo,
            login: loginInfo
        }
        conn.send(JSON.stringify(data)); //dodaje do kalambury_gamestate wpis o gierce
    }

    /* ------------------------------------------------------------------------------------------------------
                                                    LOBBY
    -------------------------------------------------------------------------------------------------------- */

    $('#createRoom').click(function() {
        createRoom();
    });

    function createRoom() {
        typeInfo = "createRoom";
        var data = {
                type: typeInfo,
                page: pageInfo,
                roomid: roomInfo,
                userid: userInfo,
                login: loginInfo
            }

        conn.send(JSON.stringify(data));
    }

});