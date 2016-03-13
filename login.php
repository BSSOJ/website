<?php
session_start();
require_once("config.php");

if(!isset($_SESSION['user'])){
    if(isset($_POST['user']) && isset($_POST['pass'])){
        $link = mysqli_connect($config['db']['addr'], $config['db']['user'], $config['db']['pass'], $config['db']['name'], $config['db']['port']);
        $user = mysqli_real_escape_string($link, $_POST['user']);

        $result = mysqli_query($link, "SELECT username, password, salt FROM users WHERE username='". $user. "' OR email='". $user. "'");

        $flag = true;
        while($row = mysqli_fetch_assoc($result)){
            $hash = hash('sha512', $row['salt']. $_POST['pass']);
            echo $hash;
            if($hash == $row['password']){
                $_SESSION['user'] = $row['username'];
                $flag = false;
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
header("Location: .");
