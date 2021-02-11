<?php
    require_once('polish.php');
?>
<html lang="pl-PL">

<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <link rel="icon" type="image/png" href="images/pawn_black.png"/>
    <link rel="stylesheet" href="style.css"/>
</head>

<body>

<div id="top" class="whitebg">
    <div id="logotitle">
        <div id="logo">
            <img src="images/pawn_black.png"/>
        </div>
        <div id="title" class="whitebg">
            Plansza.online
        </div>
    </div>
</div>

<div id="links" class="whitebg">
<a href="index.php" class="menulinks"><?php echo $lang["index"] ?></a> |
    <a href="register.php" class="menulinks"><?php echo $lang["register"] ?></a>
</div>

<br>
<div id="wbudowie">
    <?php echo $lang["strona"] ?>
 </div>