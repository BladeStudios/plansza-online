<!-- HEADER -->

<?php
    require_once('polish.php');
    $title = "Plansza.online - strona w budowie";
    $pagetitle = $lang["createaccount"];
    include "header.php";
?>

<!-- BODY -->

<?php
    if(isset($_POST['email']))
    {
        //czy udana walidacja
        $ok = true;

        //walidacja nickname
        $nick = $_POST['nick'];

        if(strlen($nick)<3 || strlen($nick)>20)
        {
            $ok = false;
            $_SESSION['e_nick'] = $lang['e_nick_length'];
        }
        if(ctype_alnum($nick)==false)
        {
            $ok = false;
            $_SESSION['e_nick'] = $lang['e_nick_letters'];
        }

        //walidacja e-maila
        $email = $_POST['email'];
        $email2 = filter_var($email,FILTER_SANITIZE_EMAIL);

        if(filter_var($email2,FILTER_VALIDATE_EMAIL)==false || $email2!=$email)
        {
            $ok = false;
            $_SESSION['e_email'] = $lang['e_email_incorrect'];
        }

        //walidacja hasla
        $password1 = $_POST['password1'];
        $password2 = $_POST['password2'];

        if(strlen($password1)<8 || strlen($password1)>32)
        {
            $ok = false;
            $_SESSION['e_password'] = $lang['e_password_length'];
        }

        if($password1 != $password2)
        {
            $ok = false;
            $_SESSION['e_password'] = $lang['e_password_identical'];
        }

        $password_hash = password_hash($password1, PASSWORD_DEFAULT);

        //miejsce na inne walidacje (regulamin, recaptcha)

        //zapamietanie wprowadzonych danych
        $_SESSION['temp_nick'] = $nick;
        $_SESSION['temp_email'] = $email;

        require_once('connect.php');
        mysqli_report(MYSQLI_REPORT_STRICT); //exceptions zamiast warningow

        try
        {
            $connection = new mysqli($host, $db_user, $db_password, $db_name);
            if($connection->connect_errno != 0)
            {
                throw new Exception(mysqli_connect_errno());
            }
            else
            {
                //czy istnieje taki sam e-mail w bazie danych
                $result = $connection->query("SELECT id FROM users WHERE email='$email'");
                if(!$result) throw new Exception($connection->error);

                $how_many_emails = $result->num_rows;
                if($how_many_emails>0)
                {
                    $ok = false;
                    $_SESSION['e_email'] = $lang['e_email_exists'];
                }
                
                //czy istnieje taki sam login w bazie danych
                $result = $connection->query("SELECT id FROM users WHERE login='$nick'");
                if(!$result) throw new Exception($connection->error);

                $how_many_nicks = $result->num_rows;
                if($how_many_nicks>0)
                {
                    $ok = false;
                    $_SESSION['e_nick'] = $lang['e_nick_exists'];
                }

                if($ok==true)
                {
                    //timestamp
                    $date = new DateTime();
                    $timestamp = $date->getTimestamp();

                    //ip
                    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
                        //ip from share internet
                        $ip = $_SERVER['HTTP_CLIENT_IP'];
                    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                        //ip pass from proxy
                        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    }else{
                        $ip = $_SERVER['REMOTE_ADDR'];
                    }

                    if($connection->query("INSERT INTO users VALUES (NULL,'$nick','$password_hash','$timestamp',0,0,'$email','','','',0,'$ip',0,0,0,'','')"))
                    {
                        $_SESSION['registered'] = true;
                        header('Location: index.php');
                    }
                    else
                    {
                        throw new Exception($connection->error);
                    }
                }

                $connection->close();
            }
        }
        catch (Exception $e)
        {
            echo '<span style="color: red;">Server error. Please contact administrator.</span>';
            echo '<br/>Error info: '.$e;
        }
    }
?>

<div id="container">
    <br>
    <div id="registerform">
        <form method="post">
            <?php echo $lang["nick"] ?><br/><input type="text" value="<?php
            if(isset($_SESSION['temp_nick']))
            {
                echo $_SESSION['temp_nick'];
                unset ($_SESSION['temp_nick']);
            }?>" name="nick"/><br/>
            <?php
			if (isset($_SESSION['e_nick']))
			{
				echo '<div class="error">'.$_SESSION['e_nick'].'</div>';
				unset($_SESSION['e_nick']);
			}
		    ?>
            <?php echo $lang["password"] ?><br/><input type="password" name="password1"/><br/>
            <?php
			if (isset($_SESSION['e_password']))
			{
				echo '<div class="error">'.$_SESSION['e_password'].'</div>';
				unset($_SESSION['e_password']);
			}
		    ?>
            <?php echo $lang["passwordrepeat"] ?><br/><input type="password" name="password2"/><br/>
            <?php echo $lang["email"] ?><br/><input type="text" value="<?php
            if(isset($_SESSION['temp_email']))
            {
                echo $_SESSION['temp_email'];
                unset ($_SESSION['temp_email']);
            }?>" name="email"/><br/>
            <?php
			if (isset($_SESSION['e_email']))
			{
				echo '<div class="error">'.$_SESSION['e_email'].'</div>';
				unset($_SESSION['e_email']);
			}
		    ?>
            </br><input type="submit" style="margin-left: auto; margin-right: auto;" value="<?php echo $lang["registerbutton"] ?>"/>
        </form>
    </div>
    
</div>

<!-- FOOTER -->

<?php
    include "footer.php"
?>