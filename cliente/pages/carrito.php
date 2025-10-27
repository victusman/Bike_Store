<?php
session_start();
require_once __DIR__ . '/../../db.php';

$pageTitle = 'Carrito de Compras - Bike Store';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Calcular totales
$subtotal = 0;
$total = 0;
$descuento_total = 0;

// Validar stock de productos en el carrito
foreach ($_SESSION['carrito'] as $key => &$item) {
    $stmt = $pdo->prepare("SELECT stock_disponible, price, descuento FROM productos WHERE product_id = :id");
    $stmt->execute(['id' => $item['product_id']]);
    $producto_actual = $stmt->fetch();
    
    if (!$producto_actual || $producto_actual['stock_disponible'] < 1) {
        // Producto ya no disponible, eliminar del carrito
        unset($_SESSION['carrito'][$key]);
        continue;
    }
    
    // Ajustar cantidad si excede el stock
    if ($item['quantity'] > $producto_actual['stock_disponible']) {
        $item['quantity'] = $producto_actual['stock_disponible'];
    }
    
    // Actualizar precios por si cambiaron
    $item['price'] = $producto_actual['price'];
    $item['descuento'] = $producto_actual['descuento'];
    $item['precio_con_descuento'] = round($producto_actual['price'] * (1 - $producto_actual['descuento'] / 100), 2);
    $item['stock_disponible'] = $producto_actual['stock_disponible'];
    
    // Calcular totales
    $item_subtotal = $item['price'] * $item['quantity'];
    $item_total = $item['precio_con_descuento'] * $item['quantity'];
    
    $subtotal += $item_subtotal;
    $total += $item_total;
    $descuento_total += ($item_subtotal - $item_total);
}

// Reindexar el array después de eliminar elementos
$_SESSION['carrito'] = array_values($_SESSION['carrito']);

include __DIR__ . '/../components/header_publico.php';
?>

