<?php
    session_start();
    require_once('connect.php');
    require_once('functions.php');
    require_once('polish.php');

    if(!isset($_POST['login']) || !isset($_POST['password']))
    {
        header('Location: login.php');
        exit();
    }


    $connection = @new mysqli($host, $db_user, $db_password, $db_name);

    if($connection->connect_errno!=0)
    {
        echo "Error: ".$connection->connect_errno;
    }
    else
    {
        $login = $_POST['login'];
        $password = $_POST['password'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $login = htmlentities($login, ENT_QUOTES, "UTF-8");

        if($result = @$connection->query(
            sprintf("SELECT * FROM users WHERE login='%s'",
            mysqli_real_escape_string($connection,$login))))
        {
            $users_amount = $result->num_rows;
            if($users_amount==1)
            {
                $row = $result->fetch_assoc();

                if(password_verify($password, $row['password']))
                {
                    $_SESSION['loggedin'] = true;
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['login'] = $row['login'];

                    //updating database
                    $date = new DateTime();
                    $timestamp = $date->getTimestamp();
                    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
                        //ip from share internet
                        $ip = $_SERVER['HTTP_CLIENT_IP'];
                    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                        //ip pass from proxy
                        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    }else{
                        $ip = $_SERVER['REMOTE_ADDR'];
                    }
                    $id = $row['id'];

                    $user_os        = getOS();
                    $user_browser   = getBrowser();

                    $sql = "UPDATE users SET last_ip=cur_ip, online=1, online_from='$timestamp', last_activity='$timestamp', system='$user_os', browser='$user_browser' WHERE id='$id'";
                    $sql2 = "UPDATE users SET cur_ip='$ip' WHERE id='$id'";
                    
                    if($result = $connection->query($sql))
                    {
                        if($result = $connection->query($sql2))
                        {
                            unset($_SESSION['error']);
                            header('Location: index.php');
                        } else {
                            $_SESSION['error']="Failed to execute sql2 query in loginengine.php.";
                            header('Location: login.php');
                        }
                    } else {
                        $_SESSION['error']="Failed to execute sql1 query in loginengine.php.";
                        header('Location: login.php');
                    }
                }
                else
                {
                    $_SESSION['error']=$lang["e_wrong_password"];
                    header('Location: login.php');
                }
            }
            else
            {
                $_SESSION['error']=$lang["e_wrong_login"];
                header('Location: login.php');
            }
        }
        else
        {
        }

        @$connection->close();
    }
?>