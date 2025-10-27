<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$product_id = (int)$input['product_id'];
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

// Validar cantidad
if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Cantidad debe ser mayor a 0']);
    exit;
}

try {
    // Verificar que el producto existe y tiene stock
    $stmt = $pdo->prepare("SELECT product_id, product_name, price, descuento, stock_disponible 
                           FROM productos WHERE product_id = :id");
    $stmt->execute(['id' => $product_id]);
    $producto = $stmt->fetch();
    
    if (!$producto) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        exit;
    }
    
    if ($producto['stock_disponible'] < $quantity) {
        echo json_encode([
            'success' => false, 
            'message' => 'Stock insuficiente. Disponible: ' . $producto['stock_disponible']
        ]);
        exit;
    }
    
    // Inicializar carrito en sesión si no existe
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
    
    // Si el producto ya está en el carrito, sumar la cantidad
    $existe = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['product_id'] == $product_id) {
            $nueva_cantidad = $item['quantity'] + $quantity;
            
            // Verificar stock para la nueva cantidad
            if ($nueva_cantidad > $producto['stock_disponible']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No hay suficiente stock. Máximo disponible: ' . $producto['stock_disponible']
                ]);
                exit;
            }
            
            $item['quantity'] = $nueva_cantidad;
            $existe = true;
            break;
        }
    }
    
    // Si no existe, agregarlo al carrito
    if (!$existe) {
        $_SESSION['carrito'][] = [
            'product_id' => $product_id,
            'product_name' => $producto['product_name'],
            'price' => $producto['price'],
            'descuento' => $producto['descuento'],
            'quantity' => $quantity,
            'precio_con_descuento' => round($producto['price'] * (1 - $producto['descuento'] / 100), 2)
        ];
    }
    
    // Calcular total de items en el carrito
    $total_items = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total_items += $item['quantity'];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Producto agregado al carrito',
        'cart_count' => $total_items
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al agregar al carrito: ' . $e->getMessage()
    ]);
}
