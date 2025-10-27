<?php
session_start();
header('Content-Type: application/json');

// Verificar si existe el carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    echo json_encode([
        'success' => true,
        'items' => [],
        'total' => 0,
        'subtotal' => 0,
        'descuento_total' => 0
    ]);
    exit;
}

// Calcular totales
$subtotal = 0;
$total = 0;
$descuento_total = 0;

foreach ($_SESSION['carrito'] as &$item) {
    $item_subtotal = $item['price'] * $item['quantity'];
    $item_total = $item['precio_con_descuento'] * $item['quantity'];
    $item_descuento = $item_subtotal - $item_total;
    
    $subtotal += $item_subtotal;
    $total += $item_total;
    $descuento_total += $item_descuento;
}

echo json_encode([
    'success' => true,
    'items' => $_SESSION['carrito'],
    'subtotal' => round($subtotal, 2),
    'total' => round($total, 2),
    'descuento_total' => round($descuento_total, 2),
    'count' => count($_SESSION['carrito'])
]);
