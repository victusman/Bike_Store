<?php
session_start();
// Forzar login: si no hay usuario en sesión, redirigir al login
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/templates/header.php';
$displayUser = htmlspecialchars($_SESSION['usuario'] ?? '');
?>

<div class="py-5 text-center">
    <h1 class="display-4">Bienvenido...</h1>
    <p class="lead">Usuario: <strong><?php echo $displayUser ?></strong></p>
    <p><a class="btn btn-primary btn-lg" href="#" role="button">Aceptar</a></p>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>