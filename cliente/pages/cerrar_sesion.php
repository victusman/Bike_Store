<?php
session_start();

// Destruir todas las variables de sesión del cliente
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['customer_email']);

// Nota: NO destruimos el carrito para que se mantenga
// Si quisieras destruir todo (incluyendo el carrito), usa: session_destroy();

// Redirigir a la página de inicio
header('Location: /Bike_Store/cliente/index.php?logout=success');
exit;
?>
