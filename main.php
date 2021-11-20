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
if(user_check() == "true") {
    if(isset($_SESSION['opponent']) && is_opponent() == "true") {
        $opponent = $_SESSION['opponent'];
        user_remove_by_name($opponent->nickname); //нужно для удаление игроков, ливнувших, просто закрыв вкладку
    }
    user_remove();
}

?>

<div id="main">
    <p class="text disable-select ">Дуэли</p>
    <a href="/duels.php">
        <img class="menu disable-select" src="images/sword.png" width="55%"></div>
    </a>

    <p class="text disable-select ">Выход</p>
    <a href="/login.php">
        <img class="menu disable-select" src="images/leave.png" width="55%"></div>
    </a>
</div>

<footer>
    <link rel="stylesheet" href="css/menu.css"">
</footer>

<?php
$end_time = microtime(TRUE);
$time_taken = ($end_time - $start_time)*1000;
$time_taken = round($time_taken, 2);

echo '<p class="disable-select" style="text-align: center;">page: '.$time_taken.' ms, db: '.$_SESSION['requests'].'('.round($_SESSION['time'], 2).'), sockets: '.$_SESSION['socket_requests'].'('.round($_SESSION['socket_time'], 2).')</p>';
?>