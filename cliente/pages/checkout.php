<?php
session_start();
require_once __DIR__ . '/../../db.php';

$pageTitle = 'Finalizar Compra - Bike Store';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Verificar que el carrito no esté vacío
if (empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit;
}

// Verificar que el cliente esté autenticado
if (!isset($_SESSION['customer_id'])) {
    // Guardar URL de retorno
    header('Location: login_cliente.php?redirect=' . urlencode('checkout.php'));
    exit;
}

// Obtener datos del cliente
$stmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id = :id");
$stmt->execute(['id' => $_SESSION['customer_id']]);
$cliente = $stmt->fetch();

if (!$cliente) {
    session_destroy();
    header('Location: login_cliente.php');
    exit;
}

// Validar stock y calcular totales
$subtotal = 0;
$descuento_total = 0;
$productos_invalidos = [];

foreach ($_SESSION['carrito'] as $key => &$item) {
    // Verificar stock actual
    $stmt = $pdo->prepare("SELECT stock_disponible, price, descuento, product_name FROM productos WHERE product_id = :id");
    $stmt->execute(['id' => $item['product_id']]);
    $producto_actual = $stmt->fetch();
    
    if (!$producto_actual || $producto_actual['stock_disponible'] < 1) {
        $productos_invalidos[] = $item['name'];
        unset($_SESSION['carrito'][$key]);
        continue;
    }
    
    if ($item['quantity'] > $producto_actual['stock_disponible']) {
        $item['quantity'] = $producto_actual['stock_disponible'];
    }
    
    // Actualizar precios actuales
    $item['price'] = $producto_actual['price'];
    $item['descuento'] = $producto_actual['descuento'];
    $item['name'] = $producto_actual['product_name'];
    
    // Calcular precio con descuento
    $precio_con_descuento = $item['price'];
    if ($item['descuento'] > 0) {
        $precio_con_descuento = $item['price'] * (1 - $item['descuento'] / 100);
    }
    $item['precio_con_descuento'] = $precio_con_descuento;
    
    // Calcular subtotales
    $item_subtotal = $item['price'] * $item['quantity'];
    $item_total = $precio_con_descuento * $item['quantity'];
    
    $subtotal += $item_subtotal;
    $descuento_total += ($item_subtotal - $item_total);
}

$_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar

// Verificar nuevamente si el carrito quedó vacío
if (empty($_SESSION['carrito'])) {
    header('Location: carrito.php?error=stock');
    exit;
}

// Calcular total final
$total_con_descuento = $subtotal - $descuento_total;
$costo_envio = ($total_con_descuento >= 500) ? 0 : 50;
$total_final = $total_con_descuento + $costo_envio;

// Obtener métodos de pago disponibles
$stmt = $pdo->query("SELECT * FROM metodos_pago WHERE activo = 1 ORDER BY nombre");
$metodos_pago = $stmt->fetchAll();

