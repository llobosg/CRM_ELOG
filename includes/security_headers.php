<?php
// includes/security_headers.php
if (!headers_sent()) {
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    // Sesión segura (si aún no se ha iniciado)
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'secure' => false, // ← false en localhost, true en producción con HTTPS
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}