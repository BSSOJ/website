<?php
session_start();
require_once("config.php");

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?= $config['site']['title'] ?></title>
    </head>
    <body>
        <?php
        if($user == null){
            echo "You are not logged in.";

            if(isset($_SESSION['error'])){
                echo  $_SESSION['error'];
            }
        ?>
        <form action="login.php" method="POST">
        <input type="text" name="user" placeholder="Username"><input type="password" name="pass" placeholder="Password"><input type="submit">
        </form>
        <?php
        }else{
            echo "You are logged in as, ". $user;
        }
        ?>
    </body>
</html>
<?php
unset($_SESSION['error']);
?>
