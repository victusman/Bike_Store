<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

$errors = [];
// cargar clientes
$cstmt = $pdo->query('SELECT customer_id, CONCAT(first_name, " ", last_name) AS name FROM customer ORDER BY first_name');
$customers = $cstmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$customer_id = $_POST['customer_id'] ?: null;
		$order_date = $_POST['order_date'] ?: date('Y-m-d');
		$estado = $_POST['estado'] ?: 'Pendiente';

		if (!$customer_id) $errors[] = 'Seleccione un cliente.';

		if (empty($errors)) {
				$ins = $pdo->prepare('INSERT INTO orders (customer_id, order_date, estado, user_id) VALUES (:c,:d,:e,:u)');
				$ins->execute(['c'=>$customer_id,'d'=>$order_date,'e'=>$estado,'u'=>$_SESSION['user_id']]);
				header('Location: index.php'); exit;
		}
}

?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Crear Pedido</h2>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?></div><?php endif; ?>
<form method="post">
	<div class="mb-3">
		<label class="form-label">Cliente</label>
		<select name="customer_id" class="form-select">
			<option value="">-- selecciona --</option>
			<?php foreach($customers as $c): ?>
				<option value="<?php echo $c['customer_id'] ?>"><?php echo htmlspecialchars($c['name']) ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="mb-3">
		<label class="form-label">Fecha</label>
		<input type="date" name="order_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
	</div>
	<div class="mb-3">
		<label class="form-label">Estado</label>
		<input type="text" name="estado" class="form-control" value="Pendiente">
	</div>
	<div class="d-flex gap-2">
		<button class="btn btn-primary">Crear</button>
		<a href="index.php" class="btn btn-secondary">Volver</a>
	</div>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>

