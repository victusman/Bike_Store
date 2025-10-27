<?php
session_start();
header('Content-Type: application/json');

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$product_id = (int)$input['product_id'];

try {
    if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
        echo json_encode(['success' => false, 'message' => 'Carrito vacío']);
        exit;
    }
    
    // Buscar y eliminar el producto del carrito
    $carrito_nuevo = [];
    $eliminado = false;
    
    foreach ($_SESSION['carrito'] as $item) {
        if ($item['product_id'] != $product_id) {
            $carrito_nuevo[] = $item;
        } else {
            $eliminado = true;
        }
    }
    
    if (!$eliminado) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado en el carrito']);
        exit;
    }
    
    $_SESSION['carrito'] = $carrito_nuevo;
    
    echo json_encode([
        'success' => true,
        'message' => 'Producto eliminado del carrito'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar: ' . $e->getMessage()
    ]);
}
