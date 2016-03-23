<?php
session_start();
require_once("config.php");

function verifytotp($code, $secret){
    return true;
}

if(!isset($_SESSION['user']) && !isset($_SESSION['totpuser'])){
    if(isset($_POST['user']) && isset($_POST['pass'])){
        $link = mysqli_connect($config['db']['addr'], $config['db']['user'], $config['db']['pass'], $config['db']['name'], $config['db']['port']);
        $user = mysqli_real_escape_string($link, $_POST['user']);

        $result = mysqli_query($link, "SELECT UserID, Username, PasswordHash, TOTPSecret FROM users WHERE Username='". $user. "' OR EmailAddress='". $user. "'");
        
        $flag = true;
        while($row = mysqli_fetch_assoc($result)){
            if(password_verify($_POST['pass'], $row['PasswordHash'])){
                $_SESSION['userid'] = $row['UserID'];
                $flag = false;
                if($row['TOTPSecret'] == null){
                    $_SESSION['user'] = $row['Username'];

                    header("Location: index.php");
                    exit;
                }else{
                    $_SESSION['totpuser'] = $row['Username'];
                    $_SESSION['totpsecret'] = $row['TOTPSecret'];
               }
            }
        }
        if($flag){
            $_SESSION['error'] = "Invalid username/password combination.";
        }
        mysqli_close($link);
    }else{
        $_SESSION['error'] = "You must enter your username and password to login";
    }
}else if(isset($_SESSION['totpuser'])){
    if(isset($_POST['code'])){
        if(verifytotp($_POST['code'], $_SESSION['totpsecret'])){
            $_SESSION['user'] = $_SESSION['totpuser'];
            unset($_SESSION['totpuser']);
            header("Location: index.php");
            exit;
        }else{
            $_SESSION['error'] = "Invalid two-step verification code";
        }
    }else{
        $_SESSION['error'] = "You must enter your two-step verification code";
    }
}
header("Location: index.php?login");
