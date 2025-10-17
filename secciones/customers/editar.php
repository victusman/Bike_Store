<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM customer WHERE customer_id = :id');
$stmt->execute(['id'=>$id]);
$c = $stmt->fetch();
if (!$c) { header('Location: index.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$first = trim($_POST['first_name'] ?? '');
		$last = trim($_POST['last_name'] ?? '');
		$phone = trim($_POST['phone'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$street = trim($_POST['street'] ?? '');
		$city = trim($_POST['city'] ?? '');
		$state = trim($_POST['state'] ?? '');
		$zip = trim($_POST['zip_code'] ?? '');

		if ($first === '' || $last === '') $errors[] = 'Nombres y apellidos son requeridos.';

		if (empty($errors)) {
				$up = $pdo->prepare('UPDATE customer SET first_name=:f,last_name=:l,phone=:ph,email=:em,street=:st,city=:ci,state=:stt,zip_code=:zip WHERE customer_id=:id');
				$up->execute(['f'=>$first,'l'=>$last,'ph'=>$phone,'em'=>$email,'st'=>$street,'ci'=>$city,'stt'=>$state,'zip'=>$zip,'id'=>$id]);
				header('Location: index.php'); exit;
		}
}

?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Editar Cliente</h2>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
	<div class="row g-3">
		<div class="col-md-6"><label class="form-label">Nombres</label><input class="form-control" name="first_name" value="<?php echo htmlspecialchars($c['first_name']); ?>" required></div>
		<div class="col-md-6"><label class="form-label">Apellidos</label><input class="form-control" name="last_name" value="<?php echo htmlspecialchars($c['last_name']); ?>" required></div>
		<div class="col-md-4"><label class="form-label">Teléfono</label><input class="form-control" name="phone" value="<?php echo htmlspecialchars($c['phone']); ?>"></div>
		<div class="col-md-8"><label class="form-label">Correo</label><input class="form-control" name="email" type="email" value="<?php echo htmlspecialchars($c['email']); ?>"></div>
		<div class="col-12"><label class="form-label">Calle</label><input class="form-control" name="street" value="<?php echo htmlspecialchars($c['street']); ?>"></div>
		<div class="col-md-4"><label class="form-label">Ciudad</label><input class="form-control" name="city" value="<?php echo htmlspecialchars($c['city']); ?>"></div>
		<div class="col-md-4"><label class="form-label">Departamento</label><input class="form-control" name="state" value="<?php echo htmlspecialchars($c['state']); ?>"></div>
		<div class="col-md-4"><label class="form-label">ZIP</label><input class="form-control" name="zip_code" value="<?php echo htmlspecialchars($c['zip_code']); ?>"></div>
		<div class="col-12"><div class="d-grid"><button class="btn btn-primary">Guardar</button></div></div>
	</div>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>

