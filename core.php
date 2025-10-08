<?php
// core.php - funciones comunes del proyecto
require_once __DIR__ . '/bd.php';

function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Aquí puedes agregar más funciones reutilizables
?>
