<?php
    session_start();
    require('status.php');
?>

<html lang="pl-PL">

<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
    <link rel="icon" type="image/png" href="images/logo.png"/>
    <!-- bootstrap and CSS-->
    <link rel="stylesheet" href="bootstrap/bootstrap.min.css"/>
    <link rel="stylesheet" href="style.css"/>
    <!-- jquery -->
    <script src="jquery/jquery-3.5.1.min.js" type="text/javascript"></script>

</head>

<body>

<div id="top" class="whitebg">

    <div id="logo">
        <a href="index.php"><img src="images/logo64px.png"/></a>
    </div>

    <div id="title" class="whitebg">
        <a href="index.php" class="no-link">Plansza.online</a>
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

</div>

<br/>
<div id="pagetitle">
    <?php echo $pagetitle; ?>
 </div>
 <!--
<div id="wbudowie">
    <?php //echo $lang["strona"]; ?>
 </div>-->