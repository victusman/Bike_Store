<?php
require_once __DIR__ . '/../../bd.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }

// obtener foto para borrar
$stmt = $pdo->prepare('SELECT foto FROM productos WHERE product_id = :id');
$stmt->execute(['id'=>$id]);
$row = $stmt->fetch();

$pdo->prepare('DELETE FROM productos WHERE product_id = :id')->execute(['id'=>$id]);

if ($row && $row['foto']) {
    $file = __DIR__ . '/../../uploads/' . $row['foto'];
    if (file_exists($file)) @unlink($file);
}

header('Location: index.php');
exit;
?>
