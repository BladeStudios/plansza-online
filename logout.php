<?php
    session_start();
    if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']==true && isset($_SESSION['login']))
    {
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
                $date = new DateTime();
                $timestamp = $date->getTimestamp();
                $login = $_SESSION['login'];
    
                //updating last_activity and status to offline
                $sql = "UPDATE users SET last_activity='$timestamp', online=0 WHERE login='$login'";
    
                if($connection->query($sql))
                {
                    //ok
                }
                 else
                {
                    throw new Exception($connection->error);
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
    session_unset();
    header('Location: index.php');
?>