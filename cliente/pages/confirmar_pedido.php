<?php
session_start();
require_once __DIR__ . '/../../db.php';

$pageTitle = 'Confirmar Pedido - Bike Store';

// Verificar que existe la sesión de checkout
if (!isset($_SESSION['checkout_data']) || !isset($_SESSION['customer_id']) || empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit;
}

$error = '';
$orden_creada = false;
$order_id = 0;

// Procesar la confirmación del pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        $checkout_data = $_SESSION['checkout_data'];
        
        // Obtener datos del método de pago
        $stmt = $pdo->prepare("SELECT nombre FROM metodos_pago WHERE metodo_id = :id");
        $stmt->execute(['id' => $checkout_data['metodo_pago_id']]);
        $metodo_pago = $stmt->fetch();
        
        if (!$metodo_pago) {
            throw new Exception('Método de pago no válido');
        }
        
        // Verificar stock nuevamente antes de crear el pedido
        foreach ($_SESSION['carrito'] as $item) {
            $stmt = $pdo->prepare("SELECT stock_disponible, product_name FROM productos WHERE product_id = :id FOR UPDATE");
            $stmt->execute(['id' => $item['product_id']]);
            $producto = $stmt->fetch();
            
            if (!$producto) {
                throw new Exception('Producto no encontrado: ' . $item['name']);
            }
            
            if ($producto['stock_disponible'] < $item['quantity']) {
                throw new Exception('Stock insuficiente para: ' . $producto['product_name'] . ' (Disponible: ' . $producto['stock_disponible'] . ')');
            }
        }
        
        // Crear el pedido en la tabla orders
        $stmt = $pdo->prepare("INSERT INTO orders 
            (customer_id, order_date, required_date, shipped_date, order_status, store_id, staff_id, 
             subtotal, descuento, costo_envio, total_amount, metodo_pago, direccion_envio, notas) 
            VALUES 
            (:customer_id, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), NULL, 1, 1, 1,
             :subtotal, :descuento, :envio, :total, :metodo_pago, :direccion, :notas)");
        
        $direccion_completa = $checkout_data['street'] . ', ' . $checkout_data['city'];
        if (!empty($checkout_data['state'])) {
            $direccion_completa .= ', ' . $checkout_data['state'];
        }
        if (!empty($checkout_data['zip_code'])) {
            $direccion_completa .= ' ' . $checkout_data['zip_code'];
        }
        
        $stmt->execute([
            'customer_id' => $_SESSION['customer_id'],
            'subtotal' => $checkout_data['subtotal'],
            'descuento' => $checkout_data['descuento'],
            'envio' => $checkout_data['envio'],
            'total' => $checkout_data['total'],
            'metodo_pago' => $metodo_pago['nombre'],
            'direccion' => $direccion_completa,
            'notas' => $checkout_data['notas'] ?? ''
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Insertar los items del pedido
        foreach ($_SESSION['carrito'] as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items 
                (order_id, item_id, product_id, quantity, list_price, discount) 
                VALUES (:order_id, :item_id, :product_id, :quantity, :price, :discount)");
            
            $stmt->execute([
                'order_id' => $order_id,
                'item_id' => $item['product_id'], // Usar product_id como item_id
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['precio_con_descuento'],
                'discount' => $item['descuento']
            ]);
            
            // Actualizar stock del producto
            $stmt = $pdo->prepare("UPDATE productos 
                SET stock_disponible = stock_disponible - :quantity,
                    ventas_totales = COALESCE(ventas_totales, 0) + :quantity
                WHERE product_id = :id");
            
            $stmt->execute([
                'quantity' => $item['quantity'],
                'id' => $item['product_id']
            ]);
        }
        
        $pdo->commit();
        
        // Limpiar carrito y datos de checkout
        $_SESSION['carrito'] = [];
        unset($_SESSION['checkout_data']);
        
        $orden_creada = true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Error al procesar el pedido: ' . $e->getMessage();
    }
}

// Si no se ha confirmado, mostrar la página de confirmación
if (!$orden_creada) {
    $checkout_data = $_SESSION['checkout_data'];
    
    // Obtener datos del cliente
    $stmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id = :id");
    $stmt->execute(['id' => $_SESSION['customer_id']]);
    $cliente = $stmt->fetch();
    
    // Obtener método de pago
    $stmt = $pdo->prepare("SELECT * FROM metodos_pago WHERE metodo_id = :id");
    $stmt->execute(['id' => $checkout_data['metodo_pago_id']]);
    $metodo_pago = $stmt->fetch();
}

include __DIR__ . '/../components/header_publico.php';
?>

<div class="container my-5">
    <?php if ($orden_creada): ?>
    <!-- Pedido Confirmado -->
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    <div class="success-animation mb-4">
                        <i class="fas fa-check-circle fa-5x text-success"></i>
                    </div>
                    
                    <h2 class="text-success mb-3">¡Pedido Confirmado!</h2>
                    
                    <p class="lead mb-4">Gracias por tu compra. Tu pedido ha sido procesado exitosamente.</p>
                    
                    <div class="order-number-box mb-4">
                        <small class="text-muted d-block mb-1">Número de Pedido</small>
                        <h3 class="text-primary mb-0">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></h3>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Recibirás un correo electrónico con los detalles de tu pedido.
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <a href="factura.php?order_id=<?php echo $order_id; ?>" class="btn btn-success btn-lg" target="_blank">
                            <i class="fas fa-file-pdf"></i> Descargar Factura PDF
                        </a>
                        <a href="mis_pedidos.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag"></i> Ver Mis Pedidos
                        </a>
                        <a href="/Bike_Store/cliente/pages/catalogo.php" class="btn btn-outline-secondary">
                            <i class="fas fa-shopping-cart"></i> Seguir Comprando
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Información adicional -->
            <div class="card mt-4 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-truck"></i> ¿Qué sigue?</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> 
                            Procesaremos tu pedido en las próximas 24 horas
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> 
                            Recibirás actualizaciones por correo electrónico
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> 
                            El envío llegará en 5-7 días hábiles
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success"></i> 
                            Puedes hacer seguimiento en "Mis Pedidos"
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Página de Confirmación -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/Bike_Store/cliente/index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="carrito.php">Carrito</a></li>
            <li class="breadcrumb-item"><a href="checkout.php">Checkout</a></li>
            <li class="breadcrumb-item active">Confirmar Pedido</li>
        </ol>
    </nav>
    
    <!-- Progress Steps -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="progress-steps">
                <div class="step completed">
                    <div class="step-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="step-label">Carrito</div>
                </div>
                <div class="step-line completed"></div>
                <div class="step completed">
                    <div class="step-icon"><i class="fas fa-credit-card"></i></div>
                    <div class="step-label">Checkout</div>
                </div>
                <div class="step-line completed"></div>
                <div class="step active">
                    <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="step-label">Confirmación</div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <hr>
        <a href="checkout.php" class="btn btn-sm btn-danger">Volver al Checkout</a>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="row">
            <!-- Columna Izquierda: Detalles -->
            <div class="col-lg-7 mb-4">
                <!-- Información del Cliente -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Datos del Cliente</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($cliente['first_name'] . ' ' . $cliente['last_name']); ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($cliente['email']); ?></p>
                        <p class="mb-0"><strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente['phone'] ?? 'No especificado'); ?></p>
                    </div>
                </div>
                
                <!-- Dirección de Envío -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Dirección de Envío</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><?php echo htmlspecialchars($checkout_data['street']); ?></p>
                        <p class="mb-1"><?php echo htmlspecialchars($checkout_data['city']); ?>, 
                            <?php echo htmlspecialchars($checkout_data['state'] ?? ''); ?> 
                            <?php echo htmlspecialchars($checkout_data['zip_code'] ?? ''); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Método de Pago -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-credit-card"></i> Método de Pago</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">
                            <strong><?php echo htmlspecialchars($metodo_pago['nombre']); ?></strong>
                            <?php if (!empty($metodo_pago['descripcion'])): ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($metodo_pago['descripcion']); ?></small>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <!-- Productos -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-box"></i> Productos (<?php echo count($_SESSION['carrito']); ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($_SESSION['carrito'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                                        <td class="text-end">$<?php echo number_format($item['precio_con_descuento'], 2); ?></td>
                                        <td class="text-end">$<?php echo number_format($item['precio_con_descuento'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Columna Derecha: Resumen y Confirmación -->
            <div class="col-lg-5">
                <div class="card shadow-lg border-primary sticky-top" style="top: 100px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Resumen Final</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($checkout_data['subtotal'], 2); ?></span>
                        </div>
                        
                        <?php if ($checkout_data['descuento'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Descuentos:</span>
                            <span>-$<?php echo number_format($checkout_data['descuento'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Envío:</span>
                            <span>
                                <?php if ($checkout_data['envio'] === 0): ?>
                                <span class="badge bg-success">GRATIS</span>
                                <?php else: ?>
                                $<?php echo number_format($checkout_data['envio'], 2); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <strong class="fs-5">TOTAL A PAGAR:</strong>
                            <strong class="fs-3 text-success">$<?php echo number_format($checkout_data['total'], 2); ?></strong>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>¡Último paso!</strong> Revisa que toda la información sea correcta antes de confirmar.
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-double"></i> Confirmar Pedido
                            </button>
                            <a href="checkout.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Volver a Editar
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-lock"></i> Transacción 100% segura
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<style>
    .progress-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px 0;
    }
    
    .step {
        text-align: center;
        flex: 0 0 120px;
    }
    
    .step-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.5rem;
        transition: all 0.3s;
    }
    
    .step.active .step-icon {
        background-color: #667eea;
        color: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
        animation: pulse 2s infinite;
    }
    
    .step.completed .step-icon {
        background-color: #28a745;
        color: white;
    }
    
    .step-label {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    .step.active .step-label {
        color: #667eea;
        font-weight: bold;
    }
    
    .step-line {
        flex: 1;
        height: 2px;
        background-color: #e9ecef;
        margin: 0 10px;
        margin-bottom: 35px;
    }
    
    .step-line.completed {
        background-color: #28a745;
    }
    
    .success-animation {
        animation: scaleIn 0.5s ease-out;
    }
    
    @keyframes scaleIn {
        0% {
            transform: scale(0);
            opacity: 0;
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
        }
        50% {
            box-shadow: 0 0 0 8px rgba(102, 126, 234, 0.1);
        }
    }
    
    .order-number-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 20px;
        border-radius: 10px;
        color: white;
    }
</style>

<?php include __DIR__ . '/../components/footer_publico.php'; ?>
