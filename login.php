<?php
session_start();
require_once("config.php");

if(!isset($_SESSION['user'])){
    if(isset($_POST['user']) && isset($_POST['pass'])){
        $link = mysqli_connect($config['db']['addr'], $config['db']['user'], $config['db']['pass'], $config['db']['name'], $config['db']['port']);
        $user = mysqli_real_escape_string($link, $_POST['user']);

        $result = mysqli_query($link, "SELECT Username, PasswordHash FROM users WHERE Username='". $user. "' OR EmailAddress='". $user. "'");

        $flag = true;
        while($row = mysqli_fetch_assoc($result)){
            if(password_verify($_POST['pass'], $row['PasswordHash'])){
                $_SESSION['user'] = $row['Username'];
                $flag = false;

                header("Location: index.php");
                exit;
            }
        }
        if($flag){
            $_SESSION['error'] = "Invalid username/password combination.";
        }
        mysqli_close($link);
    }else{
        $_SESSION['error'] = "You must enter your username and password to login";
    }
}
header("Location: index.php?login");