// Procesar el formulario
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metodo_pago_id = isset($_POST['metodo_pago']) ? intval($_POST['metodo_pago']) : 0;
    $street = trim($_POST['street'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    $notas = trim($_POST['notas'] ?? '');
    
    // Validaciones
    if ($metodo_pago_id === 0) {
        $error = 'Por favor selecciona un método de pago';
    } elseif (empty($street) || empty($city)) {
        $error = 'Por favor completa la dirección de envío';
    } else {
        // Actualizar dirección del cliente si cambió
        $stmt = $pdo->prepare("UPDATE customer SET street = :street, city = :city, state = :state, zip_code = :zip_code WHERE customer_id = :id");
        $stmt->execute([
            'street' => $street,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zip_code,
            'id' => $_SESSION['customer_id']
        ]);
        
        // Guardar datos en sesión para la confirmación
        $_SESSION['checkout_data'] = [
            'metodo_pago_id' => $metodo_pago_id,
            'street' => $street,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zip_code,
            'notas' => $notas,
            'subtotal' => $subtotal,
            'descuento' => $descuento_total,
            'envio' => $costo_envio,
            'total' => $total_final
        ];
        
        // Redirigir a confirmación
        header('Location: confirmar_pedido.php');
        exit;
    }
}

include __DIR__ . '/../components/header_publico.php';
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/Bike_Store/cliente/index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="carrito.php">Carrito</a></li>
            <li class="breadcrumb-item active">Checkout</li>
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
                <div class="step active">
                    <div class="step-icon"><i class="fas fa-credit-card"></i></div>
                    <div class="step-label">Checkout</div>
                </div>
                <div class="step-line"></div>
                <div class="step">
                    <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="step-label">Confirmación</div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($productos_invalidos)): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i> 
        <strong>Atención:</strong> Los siguientes productos fueron removidos por falta de stock: 
        <?php echo implode(', ', $productos_invalidos); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="" id="checkoutForm">
        <div class="row">
            <!-- Columna Izquierda: Formulario -->
            <div class="col-lg-7 mb-4">
                <!-- Información del Cliente -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Información del Cliente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Nombre:</strong></p>
                                <p><?php echo htmlspecialchars($cliente['first_name'] . ' ' . $cliente['last_name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Email:</strong></p>
                                <p><?php echo htmlspecialchars($cliente['email']); ?></p>
                            </div>
                        </div>
                        <a href="perfil.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i> Editar información
                        </a>
                    </div>
                </div>
                
                <!-- Dirección de Envío -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Dirección de Envío</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="street" class="form-label fw-bold">
                                    Calle y Número <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="street" 
                                       name="street"
                                       value="<?php echo htmlspecialchars($cliente['street'] ?? ''); ?>"
                                       placeholder="Ej: Av. Principal 123"
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label fw-bold">
                                    Ciudad <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="city" 
                                       name="city"
                                       value="<?php echo htmlspecialchars($cliente['city'] ?? ''); ?>"
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label fw-bold">Estado/Provincia</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="state" 
                                       name="state"
                                       value="<?php echo htmlspecialchars($cliente['state'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="zip_code" class="form-label fw-bold">Código Postal</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="zip_code" 
                                       name="zip_code"
                                       value="<?php echo htmlspecialchars($cliente['zip_code'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Método de Pago -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-credit-card"></i> Método de Pago</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($metodos_pago)): ?>
                        <div class="alert alert-warning">
                            No hay métodos de pago disponibles. Por favor contacta al administrador.
                        </div>
                        <?php else: ?>
                        <div class="metodos-pago">
                            <?php foreach ($metodos_pago as $metodo): ?>
                            <div class="form-check metodo-pago-item">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="metodo_pago" 
                                       id="metodo_<?php echo $metodo['metodo_id']; ?>"
                                       value="<?php echo $metodo['metodo_id']; ?>"
                                       required>
                                <label class="form-check-label w-100" for="metodo_<?php echo $metodo['metodo_id']; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($metodo['nombre']); ?></strong>
                                            <?php if (!empty($metodo['descripcion'])): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($metodo['descripcion']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <i class="fas fa-<?php 
                                            echo match(strtolower($metodo['nombre'])) {
                                                'efectivo' => 'money-bill-wave',
                                                'tarjeta de crédito', 'tarjeta de débito' => 'credit-card',
                                                'qr' => 'qrcode',
                                                'transferencia' => 'exchange-alt',
                                                default => 'wallet'
                                            };
                                        ?> fa-2x text-primary"></i>
                                    </div>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Notas del Pedido -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-sticky-note"></i> Notas del Pedido (Opcional)</h6>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" 
                                  name="notas" 
                                  rows="3" 
                                  placeholder="Ej: Instrucciones especiales de entrega, horarios preferidos, etc."></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Columna Derecha: Resumen -->
            <div class="col-lg-5">
                <div class="card shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Resumen del Pedido</h5>
                    </div>
                    <div class="card-body">
                        <!-- Productos -->
                        <h6 class="fw-bold mb-3">Productos (<?php echo count($_SESSION['carrito']); ?>)</h6>
                        <div class="productos-resumen mb-3">
                            <?php foreach ($_SESSION['carrito'] as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div class="flex-grow-1">
                                    <small><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></small>
                                </div>
                                <div>
                                    <small class="fw-bold">$<?php echo number_format($item['precio_con_descuento'] * $item['quantity'], 2); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <hr>
                        
                        <!-- Totales -->
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <?php if ($descuento_total > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Descuentos:</span>
                            <span>-$<?php echo number_format($descuento_total, 2); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Costo de envío:</span>
                            <span>
                                <?php if ($costo_envio === 0): ?>
                                <span class="badge bg-success">GRATIS</span>
                                <?php else: ?>
                                $<?php echo number_format($costo_envio, 2); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <?php if ($total_con_descuento < 500 && $costo_envio > 0): ?>
                        <div class="alert alert-info py-2 px-3 mb-3">
                            <small><i class="fas fa-info-circle"></i> Agrega $<?php echo number_format(500 - $total_con_descuento, 2); ?> más para envío gratis</small>
                        </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <strong class="fs-5">TOTAL:</strong>
                            <strong class="fs-4 text-success">$<?php echo number_format($total_final, 2); ?></strong>
                        </div>
                        
                        <!-- Botones -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-lock"></i> Finalizar Compra
                            </button>
                            <a href="carrito.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Carrito
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt"></i> Compra 100% segura
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
    
    .metodo-pago-item {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .metodo-pago-item:hover {
        border-color: #667eea;
        background-color: #f8f9fa;
    }
    
    .metodo-pago-item input:checked + label {
        color: #667eea;
    }
    
    .metodo-pago-item input:checked ~ * {
        border-color: #667eea;
    }
    
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
    
    .productos-resumen {
        max-height: 200px;
        overflow-y: auto;
    }
    
    .productos-resumen::-webkit-scrollbar {
        width: 6px;
    }
    
    .productos-resumen::-webkit-scrollbar-thumb {
        background-color: #cbd5e0;
        border-radius: 3px;
    }
</style>

<?php include __DIR__ . '/../components/footer_publico.php'; ?>
