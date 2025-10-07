<?php
require_once __DIR__ . '/../db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: list.php'); exit; }

// obtener foto para borrar
$stmt = $pdo->prepare('SELECT foto FROM products WHERE product_id = :id');
$stmt->execute(['id'=>$id]);
$row = $stmt->fetch();

$pdo->prepare('DELETE FROM products WHERE product_id = :id')->execute(['id'=>$id]);

if ($row && $row['foto']) {
    $file = __DIR__ . '/../uploads/' . $row['foto'];
    if (file_exists($file)) @unlink($file);
}

header('Location: list.php');
exit;
?>