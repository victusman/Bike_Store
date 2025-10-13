<?php
require_once __DIR__ . '/../../bd.php';

$id = $_GET['id'] ?? null;
if ($id) {
    // antes de borrar, podemos establecer category_id=NULL en productos relacionados
    $stmt = $pdo->prepare('UPDATE productos SET category_id = NULL WHERE category_id = :id');
    $stmt->execute(['id'=>$id]);
    $stmt = $pdo->prepare('DELETE FROM categoria WHERE category_id = :id');
    $stmt->execute(['id'=>$id]);
}
header('Location: index.php'); exit;
