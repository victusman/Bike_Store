<?php
require_once __DIR__ . '/../../bd.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT user_id, usuario, email FROM usuarios WHERE user_id = :id');
$stmt->execute(['id'=>$id]);
$user = $stmt->fetch();
if (!$user) { header('Location: index.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '') $errors[] = 'El usuario es requerido.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE usuario = :u AND user_id != :id');
    $stmt->execute(['u'=>$usuario,'id'=>$id]);
    if ($stmt->fetchColumn() > 0) $errors[] = 'El nombre de usuario ya existe.';

    if (empty($errors)) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE usuarios SET usuario=:u, password=:p, email=:e WHERE user_id=:id');
            $stmt->execute(['u'=>$usuario,'p'=>$hash,'e'=>$email,'id'=>$id]);
        } else {
            $stmt = $pdo->prepare('UPDATE usuarios SET usuario=:u, email=:e WHERE user_id=:id');
            $stmt->execute(['u'=>$usuario,'e'=>$email,'id'=>$id]);
        }
        header('Location: index.php');
        exit;
    }
}
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Editar Usuario</h2>
<p><a class="btn btn-link" href="index.php">&laquo; Volver a lista</a></p>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?></div><?php endif; ?>
<form method="post" class="row g-3">
    <div class="col-12">
        <label class="form-label">Usuario</label>
        <input class="form-control" type="text" name="usuario" value="<?php echo htmlspecialchars($user['usuario']); ?>" required>
    </div>
    <div class="col-12">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
    </div>
    <div class="col-12">
        <label class="form-label">Contraseña (dejar en blanco para no cambiar)</label>
        <input class="form-control" type="password" name="password">
    </div>
    <div class="col-12">
        <button class="btn btn-success" type="submit">Guardar</button>
    </div>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
