<?php
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        if (User::register($_POST['name'], $_POST['email'], $_POST['password'])) {
            header("Location: login.php?success=registered");
        } else {
            header("Location: register.php?error=failed");
        }
    }

    if (isset($_POST['login'])) {
        if (User::login($_POST['email'], $_POST['password'])) {
            header("Location: groups.php");
        } else {
            header("Location: login.php?error=invalid");
        }
    }
}

if (isset($_GET['logout'])) {
    User::logout();
    header("Location: login.php");
}
?>
