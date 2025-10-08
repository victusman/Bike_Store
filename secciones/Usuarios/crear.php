<?php
require_once __DIR__ . '/../../bd.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '') $errors[] = 'El usuario es requerido.';
    if ($password === '') $errors[] = 'La contraseña es requerida.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE usuario = :u');
    $stmt->execute(['u'=>$usuario]);
    if ($stmt->fetchColumn() > 0) $errors[] = 'El nombre de usuario ya existe.';

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO usuarios (usuario, password, email) VALUES (:u, :p, :e)');
        $stmt->execute(['u'=>$usuario,'p'=>$hash,'e'=>$email]);
        header('Location: index.php');
        exit;
    }
}
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Crear Usuario</h2>
<p><a class="btn btn-link" href="index.php">&laquo; Volver a lista</a></p>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?></div><?php endif; ?>
<form method="post" class="row g-3">
    <div class="col-12">
        <label class="form-label">Usuario</label>
        <input class="form-control" type="text" name="usuario" required>
    </div>
    <div class="col-12">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email">
    </div>
    <div class="col-12">
        <label class="form-label">Contraseña</label>
        <input class="form-control" type="password" name="password" required>
    </div>
    <div class="col-12">
        <button class="btn btn-primary" type="submit">Crear</button>
    </div>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
