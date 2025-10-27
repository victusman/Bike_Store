<?php
session_start();

// Si hay un admin autenticado, mostrar la vista administrativa (comportamiento previo)
if (!empty($_SESSION['user_id'])) {
    include __DIR__ . '/templates/header.php';
    $displayUser = htmlspecialchars($_SESSION['usuario'] ?? '');
    ?>

    <div class="py-5 text-center">
        <h1 class="display-4">Bienvenido...</h1>
        <p class="lead">Usuario: <strong><?php echo $displayUser ?></strong></p>
        <p><a class="btn btn-primary btn-lg" href="#" role="button">Aceptar</a></p>
    </div>

    <?php include __DIR__ . '/templates/footer.php';
    exit;
}

// Si no hay admin en sesión, mostrar la tienda pública (cliente)
header('Location: /Bike_Store/cliente/index.php');
exit;
?>