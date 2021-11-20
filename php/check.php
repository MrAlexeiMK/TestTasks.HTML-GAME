<?php
session_start();
if(!isset($_COOKIE['username']) || !isset($_COOKIE['password'])) {
    header('Location: /login.php');
}
else {

    $username = $_COOKIE['username'];
    $pass = $_COOKIE['password'];

    $start_time = microtime(TRUE);
    $memcache = new Memcache;
    $memcache->addServer('localhost') or die("Ошибка подключения к memcached");
    $user = $memcache->get('user_'.md5($username));

    if(!$user) {
        $con = new mysqli("127.0.0.1", "root", "root", "html_game");
        $stmt = $con->prepare("SELECT * FROM players WHERE nickname = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $_SESSION['requests']++;
        $result = $stmt->get_result();
        $user = $result->fetch_object();
        $memcache->set('user_'.md5($username), $user);
    }

    $user->max_hp = $user->hp;

    $end_time = microtime(TRUE);
    $time_taken = ($end_time - $start_time)*1000;
    $_SESSION['time'] += $time_taken;

    $_SESSION['user'] = $user;

    $unset = false;
    if(is_null($user)) {
        $unset = true;
    }
    else {
        $pass_db = $user->password;
        if($pass != $pass_db) {
            $unset = true;
        }
    }

    if($unset) {
        if(isset($_COOKIE['username'])) {
            unset($_COOKIE['username']);
            setcookie('username', null, -1, '/');
        }
        if(isset($_COOKIE['password'])) {
            unset($_COOKIE['password']);
            setcookie('password', null, -1, '/');
        }
        header('Location: /login.php');
    }
}
?>