<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

 $id = $_GET['id'] ?? null;
if ($id) {
    // comprobar si el cliente tiene pedidos relacionados
    $cnt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE customer_id = :id');
    $cnt->execute(['id' => $id]);
    $has = (int) $cnt->fetchColumn();
    if ($has > 0) {
        // no borrar: existen pedidos que referencian este cliente
        // redirigimos con un indicador de error para que la UI lo muestre si se desea
        header('Location: index.php?error=has_orders'); exit;
    }

    // obtener foto
    $stmt = $pdo->prepare('SELECT foto FROM customer WHERE customer_id = :id');
    $stmt->execute(['id'=>$id]);
    $row = $stmt->fetch();

    // borrar cliente
    $del = $pdo->prepare('DELETE FROM customer WHERE customer_id = :id');
    $del->execute(['id'=>$id]);

    if ($row && !empty($row['foto'])) {
        $file = __DIR__ . '/imagen/' . $row['foto'];
        if (file_exists($file)) @unlink($file);
    }
}
header('Location: index.php'); exit;
