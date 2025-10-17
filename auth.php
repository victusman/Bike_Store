<?php
// auth.php - proteger páginas que requieren autenticación
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    header('Location: /Bike_Store/login.php');
    exit;
}

?>
