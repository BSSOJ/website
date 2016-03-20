<?php
session_start();
require_once("config.php");

if(!isset($_SESSION['user'])){
    $_SESSION['error'] = "You are not logged in";
    header("Location: index.php?submit". (isset($_POST['problem']) ? ("=". $_POST['problem']) : ""));
    exit;
}
if(!isset($_POST['language'])){
    $_SESSION['error'] = "No language selected";
    header("Location: index.php?submit". (isset($_POST['problem']) ? ("=". $_POST['problem']) : ""));
    exit;
}else if(!isset($config['languages'][$_POST['language']])){
    $_SESSION['error'] = "Language not found";
    header("Location: index.php?submit". (isset($_POST['problem']) ? ("=". $_POST['problem']) : ""));
    exit;
}
if(!isset($_POST['problem'])){
    $_SESSION['error'] = "You need to enter a problem code";
    header("Location: index.php?submit". (isset($_POST['problem']) ? ("=". $_POST['problem']) : ""));
    exit;
}

$link = mysqli_connect($config['db']['addr'], $config['db']['user'], $config['db']['pass'], $config['db']['name'], $config['db']['port']);
$result = mysqli_query($link, "SELECT ProblemID FROM problems WHERE ProblemCode='". mysqli_real_escape_string($link, $_POST['problem']). "'");
$id = -1;
if($row = mysqli_fetch_assoc($result)){
    $id = $row['ProblemID'];
}else{
    $_SESSION['error'] = "Invalid problem code";
    header("Location: index.php?submit". (isset($_POST['problem']) ? ("=". $_POST['problem']) : ""));
    exit;
}

$sql = "INSERT INTO submissions (ProblemID, UserID, Language, SourceCode) VALUES (". $id. ", ". $_SESSION['userid']. ", '". $_POST['language']. "', '". mysqli_real_escape_string($link, $_POST['source']). "')";
mysqli_query($link, $sql);

header("Location: index.php?problem=". $_POST['problem']);
