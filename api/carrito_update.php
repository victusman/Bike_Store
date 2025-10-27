<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$product_id = (int)$input['product_id'];
$quantity = (int)$input['quantity'];

// Validar cantidad
if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Cantidad debe ser mayor a 0']);
    exit;
}

try {
    // Verificar stock disponible
    $stmt = $pdo->prepare("SELECT stock_disponible FROM productos WHERE product_id = :id");
    $stmt->execute(['id' => $product_id]);
    $producto = $stmt->fetch();
    
    if (!$producto) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        exit;
    }
    
    if ($producto['stock_disponible'] < $quantity) {
        echo json_encode([
            'success' => false,
            'message' => 'Stock insuficiente. Disponible: ' . $producto['stock_disponible']
        ]);
        exit;
    }
    
    // Actualizar cantidad en el carrito
    if (!isset($_SESSION['carrito'])) {
        echo json_encode(['success' => false, 'message' => 'Carrito vacío']);
        exit;
    }
    
    $encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['product_id'] == $product_id) {
            $item['quantity'] = $quantity;
            $encontrado = true;
            break;
        }
    }
    
    if (!$encontrado) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado en el carrito']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cantidad actualizada'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar: ' . $e->getMessage()
    ]);
}
