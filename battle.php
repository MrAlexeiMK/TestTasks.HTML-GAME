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
$opponent = $_SESSION['opponent'];

if(is_opponent() == "false") {
    header("Location: /main.php");
    exit;
}

if(isset($_POST['hit'])) {
    hit_opponent();
}

$user_hp_server = get_hp($user->nickname);
$opponent_hp_server = get_hp($opponent->nickname);

if($user_hp_server != "null") $user->hp = $user_hp_server;
if($opponent_hp_server != "null") $opponent->hp = $opponent_hp_server;

$short_user = $user->nickname;
$short_opponent = $opponent->nickname;

if(strlen($short_user) > 14) $short_user = substr($short_user, 0, 12)."..";
if(strlen($short_opponent) > 14) $short_opponent = substr($short_opponent, 0, 12)."..";
?>

<div class="main main-raised col-md-8 mx-auto my-2 text-center section" id="p1">
    <p class="battle_text big-text disable-select"><?=$short_opponent?></p>
    <progress id="hp" value="<?=$opponent->hp?>" max="<?=$opponent->max_hp?>"></progress>
</div>

<?php
if($user->hp == "0") {
    echo "<p class='text disable-select my-5'>Поражение :(</p>";
    echo '<form action="main.php" method="POST" id="pvp">';
    echo '<input style="margin: auto; display: block;" type="submit" name="back" class="white_button" value="Назад">';
    echo '</form>';

    reward(false);
}
else if($opponent->hp == "0") {
    echo "<p class='text disable-select my-5'>Победа!</p>";
    echo '<form action="main.php" method="POST" id="pvp">';
    echo '<input style="margin: auto; display: block;" type="submit" name="back" class="white_button" value="Назад">';
    echo '</form>';

    reward(true);
}
else {
    echo "<meta http-equiv='refresh' content='1' >";
    echo '<form action="battle.php" method="POST" id="pvp">';
    echo '<input type="submit" name="hit" class="hit" value="АТАКОВАТЬ">';
    echo '</form>';
}
?>

<div class="main main-raised col-md-8 mx-auto my-5 text-center section" id="p2">
    <p class="battle_text big-text disable-select"><?=$short_user?></p>
    <progress id="hp" value="<?=$user->hp?>" max="<?=$user->max_hp?>"></progress>
</div>

<p class="text disable-select">Логи: <br /> <br/>
<textarea name="log" class="text" cols="40" rows="8"><?=get_log()?></textarea>
</p>

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