<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../db.php';

// Obtener order_id
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
    exit;
}

try {
    // Obtener datos del pedido
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = :id");
    $stmt->execute(['id' => $order_id]);
    $pedido = $stmt->fetch();
    
    if (!$pedido) {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
        exit;
    }
    
    // Verificar que el pedido pertenece al cliente actual (si está logueado)
    if (isset($_SESSION['customer_id']) && $pedido['customer_id'] != $_SESSION['customer_id']) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para ver este pedido']);
        exit;
    }
    
    // Obtener items del pedido
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name
        FROM order_items oi
        LEFT JOIN productos p ON oi.product_id = p.product_id
        WHERE oi.order_id = :order_id
    ");
    $stmt->execute(['order_id' => $order_id]);
    $items = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'pedido' => $pedido,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener el detalle']);
}
?>
