<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

$stmt = $pdo->query('SELECT * FROM customer ORDER BY customer_id DESC');
$customers = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
		<h2 class="mb-0">Lista de Clientes</h2>
		<a class="btn btn-primary" href="crear.php">Nuevo</a>
</div>
<div class="table-responsive">
<table class="table table-striped table-hover align-middle">
	<thead class="table-light"><tr><th>ID</th><th>Nombres y Apellidos</th><th>Foto</th><th>Telefono</th><th>Correo</th><th>Calle</th><th>Ciudad</th><th>Depa</th><th>Acciones</th></tr></thead>
	<tbody>
	<?php foreach($customers as $c): ?>
	<tr>
		<td><?php echo $c['customer_id']; ?></td>
		<td><?php echo htmlspecialchars($c['first_name'].' '.$c['last_name']); ?></td>
		<td><?php if (!empty($c['foto'])): ?><img src="imagen/<?php echo htmlspecialchars($c['foto']); ?>" style="max-width:48px;"><?php else: echo '—'; endif; ?></td>
		<td><?php echo htmlspecialchars($c['phone']); ?></td>
		<td><?php echo htmlspecialchars($c['email']); ?></td>
		<td><?php echo htmlspecialchars($c['street']); ?></td>
		<td><?php echo htmlspecialchars($c['city']); ?></td>
		<td><?php echo htmlspecialchars($c['state']); ?></td>
		<td>
			<a class="btn btn-sm btn-outline-primary" href="editar.php?id=<?php echo $c['customer_id']; ?>">Editar</a>
			<a class="btn btn-sm btn-outline-danger" href="eliminar.php?id=<?php echo $c['customer_id']; ?>" onclick="return confirm('Eliminar este cliente?');">Eliminar</a>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>
</div>

<?php include __DIR__ . '/../../templates/footer.php'; ?>

