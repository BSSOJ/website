<?php
session_start();
require_once("config.php");

$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;

$page = 0;
if(isset($_GET['login'])){
    $page = 1;
}else if(isset($_GET['problem'])){
    require('lib/Parsedown.php');
    $page = 2;
}else if(isset($_GET['submit'])){
    $page = 3;
}

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$link = mysqli_connect($config['db']['addr'], $config['db']['user'], $config['db']['pass'], $config['db']['name'], $config['db']['port']);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonyous">
        <link rel="stylesheet" href="style.css">
        <title><?= $config['site']['title'] ?></title>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="?"><?= $config['site']['title'] ?></a>
                </div>
                <div id="navbar" class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li <?= $page == 0 ? "class=\"active\"" : ""?>><a href="?">Problems</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li><?= $user == null ? "<a href=\"?login\">Login</a>" : "<a href=\"logout.php\">Logout (".  $user. ")</a>"?></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container">
            <?php
            if($page == 3){
                if($error != null){
                    echo "<div class=\"alert alert-danger\" role=\"alert\">". $error. "</div>";
                }
            ?>
            <form action="submit.php" method="POST">
                <div class="form-group">
                    <label for="exampleInputEmail1">Problem Code</label>
                    <input type="text" class="form-control" name="problem" placeholder="Problem code"<?= $_GET['submit'] != "" ? " value=\"". $_GET['submit']. "\"" : ""?>>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Problem Source</label>
                    <textarea class="form-control" name="source" rows="20" placeholder="Problem source"></textarea>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Language</label>
                    <select name="language" class="form-control">
                        <?php
                        foreach($config['languages'] as $code => $name){
                            echo "<option value=\"". $code. "\">". $name. "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-default">Submit</button>
            </form>
            <?php
            }else if($page == 2){
                $problem = mysqli_real_escape_string($link, $_GET['problem']);
                $result = mysqli_query($link, "SELECT ProblemName, ProblemCode, ProblemValue, ProblemDescription FROM problems WHERE ProblemCode='". $problem. "'");
                echo mysqli_error($link);
                if($row = mysqli_fetch_assoc($result)){
            ?>
            <h1><?= $row['ProblemName']?></h1>
            <div class="col-md-2 col-md-push-10">
                <a href="?submit=<?= $row['ProblemCode']?>" class="btn btn-primary btn-submit">Submit Solution</a>
                Problem code: <?= $row['ProblemCode']?>.<br>
                Problem value: <?= $row['ProblemValue']?>
            </div>
            <div class="col-md-10 col-md-pull-2">
                <p>
                    <?php
                    $desc = $row['ProblemDescription'];
                    $Parsedown = new Parsedown();
                    echo $Parsedown->text($desc);
                    ?>
                </p>
            </div>
            <?php
                }else{
                    echo "No such problem";
                }
            }else if($page == 1){
                if($error != null){
                    echo "<div class=\"alert alert-danger\" role=\"alert\">". $error. "</div>";
                }
            ?>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="exampleInputEmail1">Username/Email address</label>
                    <input type="text" class="form-control" name="user" placeholder="Username/Email" required>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">Password</label>
                    <input type="password" class="form-control" name="pass" placeholder="Password" required>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox"> Remember me
                    </label>
                </div>
                <button type="submit" class="btn btn-default">Submit</button>
            </form>
            <?php
            }else if($page == 0){
            ?>
            <table class="table">
                <tr>
                    <th class="col-xs-10">Name</th>
                    <th class="col-xs-1">Code</th>
                    <th class="col-xs-1">Value</th>
                </tr>
                <?php
                $result = mysqli_query($link, "SELECT ProblemName, ProblemCode, ProblemValue FROM problems");
                while($row = mysqli_fetch_assoc($result)){
                    echo "<tr>";
                    echo "<td><a href=\"?problem=". $row['ProblemCode']. "\">". $row['ProblemName']. "</a></td>";
                    echo "<td>". $row['ProblemCode']. "</td>";
                    echo "<td>". $row['ProblemValue']. "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
            <?php
            }
            ?>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    </body>
</html>
<?php
unset($_SESSION['error']);
?>

