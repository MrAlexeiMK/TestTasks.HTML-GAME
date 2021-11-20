<?php
$start_time = microtime(TRUE);
error_reporting(0);
session_start();
$_SESSION['requests'] = 0;
$_SESSION['time'] = 0;
$_SESSION['socket_requests'] = 0;
$_SESSION['socket_time'] = 0;
include_once 'php/check.php';
include_once 'php/game.php';
$user = $_SESSION['user'];

user_add();
$opponent = get_opponent();
$timer = "null";
if($opponent != "null") {
    $timer = $opponent->timer;
    if($timer <= 0) {
        $_SESSION['user'] = $user;
        $_SESSION['opponent'] = $opponent;
        header('Location: /battle.php');
        exit;
    }
}
?>
<meta http-equiv="refresh" content="0.9" > <!-- Auto refresh every 0.9 seconds (replacement of ajax) !-->

<div class="my-5 text-center">
    <a href="/main.php">
        <input class="white_button" type="submit" name="cancel" value="Отмена" />
    </a>
</div>
<div class="main main-raised col-md-8 mx-auto my-5 text-center section" id="main">
    <p class="big-text disable-select">Мой рейтинг:</p> <br /> <br /> <br /> <br />
    <p class="big-text disable-select rating"><?=$user->rating?></p>
</div>

<div>
    <?php
        if($timer == "null") {
            echo '<img class="wait disable-select" src="images/waiting.gif" /> <br />';
            echo '<p class="big-text disable-select">Поиск оппонента...</p>';
        }
        else {
            echo '<p class="big-text disable-select" style="color: red">'.$timer.'</p>';
        }
    ?>
</div>

<footer>
    <link rel="stylesheet" href="css/material-kit.css"">
    <link rel="stylesheet" href="css/menu.css"">
</footer>

<?php
$end_time = microtime(TRUE);
$time_taken = ($end_time - $start_time)*1000;
$time_taken = round($time_taken, 2);

echo '<p class="disable-select" style="text-align: center;">page: '.$time_taken.' ms, db: '.$_SESSION['requests'].'('.round($_SESSION['time'], 2).'), sockets: '.$_SESSION['socket_requests'].'('.round($_SESSION['socket_time'], 2).')</p>';
?>