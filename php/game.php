<?php
session_start();
if(!isset($_SESSION['user'])) die("Нет доступа");
$user = $_SESSION['user'];
$host = "127.0.0.1";
$port = 1234;

function sendSocketMessage($msg) {
    global $host, $port;
    $start_time = microtime(TRUE);

    $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Ошибка подключения к серверу\n");
    $result = socket_connect($socket, $host, $port) or die("Ошибка подключения к серверу\n");
    $message = $msg."\n";
    socket_write($socket, $message, strlen($message)) or die("Невозможно отправить сообщение\n");
    socket_close($socket);

    $end_time = microtime(TRUE);
    $time_taken = ($end_time - $start_time)*1000;
    $_SESSION['socket_requests']++;
    $_SESSION['socket_time'] += $time_taken;
}

function sendSocketMessageAndGetAnswer($msg) {
    global $host, $port;
    $start_time = microtime(TRUE);

    $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Ошибка подключения к серверу\n");
    $result = socket_connect($socket, $host, $port) or die("Ошибка подключения к серверу\n");
    $message = $msg."\n";
    socket_write($socket, $message, strlen($message)) or die("Невозможно отправить сообщение\n");
    $ans = socket_read ($socket, 1024) or die("Невозможно получить ответ от сервера\n");
    $ans = str_replace("\r\n", "", $ans);
    socket_close($socket);

    $end_time = microtime(TRUE);
    $time_taken = ($end_time - $start_time)*1000;
    $_SESSION['socket_requests']++;
    $_SESSION['socket_time'] += $time_taken;
    return $ans;
}

function user_add() {
    global $user;
    sendSocketMessage("USER_ADD|".$user->nickname."|".$user->hp."|".$user->damage);
}

function user_remove() {
    global $user;
    sendSocketMessage("USER_REMOVE|".$user->nickname);
}

function user_remove_by_name($username) {
    sendSocketMessage("USER_REMOVE|".$username);
}

function get_opponent() {
    global $user;
    $str = sendSocketMessageAndGetAnswer("USER_OPPONENT|".$user->nickname);
    $arr = explode('|', $str);
    if(count($arr) == 1) return "null";
    $opponent = new StdClass();
    $opponent->nickname = $arr[0];
    $opponent->hp = (int)$arr[1];
    $opponent->damage = (int)$arr[2];
    $opponent->timer = (int)$arr[3];
    $opponent->max_hp = $opponent->hp;
    return $opponent;
}

function user_check() {
    global $user;
    return sendSocketMessageAndGetAnswer("USER_CHECK|".$user->nickname);
}

function is_opponent() {
    global $user, $opponent;
    return sendSocketMessageAndGetAnswer("IS_OPPONENT|".$user->nickname."|".$opponent->nickname);
}

function get_hp($username) {
    return sendSocketMessageAndGetAnswer("GET_HP|".$username);
}

function get_log() {
    global $user;
    return sendSocketMessageAndGetAnswer("GET_LOG|".$user->nickname);
}

function hit_opponent() {
    global $user;
    sendSocketMessage("HIT_OPPONENT|".$user->nickname);
}

function reward($isWinner) {
    global $user;
    $rat = 1;
    if(!$isWinner) $rat = -1;

    $start_time = microtime(TRUE);
    $memcache = new Memcache;
    $memcache->addServer('localhost') or die("Ошибка подключения к memcached");
    $memcache->delete('user'); //после выдачи награды user в бд обновляется, поэтому в check.php нужно будет его заного получить

    $con = new mysqli("127.0.0.1", "root", "root", "html_game");
    $stmt = $con->prepare("UPDATE players SET 
                   rating = '".((int)$user->rating+$rat)."',
                   hp = '".((int)$user->max_hp+1)."', 
                   damage = '".((int)$user->damage+1)."' 
                   WHERE nickname = '".$user->nickname."'");
    $stmt->execute();

    $end_time = microtime(TRUE);
    $time_taken = ($end_time - $start_time)*1000;
    $_SESSION['time'] += $time_taken;
    $_SESSION['requests']++;
}
?>
