<?php
// Validar que la sesión tenga los datos mínimos
if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
    header('Location: /login.php'); // ← Barra al inicio = ruta absoluta
    exit;
}
?>