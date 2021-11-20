<?php
if(!isset($_COOKIE['username']) || !isset($_COOKIE['password'])) {
    header('Location: /login.php');
}
else {
    header('Location: /main.php');
}
?>