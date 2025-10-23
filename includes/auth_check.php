<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
// Si hay sesión, permite el acceso
?>