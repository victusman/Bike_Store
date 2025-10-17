<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

$stmt = $pdo->query('SELECT o.order_id, o.order_date, o.estado, o.customer_id, o.user_id, c.first_name, c.last_name FROM orders o LEFT JOIN customer c ON o.customer_id = c.customer_id ORDER BY o.order_id DESC');
$orders = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
	<h2 class="mb-0">Lista de Pedidos</h2>
	<a class="btn btn-primary" href="crear.php">Nuevo</a>
</div>
<div class="table-responsive">
<table class="table table-striped table-hover align-middle">
	<thead class="table-light"><tr><th>ID</th><th>Fecha de pedido</th><th>Cliente</th><th>Estado</th><th>Usuario</th><th>Acciones</th></tr></thead>
	<tbody>
	<?php foreach($orders as $o): ?>
	<tr>
		<td><?php echo $o['order_id']; ?></td>
		<td><?php echo htmlspecialchars($o['order_date']); ?></td>
		<td><?php echo $o['first_name'] ? htmlspecialchars($o['first_name'] . ' ' . $o['last_name']) : $o['customer_id']; ?></td>
		<td><?php echo htmlspecialchars($o['estado']); ?></td>
		<td><?php echo htmlspecialchars($o['user_id']); ?></td>
		<td>
			<a class="btn btn-sm btn-outline-primary" href="ver.php?id=<?php echo $o['order_id']; ?>">Pedido Detalle</a>
			<a class="btn btn-sm btn-outline-danger" href="anular.php?id=<?php echo $o['order_id']; ?>" onclick="return confirm('Anular este pedido?');">Anular</a>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>
</div>

<?php include __DIR__ . '/../../templates/footer.php'; ?>

