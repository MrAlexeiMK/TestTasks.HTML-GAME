<?php
$start_time = microtime(TRUE);
session_start();
$_SESSION['requests'] = 0;
$_SESSION['time'] = 0;
$_SESSION['socket_requests'] = 0;
$_SESSION['socket_time'] = 0;
$error = "";
if (isset($_COOKIE['username']) && isset($_COOKIE['password'])) {
    unset($_COOKIE['username']);
    setcookie('username', null, -1, '/');
    unset($_COOKIE['password']);
    setcookie('password', null, -1, '/');
}
if (!empty($_POST)) {
    if(isset($_POST['username']) && isset($_POST['password']) && $_POST['username'] != "" && $_POST['password'] != "") {
        $con = new mysqli("127.0.0.1", "root", "root", "html_game");
        $stmt = $con->prepare("SELECT password FROM players WHERE nickname = ?");
        $stmt->bind_param('s', $_POST['username']);
        $stmt->execute();
        $_SESSION['requests']++;
        $result = $stmt->get_result();
        $user = $result->fetch_object();
        if(is_null($user)) {
            $error = "Пользователь не найден";
        }
        else {
            if($_POST['password'] == $user->password) {
                setcookie('username', $_POST['username']);
                setcookie('password', $_POST['password']);
                header('Location: /main.php');
            }
            else {
                $error = "Неверный пароль";
            }
        }
    }
    else {
        $error = "Заполните все поля!";
    }
}
?>
<div class="main main-raised col-md-8 mx-auto my-5 text-center section" id="login">
    <h3 class="big-text disable-select">Авторизация</h3>
    <form action="login.php" method="POST" id="enter">
        <input type="text" name="username" id="username" placeholder="Никнейм" /> <br /> <br />
        <input type="password" name="password" id="password" placeholder="Пароль" /> <br /> <br />
        <p class="text disable-select" style="color: red"><?=$error?></p> <br />
        <input type="submit" name="enter" value="Войти" />
    </form>
    <a href="/reg.php">Регистрация</a>
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