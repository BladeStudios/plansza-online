<?php
    session_start();
    require('status.php');
?>

<html lang="pl-PL">

<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <link rel="icon" type="image/png" href="images/logo.png"/>
    <link rel="stylesheet" href="style.css"/>
</head>

<body>

<div id="top" class="whitebg">
    <div id="logotitle">
        <div id="logo">
            <img src="images/logo.png"/>
        </div>
        <div id="title" class="whitebg">
            Plansza.online
        </div>
    </div>
</div>

<div id="links" class="whitebg">
    <a href="index.php" class="menulinks"><?php echo $lang["index"] ?></a> |
    <a href="highscores.php" class="menulinks"><?php echo $lang["ranking"] ?></a> |
    <a href="register.php" class="menulinks"><?php echo $lang["register"] ?></a> |
    <?php
        if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']==true && isset($_SESSION['login']))
        {
            echo '<a href="logout.php" class="menulinks">';
            echo $lang["loggedas"] . $_SESSION['login'] . $lang["logout"];
        }
        else
        {
            echo '<a href="login.php" class="menulinks">';
            echo $lang["login"];
        }
    ?>
    </a>
</div>

<br>
<div id="pagetitle">
    <?php echo $pagetitle; ?>
 </div>
<div id="wbudowie">
    <?php echo $lang["strona"]; ?>
 </div>