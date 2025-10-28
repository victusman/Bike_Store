<?php
session_start();

// Si hay un admin autenticado, mostrar la vista administrativa (dashboard)
if (!empty($_SESSION['user_id'])) {
    include __DIR__ . '/templates/header.php';

    // Conexión a la base de datos (para métricas del dashboard)
    require_once __DIR__ . '/db.php';

    // Consultas seguras con manejo de errores
    try {
        // tabla se llama `productos` en el esquema
        $countProducts = (int) $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
    } catch (Exception $e) { $countProducts = 0; }
    try {
        // tabla de clientes se llama `customer` en el esquema SQL provisto
        $countCustomers = (int) $pdo->query("SELECT COUNT(*) FROM customer")->fetchColumn();
    } catch (Exception $e) { $countCustomers = 0; }
    try {
        $countOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    } catch (Exception $e) { $countOrders = 0; }
    try {
        $totalSales = $pdo->query("SELECT IFNULL(SUM(price * quantity),0) FROM order_items")->fetchColumn();
        $totalSales = number_format((float)$totalSales, 2);
    } catch (Exception $e) { $totalSales = '0.00'; }

    $displayUser = htmlspecialchars($_SESSION['usuario'] ?? 'Administrador');
    ?>

    <!-- Dashboard styles -->
    <link rel="stylesheet" href="/Bike_Store/assets/css/dashboard.css">

    <div class="dashboard-container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-0">Panel de Control</h2>
                <small class="text-muted">Bienvenido, <?php echo $displayUser; ?></small>
            </div>
            <div>
                <a href="/Bike_Store/secciones/Orders/index.php" class="btn btn-outline-primary me-2">Ver Pedidos</a>
                <a href="/Bike_Store/secciones/Productos/index.php" class="btn btn-primary">Gestionar Productos</a>
            </div>
        </div>

        <div class="row g-3">
            <?php
            // Obtener más métricas (categorías, pendientes, entregados, stock bajo)
            try {
                // la tabla de categorías en el SQL se llama `categoria`
                $countCategories = (int) $pdo->query("SELECT COUNT(*) FROM categoria")->fetchColumn();
            } catch (Exception $e) {
                try { $countCategories = (int) $pdo->query("SELECT COUNT(*) FROM Categorias")->fetchColumn(); } catch (Exception $e2) { $countCategories = 0; }
            }

            try {
                $pendingOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE estado IN ('pendiente','PENDIENTE','pending')")->fetchColumn();
            } catch (Exception $e) { $pendingOrders = 0; }
            try {
                $deliveredOrders = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE estado IN ('entregado','ENTREGADO','delivered')")->fetchColumn();
            } catch (Exception $e) { $deliveredOrders = 0; }
            try {
                $lowStock = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE stock IS NOT NULL AND stock <= 5")->fetchColumn();
            } catch (Exception $e) { $lowStock = 0; }

            // Top products
            try {
                // Ajuste: tabla de productos es `productos` y la PK es product_id
                $topStmt = $pdo->query("SELECT p.product_name AS name, SUM(oi.quantity) AS sold, IFNULL(SUM(oi.quantity * oi.price),0) AS revenue FROM order_items oi JOIN productos p ON oi.product_id = p.product_id GROUP BY p.product_id ORDER BY sold DESC LIMIT 5");
                $topProducts = $topStmt->fetchAll();
            } catch (Exception $e) { $topProducts = []; }
            ?>

            <div class="col-md-3">
                <div class="card metric-card border-left-blue">
                    <div class="card-body">
                        <h6 class="card-title">Total Productos</h6>
                        <p class="metric-value"><?php echo $countProducts; ?></p>
                        <p class="text-muted small">Catálogo</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card border-left-purple">
                    <div class="card-body">
                        <h6 class="card-title">Categorías</h6>
                        <p class="metric-value"><?php echo $countCategories; ?></p>
                        <p class="text-muted small">Categorías activas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card border-left-yellow">
                    <div class="card-body">
                        <h6 class="card-title">Clientes registrados</h6>
                        <p class="metric-value"><?php echo $countCustomers; ?></p>
                        <p class="text-muted small">Usuarios</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card border-left-blue">
                    <div class="card-body">
                        <h6 class="card-title">Total Pedidos</h6>
                        <p class="metric-value"><?php echo $countOrders; ?></p>
                        <p class="text-muted small">Pedidos recibidos</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card metric-card border-left-red">
                    <div class="card-body">
                        <h6 class="card-title">Pedidos pendientes</h6>
                        <p class="metric-value"><?php echo $pendingOrders; ?></p>
                        <p class="text-muted small">Por procesar</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card border-left-green">
                    <div class="card-body">
                        <h6 class="card-title">Pedidos entregados</h6>
                        <p class="metric-value"><?php echo $deliveredOrders; ?></p>
                        <p class="text-muted small">Entregados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card border-left-green">
                    <div class="card-body">
                        <h6 class="card-title">Total Ventas</h6>
                        <p class="metric-value">$<?php echo $totalSales; ?></p>
                        <p class="text-muted small">Suma aproximada</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card border-left-yellow">
                    <div class="card-body">
                        <h6 class="card-title">Productos stock bajo</h6>
                        <p class="metric-value"><?php echo $lowStock; ?></p>
                        <p class="text-muted small">Stock ≤ 5</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="quick-actions">
            <a href="/Bike_Store/secciones/Productos/index.php" class="btn btn-outline btn-outline-primary">📦 Gestionar Productos</a>
            <a href="/Bike_Store/secciones/orders/index.php" class="btn btn-green">📋 Ver Pedidos</a>
            <a href="/Bike_Store/secciones/Usuarios/index.php" class="btn btn-yellow">👥 Gestionar Usuarios</a>
            <a href="/Bike_Store/secciones/stocks/index.php" class="btn btn-outline">🔧 Control de Stock</a>
        </div>

        <div class="dashboard-main">
            <div class="dashboard-left">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Últimos Pedidos</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="latest-orders-body">
                                <?php
                                // Obtener últimos 8 pedidos con total calculado desde order_items
                                try {
                                    $stmt = $pdo->query("SELECT o.order_id, o.order_date, o.estado, c.first_name, c.last_name, (SELECT IFNULL(SUM(oi.price * oi.quantity),0) FROM order_items oi WHERE oi.order_id = o.order_id) AS total FROM orders o LEFT JOIN customer c ON o.customer_id = c.customer_id ORDER BY o.created_at DESC LIMIT 8");
                                    $recent = $stmt->fetchAll();
                                } catch (Exception $e) { $recent = []; }

                                if (!empty($recent)) {
                                    foreach ($recent as $r) {
                                        $cliente = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: 'Sin cliente';
                                        $fecha = !empty($r['order_date']) ? date('d/m/Y', strtotime($r['order_date'])) : '-';
                                        $estado = $r['estado'] ?? '-';
                                        $total = isset($r['total']) ? ('$' . number_format($r['total'],2)) : '-';
                                        echo "<tr><td>#" . htmlspecialchars($r['order_id']) . "</td><td>" . htmlspecialchars($cliente) . "</td><td>" . htmlspecialchars($fecha) . "</td><td><span class='badge bg-success'>" . htmlspecialchars($estado) . "</span></td><td class='text-end'>" . htmlspecialchars($total) . "</td></tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan=5 class='text-muted'>No hay pedidos recientes</td></tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-right">
                <div class="card top-products">
                    <div class="card-body">
                        <h5 class="card-title">Productos Más Vendidos</h5>
                        <?php
                        if (!empty($topProducts)) {
                            $rank = 1;
                            foreach ($topProducts as $tp) {
                                $name = htmlspecialchars($tp['name'] ?? 'Producto');
                                $sold = (int)($tp['sold'] ?? 0);
                                $revenue = isset($tp['revenue']) ? '$' . number_format($tp['revenue'],2) : '';
                                echo "<div class='product-item'><div><strong>" . $rank++ . ". " . $name . "</strong><div class='meta'>" . $sold . " unidades vendidas</div></div><div class='badge'>" . $revenue . "</div></div>";
                            }
                        } else {
                            echo "<div class='text-muted'>No hay datos de productos vendidos</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="/Bike_Store/assets/js/dashboard.js"></script>

    <?php include __DIR__ . '/templates/footer.php';
    exit;
}

// Si no hay admin en sesión, mostrar la tienda pública (cliente)
header('Location: /Bike_Store/cliente/index.php');
exit;
?>