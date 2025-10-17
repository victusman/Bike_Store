<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare('UPDATE orders SET estado = :e WHERE order_id = :id');
    $stmt->execute(['e'=>'Anulado','id'=>$id]);
}
header('Location: index.php');
exit;