<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/Bike_Store/cliente/index.php">Inicio</a></li>
            <li class="breadcrumb-item active">Carrito de Compras</li>
        </ol>
    </nav>

    <h1 class="mb-4"><i class="fas fa-shopping-cart"></i> Mi Carrito</h1>

    <?php if (empty($_SESSION['carrito'])): ?>
    <!-- Carrito vacío -->
    <div class="text-center py-5">
        <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
        <h3>Tu carrito está vacío</h3>
        <p class="text-muted mb-4">¡Agrega productos para comenzar tu compra!</p>
        <a href="/Bike_Store/cliente/pages/catalogo.php" class="btn btn-primary btn-lg">
            <i class="fas fa-shopping-bag"></i> Ir al Catálogo
        </a>
    </div>
    <?php else: ?>
    
    <div class="row">
        <!-- Lista de productos en el carrito -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Productos (<?php echo count($_SESSION['carrito']); ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Imagen</th>
                                    <th>Producto</th>
                                    <th class="text-center" style="width: 120px;">Precio Unit.</th>
                                    <th class="text-center" style="width: 150px;">Cantidad</th>
                                    <th class="text-end" style="width: 120px;">Subtotal</th>
                                    <th class="text-center" style="width: 80px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['carrito'] as $item): ?>
                                <tr id="item-<?php echo $item['product_id']; ?>">
                                    <!-- Imagen -->
                                    <td>
                                        <?php
                                        $stmt = $pdo->prepare("SELECT foto FROM productos WHERE product_id = :id");
                                        $stmt->execute(['id' => $item['product_id']]);
                                        $img = $stmt->fetch();
                                        ?>
                                        <?php if ($img && $img['foto']): ?>
                                        <img src="/Bike_Store/uploads/<?php echo htmlspecialchars($img['foto']); ?>" 
                                             class="img-fluid rounded" 
                                             style="width: 80px; height: 80px; object-fit: contain;"
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                        <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 80px; height: 80px;">
                                            <i class="fas fa-bicycle fa-2x text-muted"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Nombre del producto -->
                                    <td>
                                        <h6 class="mb-1">
                                            <a href="/Bike_Store/cliente/pages/producto.php?id=<?php echo $item['product_id']; ?>" 
                                               class="text-decoration-none">
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                            </a>
                                        </h6>
                                        <?php if ($item['descuento'] > 0): ?>
                                        <span class="badge bg-danger">-<?php echo number_format($item['descuento'], 0); ?>%</span>
                                        <?php endif; ?>
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-box"></i> Stock: <?php echo $item['stock_disponible']; ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Precio -->
                                    <td class="text-center">
                                        <?php if ($item['descuento'] > 0): ?>
                                        <div class="text-muted small text-decoration-line-through">
                                            $<?php echo number_format($item['price'], 2); ?>
                                        </div>
                                        <div class="fw-bold text-success">
                                            $<?php echo number_format($item['precio_con_descuento'], 2); ?>
                                        </div>
                                        <?php else: ?>
                                        <div class="fw-bold">
                                            $<?php echo number_format($item['price'], 2); ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Cantidad -->
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" type="button" 
                                                    onclick="cambiarCantidad(<?php echo $item['product_id']; ?>, -1)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" 
                                                   class="form-control text-center" 
                                                   id="qty-<?php echo $item['product_id']; ?>"
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   min="1" 
                                                   max="<?php echo $item['stock_disponible']; ?>"
                                                   onchange="actualizarCantidad(<?php echo $item['product_id']; ?>, this.value)">
                                            <button class="btn btn-outline-secondary" type="button" 
                                                    onclick="cambiarCantidad(<?php echo $item['product_id']; ?>, 1)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    
                                    <!-- Subtotal -->
                                    <td class="text-end">
                                        <div class="fw-bold" id="subtotal-<?php echo $item['product_id']; ?>">
                                            $<?php echo number_format($item['precio_con_descuento'] * $item['quantity'], 2); ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Eliminar -->
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="eliminarProducto(<?php echo $item['product_id']; ?>)"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="d-flex justify-content-between mt-3">
                <a href="/Bike_Store/cliente/pages/catalogo.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Seguir Comprando
                </a>
                <button class="btn btn-outline-danger" onclick="vaciarCarrito()">
                    <i class="fas fa-trash"></i> Vaciar Carrito
                </button>
            </div>
        </div>

        <!-- Resumen del pedido -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-calculator"></i> Resumen del Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="resumen-subtotal">$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <?php if ($descuento_total > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span><i class="fas fa-tag"></i> Descuentos:</span>
                        <span id="resumen-descuento">-$<?php echo number_format($descuento_total, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Envío:</span>
                        <span class="text-success">
                            <?php if ($total >= 500): ?>
                            <i class="fas fa-check-circle"></i> GRATIS
                            <?php else: ?>
                            $<?php echo number_format(50, 2); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <?php if ($total < 500): ?>
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-info-circle"></i> Agrega $<?php echo number_format(500 - $total, 2); ?> más para envío gratis
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="h5 mb-0">Total:</span>
                        <span class="h5 mb-0 text-primary" id="resumen-total">
                            $<?php echo number_format($total + ($total >= 500 ? 0 : 50), 2); ?>
                        </span>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="/Bike_Store/cliente/pages/checkout.php" class="btn btn-success btn-lg">
                            <i class="fas fa-credit-card"></i> Proceder al Pago
                        </a>
                        <small class="text-muted text-center">
                            <i class="fas fa-lock"></i> Pago seguro y protegido
                        </small>
                    </div>
                </div>
                
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt"></i> Compra 100% segura<br>
                        <i class="fas fa-undo"></i> Devoluciones en 30 días<br>
                        <i class="fas fa-headset"></i> Soporte 24/7
                    </small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
    function cambiarCantidad(productId, cambio) {
        const input = document.getElementById(`qty-${productId}`);
        const nuevaCantidad = parseInt(input.value) + cambio;
        
        if (nuevaCantidad >= 1 && nuevaCantidad <= parseInt(input.max)) {
            input.value = nuevaCantidad;
            actualizarCantidad(productId, nuevaCantidad);
        }
    }
    
    function actualizarCantidad(productId, cantidad) {
        cantidad = parseInt(cantidad);
        
        if (cantidad < 1) {
            alert('La cantidad debe ser al menos 1');
            location.reload();
            return;
        }
        
        fetch('/Bike_Store/api/carrito_update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: cantidad
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Recargar para actualizar totales
            } else {
                alert('Error: ' + (data.message || 'No se pudo actualizar'));
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar cantidad');
            location.reload();
        });
    }
    
    function eliminarProducto(productId) {
        if (!confirm('¿Estás seguro de eliminar este producto del carrito?')) {
            return;
        }
        
        fetch('/Bike_Store/api/carrito_remove.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo eliminar'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar producto');
        });
    }
    
    function vaciarCarrito() {
        if (!confirm('¿Estás seguro de vaciar todo el carrito?')) {
            return;
        }
        
        fetch('/Bike_Store/api/carrito_clear.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al vaciar carrito');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al vaciar carrito');
        });
    }
</script>

<?php include __DIR__ . '/../components/footer_publico.php'; ?>
