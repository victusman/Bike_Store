<?php
require_once __DIR__ . '/../../bd.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE user_id = :id');
    $stmt->execute(['id'=>$id]);
}
header('Location: index.php'); exit;
