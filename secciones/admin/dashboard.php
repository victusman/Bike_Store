<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../bd.php';

$pageTitle = 'Dashboard - Admin - Bike Store';

// Metric: total sales
$stmt = $pdo->query("SELECT COALESCE(SUM(total_amount),0) AS total_sales FROM orders");
$totalSales = $stmt->fetchColumn();

// Metric: total orders
$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$ordersCount = (int)$stmt->fetchColumn();

// Metric: total customers
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM customer");
    $customersCount = (int)$stmt->fetchColumn();
} catch (Exception $e) {
    $customersCount = 0;
}

// New customers last 30 days (if created_at exists)
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM customer WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $newCustomers30 = (int)$stmt->fetchColumn();
} catch (Exception $e) {
    // fallback: 0
    $newCustomers30 = 0;
}

// Top products
$stmt = $pdo->query("SELECT p.product_name, COALESCE(SUM(oi.quantity),0) AS sold FROM order_items oi JOIN productos p ON oi.product_id = p.product_id GROUP BY p.product_id ORDER BY sold DESC LIMIT 5");
$topProducts = $stmt->fetchAll();

// Sales per month (last 12 months)
$stmt = $pdo->query("SELECT DATE_FORMAT(order_date, '%Y-%m') AS ym, COALESCE(SUM(total_amount),0) AS total FROM orders GROUP BY ym ORDER BY ym ASC LIMIT 12");
$salesByMonthRows = $stmt->fetchAll();
$salesByMonth = [];
$months = [];
$salesValues = [];
foreach ($salesByMonthRows as $r) {
    $months[] = $r['ym'];
    $salesValues[] = floatval($r['total']);
}

// Payment method distribution
$stmt = $pdo->query("SELECT metodo_pago AS metodo, COUNT(*) AS cnt FROM orders GROUP BY metodo_pago");
$paymentDistRows = $stmt->fetchAll();
$paymentLabels = [];
$paymentCounts = [];
foreach ($paymentDistRows as $r) {
    $paymentLabels[] = $r['metodo'];
    $paymentCounts[] = (int)$r['cnt'];
}

include __DIR__ . '/../../templates/header.php';
?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Dashboard administrativo</h1>
        <small class="text-muted">Visión general</small>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Total ventas</h6>
                <div class="h4">$<?php echo number_format($totalSales,2); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Pedidos</h6>
                <div class="h4"><?php echo $ordersCount; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Clientes</h6>
                <div class="h4"><?php echo $customersCount; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Nuevos (30d)</h6>
                <div class="h4"><?php echo $newCustomers30; ?></div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card p-3 mb-3">
                <h6>Ventas por mes</h6>
                <canvas id="chartSales" height="120"></canvas>
            </div>
            <div class="card p-3 mb-3">
                <h6>Top productos</h6>
                <canvas id="chartTopProducts" height="120"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-3 mb-3">
                <h6>Métodos de pago</h6>
                <canvas id="chartPayment" height="220"></canvas>
            </div>
            <div class="card p-3">
                <h6>Top productos (lista)</h6>
                <ul class="list-group">
                    <?php foreach ($topProducts as $tp): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($tp['product_name']); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $tp['sold']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const months = <?php echo json_encode($months); ?>;
const salesValues = <?php echo json_encode($salesValues); ?>;
const paymentLabels = <?php echo json_encode($paymentLabels); ?>;
const paymentCounts = <?php echo json_encode($paymentCounts); ?>;
const topProductsLabels = <?php echo json_encode(array_column($topProducts, 'product_name')); ?>;
const topProductsValues = <?php echo json_encode(array_map('intval', array_column($topProducts, 'sold'))); ?>;

// Sales line
new Chart(document.getElementById('chartSales'), {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Ventas',
            data: salesValues,
            borderColor: '#3498db',
            backgroundColor: 'rgba(52,152,219,0.2)',
            fill: true,
            tension: 0.3
        }]
    }
});

// Top products bar
new Chart(document.getElementById('chartTopProducts'), {
    type: 'bar',
    data: {
        labels: topProductsLabels,
        datasets: [{
            label: 'Unidades vendidas',
            data: topProductsValues,
            backgroundColor: '#2c3e50'
        }]
    }
});

// Payment pie
new Chart(document.getElementById('chartPayment'), {
    type: 'pie',
    data: {
        labels: paymentLabels,
        datasets: [{
            data: paymentCounts,
            backgroundColor: ['#3498db','#27ae60','#f1c40f','#e67e22','#e74c3c']
        }]
    }
});
</script>

<?php include __DIR__ . '/../../templates/footer.php';
