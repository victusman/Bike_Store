<?php
session_start();
require_once __DIR__ . '/../db.php';

// Configurar título de la página
$pageTitle = 'Inicio - Bike Store';

// Obtener productos destacados para el carrusel (máximo 5)
$stmtDestacados = $pdo->query("SELECT p.*, c.descripcion AS categoria, 
                               ROUND(p.price * (1 - COALESCE(p.descuento,0) / 100), 2) AS precio_con_descuento
                               FROM productos p 
                               LEFT JOIN categoria c ON p.category_id = c.category_id 
                               WHERE p.destacado = 1 
                               AND (p.stock_disponible > 0 OR p.stock_disponible IS NULL)
                               ORDER BY p.product_id DESC
                               LIMIT 5");
$productosDestacados = $stmtDestacados->fetchAll();

// Obtener productos más vendidos (máximo 4) - basado en ventas reales
$stmtVendidos = $pdo->query("SELECT p.*, c.descripcion AS categoria,
                             ROUND(p.price * (1 - COALESCE(p.descuento,0) / 100), 2) AS precio_con_descuento,
                             COALESCE(p.ventas_totales, 0) AS total_vendido
                             FROM productos p 
                             LEFT JOIN categoria c ON p.category_id = c.category_id 
                             WHERE (p.stock_disponible > 0 OR p.stock_disponible IS NULL)
                             ORDER BY COALESCE(p.ventas_totales, 0) DESC, p.product_id DESC
                             LIMIT 4");
$productosMasVendidos = $stmtVendidos->fetchAll();

// Si no hay productos destacados, usar los primeros 5 productos
if (empty($productosDestacados)) {
    $stmtDestacados = $pdo->query("SELECT p.*, c.descripcion AS categoria, 
                                   ROUND(p.price * (1 - p.descuento / 100), 2) AS precio_con_descuento
                                   FROM productos p 
                                   LEFT JOIN categoria c ON p.category_id = c.category_id 
                                   WHERE p.stock_disponible > 0 
                                   LIMIT 5");
    $productosDestacados = $stmtDestacados->fetchAll();
}

// Si no hay productos más vendidos, usar productos aleatorios
if (empty($productosMasVendidos)) {
    $stmtVendidos = $pdo->query("SELECT p.*, c.descripcion AS categoria,
                                 ROUND(p.price * (1 - p.descuento / 100), 2) AS precio_con_descuento
                                 FROM productos p 
                                 LEFT JOIN categoria c ON p.category_id = c.category_id 
                                 WHERE p.stock_disponible > 0 
                                 ORDER BY RAND()
                                 LIMIT 4");
    $productosMasVendidos = $stmtVendidos->fetchAll();
}

include __DIR__ . '/components/header_publico.php';
?>

<!-- Hero Banner -->
<div class="bg-primary text-white py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Bienvenido a Bike Store</h1>
        <p class="lead mb-4">Encuentra las mejores bicicletas y accesorios para tu aventura</p>
        <a href="/Bike_Store/cliente/pages/catalogo.php" class="btn btn-light btn-lg">
            <i class="fas fa-shopping-bag"></i> Ver Catálogo
        </a>
    </div>
</div>

<!-- Carrusel de Productos Destacados -->
<div class="container my-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold">Productos Destacados</h2>
        <p class="text-muted">Las mejores ofertas y productos de la temporada</p>
    </div>
    
    <?php if (!empty($productosDestacados)): ?>
    <div id="carouselDestacados" class="carousel slide shadow-lg rounded" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($productosDestacados as $index => $producto): ?>
            <button type="button" data-bs-target="#carouselDestacados" data-bs-slide-to="<?php echo $index; ?>" 
                    class="<?php echo $index === 0 ? 'active' : ''; ?>"></button>
            <?php endforeach; ?>
        </div>
        
        <div class="carousel-inner">
            <?php foreach ($productosDestacados as $index => $producto): ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <div class="row g-0 align-items-center" style="min-height: 400px; background: linear-gradient(to right, #f8f9fa 50%, #e9ecef 50%);">
                    <div class="col-md-6 p-5">
                        <div class="p-4">
                            <?php if ($producto['descuento'] > 0): ?>
                            <span class="discount-badge mb-2 d-inline-block">
                                <i class="fas fa-tag"></i> <?php echo number_format($producto['descuento'], 0); ?>% OFF
                            </span>
                            <?php endif; ?>
                            
                            <h3 class="fw-bold mb-3"><?php echo htmlspecialchars($producto['product_name']); ?></h3>
                            
                            <?php if ($producto['categoria']): ?>
                            <p class="text-muted mb-2">
                                <i class="fas fa-tags"></i> <?php echo htmlspecialchars($producto['categoria']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if ($producto['model_year']): ?>
                            <p class="text-muted mb-3">
                                <i class="fas fa-calendar"></i> Modelo <?php echo $producto['model_year']; ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="mb-4">
                                <?php if ($producto['descuento'] > 0): ?>
                                <span class="text-muted text-decoration-line-through me-2">
                                    $<?php echo number_format($producto['price'], 2); ?>
                                </span>
                                <span class="h3 text-success fw-bold">
                                    $<?php echo number_format($producto['precio_con_descuento'], 2); ?>
                                </span>
                                <?php else: ?>
                                <span class="h3 text-primary fw-bold">
                                    $<?php echo number_format($producto['price'], 2); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex">
                                <a href="/Bike_Store/cliente/pages/producto.php?id=<?php echo $producto['product_id']; ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-eye"></i> Ver Detalles
                                </a>
                                <button class="btn btn-primary" onclick="agregarAlCarrito(<?php echo $producto['product_id']; ?>)">
                                    <i class="fas fa-cart-plus"></i> Agregar al Carrito
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if ($producto['foto']): ?>
                        <img src="/Bike_Store/uploads/<?php echo htmlspecialchars($producto['foto']); ?>" 
                             class="d-block w-100 p-5" 
                             alt="<?php echo htmlspecialchars($producto['product_name']); ?>"
                             style="object-fit: contain; max-height: 400px;">
                        <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center p-5" style="height: 400px;">
                            <i class="fas fa-bicycle fa-10x text-muted opacity-25"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselDestacados" data-bs-slide="prev">
            <span class="carousel-control-prev-icon bg-dark rounded-circle p-3"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselDestacados" data-bs-slide="next">
            <span class="carousel-control-next-icon bg-dark rounded-circle p-3"></span>
        </button>
    </div>
    <?php else: ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle"></i> No hay productos destacados en este momento.
    </div>
    <?php endif; ?>
</div>

<!-- Productos Más Vendidos -->
<div class="container my-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold">
            <i class="fas fa-fire text-danger"></i> Productos Más Vendidos
        </h2>
        <p class="text-muted">Los favoritos de nuestros clientes que no puedes perderte</p>
    </div>
    
    <?php if (!empty($productosMasVendidos)): ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php foreach ($productosMasVendidos as $producto): ?>
        <div class="col">
            <div class="card h-100 shadow-sm hover-shadow">
                <!-- Imagen del producto -->
                <?php if ($producto['foto']): ?>
                <img src="/Bike_Store/uploads/<?php echo htmlspecialchars($producto['foto']); ?>" 
                     class="card-img-top p-3" 
                     alt="<?php echo htmlspecialchars($producto['product_name']); ?>"
                     style="height: 200px; object-fit: contain;">
                <?php else: ?>
                <div class="card-img-top p-3 bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="fas fa-bicycle fa-4x text-muted opacity-25"></i>
                </div>
                <?php endif; ?>
                
                <!-- Badge de descuento y más vendido -->
                <div class="position-absolute top-0 end-0 m-2">
                    <?php if ($producto['descuento'] > 0): ?>
                    <span class="discount-badge mb-1">-<?php echo number_format($producto['descuento'], 0); ?>%</span>
                    <br>
                    <?php endif; ?>
                    
                    <?php if ($producto['total_vendido'] > 0): ?>
                    <span class="badge bg-danger">
                        <i class="fas fa-fire"></i> Más Vendido
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title fw-bold"><?php echo htmlspecialchars($producto['product_name']); ?></h6>
                    
                    <?php if ($producto['categoria']): ?>
                    <p class="text-muted small mb-1">
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($producto['categoria']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <?php if ($producto['model_year']): ?>
                    <p class="text-muted small mb-1">
                        Año: <?php echo $producto['model_year']; ?>
                    </p>
                    <?php endif; ?>
                    
                    <!-- Mostrar total vendido -->
                    <?php if ($producto['total_vendido'] > 0): ?>
                    <p class="text-success small mb-2">
                        <i class="fas fa-fire"></i> <?php echo $producto['total_vendido']; ?> vendidos
                    </p>
                    <?php endif; ?>
                    
                    <div class="mt-auto">
                        <div class="mb-2">
                            <?php if ($producto['descuento'] > 0): ?>
                            <div class="text-muted small text-decoration-line-through">
                                $<?php echo number_format($producto['price'], 2); ?>
                            </div>
                            <div class="h5 text-success mb-0">
                                $<?php echo number_format($producto['precio_con_descuento'], 2); ?>
                            </div>
                            <?php else: ?>
                            <div class="h5 text-primary mb-0">
                                $<?php echo number_format($producto['price'], 2); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-sm" onclick="agregarAlCarrito(<?php echo $producto['product_id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Agregar
                            </button>
                            <a href="/Bike_Store/cliente/pages/producto.php?id=<?php echo $producto['product_id']; ?>" 
                               class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-eye"></i> Detalles
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle"></i> No hay productos disponibles en este momento.
    </div>
    <?php endif; ?>
</div>

<!-- Sección de beneficios -->
<div class="bg-light py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="p-3">
                    <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                    <h5>Envío Gratis</h5>
                    <p class="text-muted small">En compras mayores a $500</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="p-3">
                    <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                    <h5>Compra Segura</h5>
                    <p class="text-muted small">Protección al 100%</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="p-3">
                    <i class="fas fa-undo fa-3x text-warning mb-3"></i>
                    <h5>Devoluciones</h5>
                    <p class="text-muted small">30 días para devolver</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="p-3">
                    <i class="fas fa-headset fa-3x text-info mb-3"></i>
                    <h5>Soporte 24/7</h5>
                    <p class="text-muted small">Siempre disponibles</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .carousel-item {
        transition: transform 0.6s ease-in-out;
    }
</style>

<script>
    function agregarAlCarrito(productId) {
        fetch('/Bike_Store/api/carrito_add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar contador del carrito
                actualizarContadorCarrito();
                
                // Mostrar mensaje de éxito
                alert('✓ Producto agregado al carrito');
            } else {
                alert('Error: ' + (data.message || 'No se pudo agregar el producto'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al agregar al carrito');
        });
    }
</script>

<?php include __DIR__ . '/components/footer_publico.php'; ?>
