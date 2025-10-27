<?php
session_start();
require_once __DIR__ . '/../../db.php';

// Verificar autenticación
if (!isset($_SESSION['customer_id'])) {
    header('Location: login_cliente.php?redirect=mis_pedidos.php');
    exit;
}

$pageTitle = 'Mis Pedidos - Bike Store';

// Obtener pedidos del cliente
$stmt = $pdo->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) as total_items
    FROM orders o
    WHERE o.customer_id = :customer_id
    ORDER BY o.order_date DESC
");
$stmt->execute(['customer_id' => $_SESSION['customer_id']]);
$pedidos = $stmt->fetchAll();

include __DIR__ . '/../components/header_publico.php';
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/Bike_Store/cliente/index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="perfil.php">Mi Perfil</a></li>
            <li class="breadcrumb-item active">Mis Pedidos</li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-user-circle"></i> Mi Cuenta</h6>
                    <div class="list-group list-group-flush">
                        <a href="perfil.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user"></i> Editar Perfil
                        </a>
                        <a href="mis_pedidos.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-shopping-bag"></i> Mis Pedidos
                        </a>
                        <a href="cerrar_sesion.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenido Principal -->
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-shopping-bag"></i> Mis Pedidos</h4>
                    <span class="badge bg-light text-dark"><?php echo count($pedidos); ?> pedido(s)</span>
                </div>
                
                <div class="card-body p-0">
                    <?php if (count($pedidos) === 0): ?>
                    <!-- Sin Pedidos -->
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No tienes pedidos todavía</h5>
                        <p class="text-muted">¡Explora nuestro catálogo y haz tu primera compra!</p>
                        <a href="/Bike_Store/cliente/pages/catalogo.php" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Ver Productos
                        </a>
                    </div>
                    <?php else: ?>
                    <!-- Lista de Pedidos -->
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Pedido #</th>
                                    <th>Fecha</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary">#<?php echo str_pad($pedido['order_id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar-alt text-muted"></i>
                                        <?php echo date('d/m/Y', strtotime($pedido['order_date'])); ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo date('H:i', strtotime($pedido['order_date'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $pedido['total_items']; ?> item(s)
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            $<?php echo number_format($pedido['total_amount'], 2); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php
                                        $status = $pedido['status'] ?? 'pendiente';
                                        $badge_class = 'secondary';
                                        $icon = 'clock';
                                        
                                        switch ($status) {
                                            case 'completado':
                                            case 'completed':
                                                $badge_class = 'success';
                                                $icon = 'check-circle';
                                                $status_text = 'Completado';
                                                break;
                                            case 'pendiente':
                                            case 'pending':
                                                $badge_class = 'warning';
                                                $icon = 'clock';
                                                $status_text = 'Pendiente';
                                                break;
                                            case 'cancelado':
                                            case 'cancelled':
                                                $badge_class = 'danger';
                                                $icon = 'times-circle';
                                                $status_text = 'Cancelado';
                                                break;
                                            case 'en_proceso':
                                            case 'processing':
                                                $badge_class = 'info';
                                                $icon = 'spinner';
                                                $status_text = 'En Proceso';
                                                break;
                                            default:
                                                $status_text = ucfirst($status);
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo $badge_class; ?>">
                                            <i class="fas fa-<?php echo $icon; ?>"></i> <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="verDetalle(<?php echo $pedido['order_id']; ?>)"
                                                    title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="factura.php?order_id=<?php echo $pedido['order_id']; ?>" 
                                               class="btn btn-sm btn-outline-success" 
                                               target="_blank"
                                               title="Descargar factura">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle de Pedido -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-file-invoice"></i> Detalle del Pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function verDetalle(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
    modal.show();
    
    // Cargar detalle del pedido
    fetch(`/Bike_Store/api/pedido_detalle.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarDetalle(data.pedido, data.items);
            } else {
                document.getElementById('detalleContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Error al cargar el detalle
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('detalleContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Error de conexión
                </div>
            `;
        });
}

function mostrarDetalle(pedido, items) {
    let html = `
        <div class="row mb-3">
            <div class="col-md-6">
                <h6 class="fw-bold">Pedido #${String(pedido.order_id).padStart(6, '0')}</h6>
                <p class="mb-1"><i class="fas fa-calendar"></i> ${new Date(pedido.order_date).toLocaleDateString('es-ES')}</p>
            </div>
            <div class="col-md-6 text-end">
                <span class="badge bg-primary">${pedido.status || 'Pendiente'}</span>
            </div>
        </div>
        
        <h6 class="fw-bold mb-3">Productos</h6>
        <div class="table-responsive mb-3">
            <table class="table table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Precio</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    items.forEach(item => {
        html += `
            <tr>
                <td>${item.product_name || 'Producto'}</td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-end">$${parseFloat(item.list_price).toFixed(2)}</td>
                <td class="text-end">$${(item.quantity * item.list_price).toFixed(2)}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="3" class="text-end fw-bold">TOTAL:</td>
                        <td class="text-end fw-bold text-success">$${parseFloat(pedido.total_amount).toFixed(2)}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
    
    document.getElementById('detalleContent').innerHTML = html;
}
</script>

<style>
    .list-group-item.active {
        background-color: #667eea;
        border-color: #667eea;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>

<?php include __DIR__ . '/../components/footer_publico.php'; ?>
