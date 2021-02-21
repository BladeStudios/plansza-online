<?php
    //if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']==true && isset($_SESSION['login']))
    if(true)
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
                if(isset($_SESSION['login']))
                {
                    $login = $_SESSION['login'];

                    //updating last_activity and status to online
                    $sql = "UPDATE users SET last_activity='$timestamp', online = 1 WHERE login='$login'";
    
                    if($connection->query($sql))
                    {
                        //ok
                    }
                     else
                    {
                        throw new Exception($connection->error);
                    }
                }
                

                //updating status to Away (online=2)
                $awayTimestamp = $timestamp - 300;
                $sql2 = "UPDATE users SET online=2 WHERE last_activity<= '$awayTimestamp' AND online=1";

                if($connection->query($sql2))
                {
                    //ok
                }
                 else
                {
                    throw new Exception($connection->error);
                }

                //updating status to offline (online=0)
                $offlineTimestamp = $timestamp - ini_get("session.gc_maxlifetime");
                $sql3 = "UPDATE users SET online=0 WHERE last_activity<= '$offlineTimestamp' AND online!=0";

                if($connection->query($sql3))
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

?>