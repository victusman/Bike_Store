<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

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

		$fotoName = null;
		if (!empty($_FILES['foto']['name'])) {
				$uploaddir = __DIR__ . '/imagen/';
				if (!is_dir($uploaddir)) mkdir($uploaddir,0755,true);
				$ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
				$fotoName = uniqid('c_') . '.' . $ext;
				move_uploaded_file($_FILES['foto']['tmp_name'], $uploaddir . $fotoName);
		}

		if (empty($errors)) {
				$ins = $pdo->prepare('INSERT INTO customer (first_name,last_name,phone,email,street,city,state,zip_code) VALUES (:f,:l,:ph,:em,:st,:ci,:stt,:zip)');
				$ins->execute(['f'=>$first,'l'=>$last,'ph'=>$phone,'em'=>$email,'st'=>$street,'ci'=>$city,'stt'=>$state,'zip'=>$zip]);
				$id = $pdo->lastInsertId();
				if ($fotoName) {
						$pdo->prepare('UPDATE customer SET foto = :foto WHERE customer_id = :id')->execute(['foto'=>$fotoName,'id'=>$id]);
				}
				header('Location: index.php'); exit;
		}
}

?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Crear Cliente</h2>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
	<div class="row g-3">
		<div class="col-md-6">
			<label class="form-label">Nombres</label>
			<input class="form-control" name="first_name" required>
		</div>
		<div class="col-md-6">
			<label class="form-label">Apellidos</label>
			<input class="form-control" name="last_name" required>
		</div>
		<div class="col-md-4"><label class="form-label">Teléfono</label><input class="form-control" name="phone"></div>
		<div class="col-md-8"><label class="form-label">Correo</label><input class="form-control" name="email" type="email"></div>
		<div class="col-12"><label class="form-label">Calle</label><input class="form-control" name="street"></div>
		<div class="col-md-4"><label class="form-label">Ciudad</label><input class="form-control" name="city"></div>
		<div class="col-md-4"><label class="form-label">Departamento</label><input class="form-control" name="state"></div>
		<div class="col-md-4"><label class="form-label">ZIP</label><input class="form-control" name="zip_code"></div>
		<div class="col-12"><label class="form-label">Foto (opcional)</label><input class="form-control" type="file" name="foto" accept="image/*"></div>
		<div class="col-12"><div class="d-grid"><button class="btn btn-primary">Crear</button></div></div>
	</div>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>

