<?php
session_start();
require_once __DIR__ . '/../../db.php';

// Obtener ID del producto
$product_id = $_GET['id'] ?? 0;

if (!$product_id) {
    header('Location: catalogo.php');
    exit;
}

// Obtener información del producto
$stmt = $pdo->prepare("SELECT p.*, c.descripcion AS categoria,
                       ROUND(p.price * (1 - p.descuento / 100), 2) AS precio_con_descuento
                       FROM productos p
                       LEFT JOIN categoria c ON p.category_id = c.category_id
                       WHERE p.product_id = :id");
$stmt->execute(['id' => $product_id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: catalogo.php');
    exit;
}

// Obtener productos relacionados (misma categoría)
$stmtRelacionados = $pdo->prepare("SELECT p.*, c.descripcion AS categoria,
                                   ROUND(p.price * (1 - p.descuento / 100), 2) AS precio_con_descuento
                                   FROM productos p
                                   LEFT JOIN categoria c ON p.category_id = c.category_id
                                   WHERE p.category_id = :cat_id 
                                   AND p.product_id != :prod_id 
                                   AND p.stock_disponible > 0
                                   ORDER BY RAND()
                                   LIMIT 4");
$stmtRelacionados->execute([
    'cat_id' => $producto['category_id'],
    'prod_id' => $product_id
]);
$productosRelacionados = $stmtRelacionados->fetchAll();

$pageTitle = htmlspecialchars($producto['product_name']) . ' - Bike Store';

include __DIR__ . '/../components/header_publico.php';
?>

<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/Bike_Store/cliente/index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/Bike_Store/cliente/pages/catalogo.php">Catálogo</a></li>
            <?php if ($producto['categoria']): ?>
            <li class="breadcrumb-item"><a href="/Bike_Store/cliente/pages/catalogo.php?categoria=<?php echo $producto['category_id']; ?>"><?php echo htmlspecialchars($producto['categoria']); ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($producto['product_name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Imagen del producto -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <?php if ($producto['foto']): ?>
                    <img src="/Bike_Store/uploads/<?php echo htmlspecialchars($producto['foto']); ?>" 
                         class="img-fluid rounded" 
                         alt="<?php echo htmlspecialchars($producto['product_name']); ?>"
                         style="width: 100%; max-height: 500px; object-fit: contain;">
                    <?php else: ?>
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 500px;">
                        <i class="fas fa-bicycle fa-10x text-muted opacity-25"></i>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Características adicionales -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información Adicional</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-shield-alt text-success"></i> Garantía de 1 año
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-truck text-primary"></i> Envío gratis en compras mayores a $500
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-undo text-warning"></i> Devolución dentro de 30 días
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-headset text-info"></i> Soporte técnico incluido
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Información del producto -->
        <div class="col-md-6">
            <div class="mb-3">
                <?php if ($producto['categoria']): ?>
                <span class="badge bg-secondary mb-2">
                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($producto['categoria']); ?>
                </span>
                <?php endif; ?>
                
                <?php if ($producto['destacado']): ?>
                <span class="badge bg-warning text-dark mb-2">
                    <i class="fas fa-star"></i> Destacado
                </span>
                <?php endif; ?>
                
                <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($producto['product_name']); ?></h1>
            </div>

            <!-- Precio -->
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <?php if ($producto['descuento'] > 0): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-danger fs-6">
                            <i class="fas fa-tag"></i> <?php echo number_format($producto['descuento'], 0); ?>% de descuento
                        </span>
                        <span class="text-muted text-decoration-line-through fs-5">
                            $<?php echo number_format($producto['price'], 2); ?>
                        </span>
                    </div>
                    <div class="display-4 text-success fw-bold mb-2">
                        $<?php echo number_format($producto['precio_con_descuento'], 2); ?>
                    </div>
                    <p class="text-success mb-0">
                        <i class="fas fa-piggy-bank"></i> Ahorras $<?php echo number_format($producto['price'] - $producto['precio_con_descuento'], 2); ?>
                    </p>
                    <?php else: ?>
                    <div class="display-4 text-primary fw-bold">
                        $<?php echo number_format($producto['price'], 2); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Detalles del producto -->
            <div class="mb-4">
                <h5 class="fw-bold mb-3">Detalles del Producto</h5>
                <table class="table table-sm">
                    <tbody>
                        <?php if ($producto['model_year']): ?>
                        <tr>
                            <td class="fw-bold" style="width: 40%;"><i class="fas fa-calendar"></i> Año del Modelo:</td>
                            <td><?php echo $producto['model_year']; ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="fw-bold"><i class="fas fa-box"></i> Stock Disponible:</td>
                            <td>
                                <?php if ($producto['stock_disponible'] > 10): ?>
                                    <span class="badge bg-success"><?php echo $producto['stock_disponible']; ?> unidades</span>
                                <?php elseif ($producto['stock_disponible'] > 0): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-exclamation-triangle"></i> Solo <?php echo $producto['stock_disponible']; ?> unidades disponibles
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Agotado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold"><i class="fas fa-barcode"></i> SKU:</td>
                            <td>PROD-<?php echo str_pad($producto['product_id'], 5, '0', STR_PAD_LEFT); ?></td>
                        </tr>
                        <?php if ($producto['ventas_totales'] > 0): ?>
                        <tr>
                            <td class="fw-bold"><i class="fas fa-shopping-cart"></i> Unidades Vendidas:</td>
                            <td><?php echo $producto['ventas_totales']; ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Selección de cantidad y agregar al carrito -->
            <?php if ($producto['stock_disponible'] > 0): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Cantidad:</label>
                            <input type="number" id="cantidad" class="form-control form-control-lg" 
                                   value="1" min="1" max="<?php echo $producto['stock_disponible']; ?>">
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <button class="btn btn-primary btn-lg w-100" onclick="agregarAlCarrito()">
                                <i class="fas fa-cart-plus fa-lg"></i> Agregar al Carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botones adicionales -->
            <div class="d-grid gap-2">
                <a href="/Bike_Store/cliente/pages/carrito.php" class="btn btn-outline-success btn-lg">
                    <i class="fas fa-shopping-cart"></i> Ver mi Carrito
                </a>
                <a href="/Bike_Store/cliente/pages/catalogo.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Seguir Comprando
                </a>
            </div>
            <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Este producto está agotado actualmente.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Productos Relacionados -->
    <?php if (!empty($productosRelacionados)): ?>
    <div class="mt-5">
        <h3 class="fw-bold mb-4">Productos Relacionados</h3>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php foreach ($productosRelacionados as $relacionado): ?>
            <div class="col">
                <div class="card h-100 shadow-sm hover-card">
                    <?php if ($relacionado['foto']): ?>
                    <img src="/Bike_Store/uploads/<?php echo htmlspecialchars($relacionado['foto']); ?>" 
                         class="card-img-top p-3" 
                         alt="<?php echo htmlspecialchars($relacionado['product_name']); ?>"
                         style="height: 200px; object-fit: contain;">
                    <?php else: ?>
                    <div class="card-img-top p-3 bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-bicycle fa-4x text-muted opacity-25"></i>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($relacionado['descuento'] > 0): ?>
                    <div class="position-absolute top-0 end-0 m-2">
                        <span class="badge bg-danger">-<?php echo number_format($relacionado['descuento'], 0); ?>%</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h6 class="card-title fw-bold"><?php echo htmlspecialchars($relacionado['product_name']); ?></h6>
                        
                        <div class="mb-2">
                            <?php if ($relacionado['descuento'] > 0): ?>
                            <div class="text-muted small text-decoration-line-through">
                                $<?php echo number_format($relacionado['price'], 2); ?>
                            </div>
                            <div class="h5 text-success mb-0">
                                $<?php echo number_format($relacionado['precio_con_descuento'], 2); ?>
                            </div>
                            <?php else: ?>
                            <div class="h5 text-primary mb-0">
                                $<?php echo number_format($relacionado['price'], 2); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <a href="producto.php?id=<?php echo $relacionado['product_id']; ?>" 
                           class="btn btn-outline-primary btn-sm w-100">
                            Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    .hover-card {
        transition: all 0.3s ease;
    }
    
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
</style>

<script>
    function agregarAlCarrito() {
        const cantidad = parseInt(document.getElementById('cantidad').value);
        const productId = <?php echo $producto['product_id']; ?>;
        const stockDisponible = <?php echo $producto['stock_disponible']; ?>;
        
        if (cantidad < 1 || cantidad > stockDisponible) {
            alert('Cantidad no válida. Stock disponible: ' + stockDisponible);
            return;
        }
        
        fetch('/Bike_Store/api/carrito_add.php', {
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
                actualizarContadorCarrito();
                mostrarMensaje(`✓ ${cantidad} unidad(es) agregadas al carrito`, 'success');
            } else {
                mostrarMensaje('Error: ' + (data.message || 'No se pudo agregar el producto'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error al agregar al carrito', 'error');
        });
    }
    
    function mostrarMensaje(mensaje, tipo) {
        const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
        const alerta = document.createElement('div');
        alerta.className = `alert ${alertClass} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        alerta.style.zIndex = '9999';
        alerta.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alerta);
        
        setTimeout(() => {
            alerta.remove();
        }, 3000);
    }
</script>

<?php include __DIR__ . '/../components/footer_publico.php'; ?>
