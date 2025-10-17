<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_id = :id');
$stmt->execute(['id'=>$id]);
$order = $stmt->fetch();
if (!$order) { header('Location: index.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$estado = $_POST['estado'] ?? $order['estado'];
		$up = $pdo->prepare('UPDATE orders SET estado = :e WHERE order_id = :id');
		$up->execute(['e'=>$estado,'id'=>$id]);
		header('Location: index.php'); exit;
}

?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Editar Pedido #<?php echo $order['order_id']; ?></h2>
<form method="post">
	<div class="mb-3">
		<label class="form-label">Estado</label>
		<input class="form-control" name="estado" value="<?php echo htmlspecialchars($order['estado']); ?>">
	</div>
	<div class="d-grid"><button class="btn btn-primary">Guardar</button></div>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>

