<?php
    session_start();

    if (empty($_POST['nombre']) || empty($_POST['password'])) {
        header('Location: login.php?error=1');
        exit;
    }

    $username = $_POST['nombre'];
    $password = $_POST['password'];

    require_once __DIR__ . '/config.php';

    $stmt = $pdo->prepare("SELECT nombre, rol, password FROM usuarios WHERE nombre = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        $_SESSION['user'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
        header('Location: index.php');
        exit;
    } else {
        header('Location: login.php?error=1');
        exit;
    }
?>