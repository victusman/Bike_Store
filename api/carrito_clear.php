<?php
session_start();
header('Content-Type: application/json');

try {
    // Vaciar el carrito
    $_SESSION['carrito'] = [];
    
    echo json_encode([
        'success' => true,
        'message' => 'Carrito vaciado'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al vaciar carrito: ' . $e->getMessage()
    ]);
}
