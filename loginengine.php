<?php
    session_start();

    if(!isset($_POST['login']) || !isset($_POST['password']))
    {
        header('Location: login.php');
        exit();
    }

    require_once('connect.php');

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
            sprintf("SELECT * FROM users WHERE user='%s'",
            mysqli_real_escape_string($connection,$login))))
        {
            $users_amount = $result->num_rows;
            if($users_amount==1)
            {
                $row = $result->fetch_assoc();

                if(password_verify($password_hash, $row['password']))
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
                    $sql = "UPDATE users SET last_ip=cur_ip, online=1, online_from='$timestamp' WHERE id='$id'";
                    $sql2 = "UPDATE users SET cur_ip='$ip' WHERE id='$id'";
                    
                    if($result = $mysqli->query($sql))
                    {
                        $result->free_result();
                        if($result = $mysqli->query($sql2))
                        {
                            $result->free_result();
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
            }
        }

        @$connection->close();
    }
?>