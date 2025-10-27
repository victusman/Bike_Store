<?php
session_start();
require_once __DIR__ . '/../../db.php';

$pageTitle = 'Catálogo de Productos - Bike Store';

// Obtener parámetros de búsqueda y filtros
$buscar = $_GET['buscar'] ?? '';
$categoria_id = $_GET['categoria'] ?? '';
$orden = $_GET['orden'] ?? 'recientes'; // recientes, precio_asc, precio_desc, nombre

// Construir consulta base
$sql = "SELECT p.*, c.descripcion AS categoria,
        ROUND(p.price * (1 - p.descuento / 100), 2) AS precio_con_descuento
        FROM productos p
        LEFT JOIN categoria c ON p.category_id = c.category_id
        WHERE p.stock_disponible > 0";

$params = [];

// Filtro de búsqueda
if ($buscar) {
    $sql .= " AND p.product_name LIKE :buscar";
    $params['buscar'] = "%$buscar%";
}

// Filtro de categoría
if ($categoria_id) {
    $sql .= " AND p.category_id = :categoria";
    $params['categoria'] = $categoria_id;
}

// Ordenamiento
switch ($orden) {
    case 'precio_asc':
        $sql .= " ORDER BY precio_con_descuento ASC";
        break;
    case 'precio_desc':
        $sql .= " ORDER BY precio_con_descuento DESC";
        break;
    case 'nombre':
        $sql .= " ORDER BY p.product_name ASC";
        break;
    case 'descuento':
        $sql .= " ORDER BY p.descuento DESC";
        break;
    default:
        $sql .= " ORDER BY p.product_id DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// Obtener todas las categorías ordenadas alfabéticamente
$stmtCategorias = $pdo->query("SELECT * FROM categoria ORDER BY descripcion ASC");
$categorias = $stmtCategorias->fetchAll();

include __DIR__ . '/../components/header_publico.php';
?>

<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/Bike_Store/cliente/index.php">Inicio</a></li>
            <li class="breadcrumb-item active">Catálogo de Productos</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Sidebar de Filtros -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filtros</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="">
                        <!-- Búsqueda -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Buscar</label>
                            <input type="text" name="buscar" class="form-control" 
                                   placeholder="Nombre del producto..." 
                                   value="<?php echo htmlspecialchars($buscar); ?>">
                        </div>

                        <!-- Categorías -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Categoría</label>
                            <select name="categoria" class="form-select">
                                <option value="">Todas las categorías</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" 
                                        <?php echo $categoria_id == $cat['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['descripcion']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Ordenar por -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ordenar por</label>
                            <select name="orden" class="form-select">
                                <option value="recientes" <?php echo $orden === 'recientes' ? 'selected' : ''; ?>>Más recientes</option>
                                <option value="nombre" <?php echo $orden === 'nombre' ? 'selected' : ''; ?>>Nombre (A-Z)</option>
                                <option value="precio_asc" <?php echo $orden === 'precio_asc' ? 'selected' : ''; ?>>Precio: menor a mayor</option>
                                <option value="precio_desc" <?php echo $orden === 'precio_desc' ? 'selected' : ''; ?>>Precio: mayor a menor</option>
                                <option value="descuento" <?php echo $orden === 'descuento' ? 'selected' : ''; ?>>Mayor descuento</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Aplicar Filtros
                            </button>
                            <a href="/Bike_Store/cliente/pages/catalogo.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Categorías como lista -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-tags"></i> Categorías</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="/Bike_Store/cliente/pages/catalogo.php" 
                       class="list-group-item list-group-item-action <?php echo !$categoria_id ? 'active' : ''; ?>">
                        <i class="fas fa-th"></i> Todas las categorías
                    </a>
                    <?php foreach ($categorias as $cat): ?>
                    <a href="?categoria=<?php echo $cat['category_id']; ?>" 
                       class="list-group-item list-group-item-action <?php echo $categoria_id == $cat['category_id'] ? 'active' : ''; ?>">
                        <i class="fas fa-bicycle"></i> <?php echo htmlspecialchars($cat['descripcion']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Lista de Productos -->
        <div class="col-md-9">
            <!-- Encabezado con contador -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">
                        <?php if ($categoria_id): ?>
                            <?php
                            $catActual = array_filter($categorias, fn($c) => $c['category_id'] == $categoria_id);
                            $catActual = reset($catActual);
                            echo htmlspecialchars($catActual['descripcion'] ?? 'Productos');
                            ?>
                        <?php else: ?>
                            Todos los Productos
                        <?php endif; ?>
                    </h2>
                    <p class="text-muted mb-0">
                        <?php echo count($productos); ?> producto<?php echo count($productos) != 1 ? 's' : ''; ?> encontrado<?php echo count($productos) != 1 ? 's' : ''; ?>
                    </p>
                </div>
            </div>

            <?php if (!empty($productos)): ?>
            <!-- Grid de productos -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($productos as $producto): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm hover-card">
                        <!-- Imagen del producto -->
                        <div class="position-relative">
                            <?php if ($producto['foto']): ?>
                            <img src="/Bike_Store/uploads/<?php echo htmlspecialchars($producto['foto']); ?>" 
                                 class="card-img-top p-3" 
                                 alt="<?php echo htmlspecialchars($producto['product_name']); ?>"
                                 style="height: 220px; object-fit: contain; cursor: pointer;"
                                 onclick="window.location.href='producto.php?id=<?php echo $producto['product_id']; ?>'">
                            <?php else: ?>
                            <div class="card-img-top p-3 bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 220px; cursor: pointer;"
                                 onclick="window.location.href='producto.php?id=<?php echo $producto['product_id']; ?>'">
                                <i class="fas fa-bicycle fa-5x text-muted opacity-25"></i>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Badges -->
                            <?php if ($producto['descuento'] > 0): ?>
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-danger">-<?php echo number_format($producto['descuento'], 0); ?>%</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($producto['stock_disponible'] < 10): ?>
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-exclamation-triangle"></i> Últimas unidades
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <!-- Categoría -->
                            <?php if ($producto['categoria']): ?>
                            <small class="text-muted mb-1">
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($producto['categoria']); ?>
                            </small>
                            <?php endif; ?>
                            
                            <!-- Nombre del producto -->
                            <h6 class="card-title fw-bold mb-2">
                                <a href="producto.php?id=<?php echo $producto['product_id']; ?>" 
                                   class="text-decoration-none text-dark stretched-link-custom">
                                    <?php echo htmlspecialchars($producto['product_name']); ?>
                                </a>
                            </h6>
                            
                            <!-- Año del modelo -->
                            <?php if ($producto['model_year']): ?>
                            <small class="text-muted mb-2">
                                <i class="fas fa-calendar"></i> Modelo <?php echo $producto['model_year']; ?>
                            </small>
                            <?php endif; ?>
                            
                            <!-- Stock -->
                            <small class="text-muted mb-3">
                                <i class="fas fa-box"></i> Stock: <?php echo $producto['stock_disponible']; ?> unidades
                            </small>
                            
                            <div class="mt-auto">
                                <!-- Precio -->
                                <div class="mb-3">
                                    <?php if ($producto['descuento'] > 0): ?>
                                    <div class="text-muted small text-decoration-line-through">
                                        $<?php echo number_format($producto['price'], 2); ?>
                                    </div>
                                    <div class="h5 text-success fw-bold mb-0">
                                        $<?php echo number_format($producto['precio_con_descuento'], 2); ?>
                                    </div>
                                    <small class="text-success">
                                        Ahorra $<?php echo number_format($producto['price'] - $producto['precio_con_descuento'], 2); ?>
                                    </small>
                                    <?php else: ?>
                                    <div class="h5 text-primary fw-bold mb-0">
                                        $<?php echo number_format($producto['price'], 2); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Botones -->
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary btn-sm position-relative" 
                                            onclick="agregarAlCarrito(<?php echo $producto['product_id']; ?>)">
                                        <i class="fas fa-cart-plus"></i> Agregar al Carrito
                                    </button>
                                    <a href="producto.php?id=<?php echo $producto['product_id']; ?>" 
                                       class="btn btn-outline-secondary btn-sm position-relative">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <!-- Sin resultados -->
            <div class="text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h4>No se encontraron productos</h4>
                <p class="text-muted">Intenta cambiar los filtros o realizar otra búsqueda</p>
                <a href="/Bike_Store/cliente/pages/catalogo.php" class="btn btn-primary">
                    <i class="fas fa-redo"></i> Ver todos los productos
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .hover-card {
        transition: all 0.3s ease;
        position: relative;
    }
    
    .hover-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important;
    }
    
    .stretched-link-custom::after {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 0;
        content: "";
    }
    
    .card-body button,
    .card-body a.btn {
        position: relative;
        z-index: 1;
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
                mostrarMensaje('✓ Producto agregado al carrito', 'success');
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
