<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../bd.php';

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $del = $pdo->prepare("DELETE FROM stores WHERE store_id = ?");
    $del->execute([$id]);
}
header('Location: index.php'); exit;
