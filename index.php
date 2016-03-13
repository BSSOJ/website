<?php
session_start();
require_once("config.php");

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

$problems = [];
$link = mysqli_connect($config['db']['addr'], $config['db']['user'], $config['db']['pass'], $config['db']['name'], $config['db']['port']);
$result = mysqli_query($link, "SELECT name, code, SUM(testcases.score) AS score FROM problems JOIN testcases WHERE testcases.probid=problems.id GROUP BY problems.id");
$i = 0;
while($row = mysqli_fetch_assoc($result)){
    $problems[$i++] = $row;
}
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
            echo "You are logged in as, ". $user. " <a href=\"logout.php\">Logout</a>";
        }
        ?>
        <table border="1">
            <tr>
                <th>Problem name</th>
                <th>Problem code</th>
                <th>Points value</th>
            </tr>
            <?php
            foreach($problems as $problem){
                echo "<tr>";
                echo "<td>". $problem['name']. "</td>";
                echo "<td>". $problem['code']. "</td>";
                echo "<td>". $problem['score']. "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </body>
</html>
<?php
unset($_SESSION['error']);
?>
