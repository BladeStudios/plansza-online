<!-- HEADER -->

<?php
    require_once('polish.php');
    $title = "Plansza.online - strona w budowie";
    $pagetitle = $lang["titlelogin"];
    include "header.php";
?>

<?php
    if(isset($_GET['error']))
    {
        if($_GET['error']==1)
            $_SESSION['error'] = $lang['e_login_to_create'];
    }

    if(isset($_SESSION['error']))
    {
        echo '<span style="color: red">'.$_SESSION['error'].'</span>';
        unset($_SESSION['error']);
    }
?>

<!-- BODY -->

<div id="container">
    <br>
    <div id="registerform">
        <form action="loginengine.php" method="post">
            <?php echo $lang["nick"] ?><br/><input type="text" name="login"/><br/>
            <?php echo $lang["password"] ?><br/><input type="password" name="password"/><br/>
            </br><input type="submit" class="btn btn-success center-in-div" value="<?php echo $lang["loginbutton"] ?>"/>
        </form>
    </div>
    
</div>

<!-- FOOTER -->

<?php
    include "footer.php"
?>