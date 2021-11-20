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
        if(strpos($_POST['username'], "|")) {
            $error = "Запрещённые символы в нике";
        }
        else {
            $username = $_POST['username'];
            $pass = $_POST['password'];
            $con = new mysqli("127.0.0.1", "root", "root", "html_game");
            $stmt = $con->prepare("INSERT INTO players (nickname, password, damage, hp, rating) VALUES ('" . $username . "', '" . $pass . "', '10', '100', '100')");
            $_SESSION['requests']++;
            if (!$stmt->execute()) {
                $error = "Пользователь с этим ником уже существует!";
            } else {
                setcookie('username', $username);
                setcookie('password', $pass);
                header('Location: main.php');
            }
        }
    }
    else {
        $error = "Заполните все поля!";
    }
}
?>
<div class="main main-raised col-md-8 mx-auto my-5 text-center section" id="reg">
    <h3 class="big-text disable-select">Регистрация</h3>
    <form action="reg.php" method="POST" id="enter">
        <input type="text" name="username" id="username" placeholder="Никнейм" /> <br /> <br />
        <input type="password" name="password" id="password" placeholder="Пароль" /> <br /> <br />
        <p class="text disable-select" style="color: red"><?=$error?></p>
        <input type="submit" name="enter" value="Регистрация" />
    </form>
    <a href="/login.php">Вход</a>
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
