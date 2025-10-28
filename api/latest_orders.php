<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

$result = ['success' => false, 'data' => null, 'error' => null];
try {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
    // Obtener los últimos pedidos con total calculado
    $sql = "SELECT o.order_id, o.order_date, o.estado, c.first_name, c.last_name, (SELECT IFNULL(SUM(oi.price * oi.quantity),0) FROM order_items oi WHERE oi.order_id = o.order_id) AS total FROM orders o LEFT JOIN customer c ON o.customer_id = c.customer_id ORDER BY o.created_at DESC LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $result['success'] = true;
    $result['data'] = $rows;
} catch (Exception $e) {
    $result['error'] = $e->getMessage();
}

echo json_encode($result);
