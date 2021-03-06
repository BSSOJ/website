<?php
session_start();
require_once("config.php");

if(!isset($_GET['login'])){
    require_once("auth.php");
}

$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;

$page = 0;
if(isset($_GET['login'])){
    $page = 1;
}else if(isset($_GET['problem'])){
    require('lib/Parsedown.php');
    $page = 2;
}else if(isset($_GET['submit'])){
    $page = 3;
}else if(isset($_GET['submissions'])){
    $page = 4;
}else if(isset($_GET['viewsubmission'])){
    $page = 5;
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
                        <li <?= $page == 3 ? "class=\"active\"" : ""?>><a href="?submit">Submit</a></li>
                        <li <?= $page == 4 ? "class=\"active\"" : ""?>><a href="?submissions">Submissions</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li <?= $page == 1 ? "class=\"active\"" : ""?>><?= $user == null ? "<a href=\"?login\">Login</a>" : "<a href=\"logout.php\">Logout (".  $user. ")</a>"?></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container">
            <?php
            if($page == 5){
                if(!isset($_SESSION['user'])){
                    echo "<div class=\"alert alert-danger\" role=\"alert\">You must be logged in to do that</div>";
                }else{
                    $id = mysqli_real_escape_string($link, $_GET['viewsubmission']);
                    $result = mysqli_query($link, "SELECT problems.ProblemName, problems.ProblemCode, problems.ProblemValue, problems.ProblemDescription, submissions.SubmissionDate, submissions.SourceCode, submissions.PointsEarnt, CASE WHEN submissions.SubmissionStatus = 'DONE' THEN submissions.Result ELSE submissions.SubmissionStatus END AS 'status' FROM submissions JOIN problems WHERE problems.ProblemID = submissions.ProblemID AND submissions.SubmissionID='". $id. "' AND submissions.UserID=". $_SESSION['userid']);
                    
                    if($row = mysqli_fetch_assoc($result)){
                        $status = $row['status'];
                        if($status == "PEND"){
                            $status = "Pending";
                        }else if($status == "PROG"){
                            $status = "Judging";
                        }
            ?>
            <h1>Submission</h1>
            <div class="col-md-3 col-md-push-9">
                Problem code: <?= $row['ProblemCode']?><br>
                Problem value: <?= $row['ProblemValue']?><br><br>
                Submission date: <?= $row['SubmissionDate']?><br>
                Result: <?= $status?><br>
                Points earnt: <?= $row['PointsEarnt']?>
            </div>
            <div class="col-md-9 col-md-pull-3">
                <pre><?= htmlspecialchars($row['SourceCode'])?></pre>
                <?php
                $result = mysqli_query($link, "SELECT judge_results.TestResult, testcases.CaseValue FROM judge_results JOIN testcases WHERE testcases.TestcaseID=judge_results.TestcaseID AND judge_results.SubmissionID=". $id);
                while($row = mysqli_fetch_assoc($result)){
                    echo $row['TestResult']. " - ". ($row['TestResult'] == "AC" ? $row['CaseValue'] : "0"). "/". $row['CaseValue']. "<br>";
                }
                ?>
            </div>
            <?php
                    }else{
                        echo "<div class=\"alert alert-danger\" role=\"alert\">Either that submission does not exist, or you do not have permission to view it</div>";
                    }
                }
            }else if($page == 4){
                $id = $_GET['submissions'] != "" ? mysqli_real_escape_string($link, $_GET['submissions']) : -1;
                $result = mysqli_query($link, "SELECT submissions.SubmissionDate, users.Username, problems.ProblemName, CASE WHEN submissions.SubmissionStatus =  'DONE' THEN submissions.Result ELSE submissions.SubmissionStatus END AS 'status', submissions.PointsEarnt, problems.ProblemCode, submissions.SubmissionID FROM submissions JOIN users, problems WHERE users.UserID = submissions.UserID AND submissions.ProblemID=problems.ProblemID ". ($id != -1 ? ("AND problems.ProblemCode='". $id. "' ") : ""). "ORDER BY submissions.SubmissionDate DESC");
            ?>
            <table class="table">
                <tr>
                    <th class="col-xs-2">
                        Date
                    </th>
                    <th class="col-xs-3">
                        User
                    </th>
                    <th class="col-xs-3">
                        Problem
                    </th>
                    <th class="col-xs-2">
                        Result
                    </th>
                    <th class="col-xs-2">
                        Score
                    </th>
                </tr>
                <?php
                while($row = mysqli_fetch_assoc($result)){
                    $status = $row['status'];
                    if($status == "PEND"){
                        $status = "Pending";
                    }else if($status == "PROG"){
                        $status = "Judging";
                    }
                    echo "<tr>";
                    echo "<td>". $row['SubmissionDate']. "</td>";
                    echo "<td>". $row['Username']. "</td>";
                    echo "<td><a href=\"?problem=". $row['ProblemCode']. "\">". $row['ProblemName']. "</a></td>";
                    echo "<td><a href=\"?viewsubmission=". $row['SubmissionID']. "\">". $status. "</a></td>";
                    echo "<td>". $row['PointsEarnt']. "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
            <?php
            }else if($page == 3){
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
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
            <?php
            }else if($page == 2){
                $problem = mysqli_real_escape_string($link, $_GET['problem']);
                $result = mysqli_query($link, "SELECT ProblemName, ProblemCode, ProblemValue, ProblemDescription FROM problems WHERE ProblemCode='". $problem. "'");
                if($row = mysqli_fetch_assoc($result)){
            ?>
            <h1><?= $row['ProblemName']?></h1>
            <div class="col-md-2 col-md-push-10">
                <a href="?submit=<?= $row['ProblemCode']?>" class="btn btn-primary btn-fullwidth">Submit Solution</a>
                <a href="?submissions=<?= $row['ProblemCode']?>" class="btn btn-success btn-fullwidth">View solutions</a><br>
                Problem code: <?= $row['ProblemCode']?><br>
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
                if(!isset($_SESSION['totpuser'])){
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
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <?php
                }else{
            ?>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="exampleInputEmail1">Two-step verification code</label>
                    <input type="text" class="form-control" name="code" placeholder="Two-step veification code" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <?php
                }
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
        <script type="text/javascript" async src="https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-MML-AM_CHTML"></script>
    </body>
</html>
<?php
unset($_SESSION['error']);
?>

