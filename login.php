<!-- HEADER -->

<?php
    require_once('polish.php');
    $title = "Plansza.online - strona w budowie";
    $pagetitle = $lang["titlelogin"];
    include "header.php";
?>

<?php
    if(isset($_SESSION['error']))
    {
        echo $_SESSION['error'];
    }
?>

<!-- BODY -->

<div id="container">
    <br>
    <div id="registerform">
        <form action="loginengine.php" method="post">
            <?php echo $lang["nick"] ?><br/><input type="text" name="login"/><br/>
            <?php echo $lang["password"] ?><br/><input type="password" name="password"/><br/>
            </br><input type="submit" style="margin-left: auto; margin-right: auto;" value="<?php echo $lang["loginbutton"] ?>"/>
        </form>
    </div>
    
</div>

<!-- FOOTER -->

<?php
    include "footer.php"
?>