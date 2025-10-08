<?php
require_once __DIR__ . '/../db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: list.php'); exit; }

$stmt = $pdo->prepare('SELECT user_id, usuario, email FROM usuarios WHERE user_id = :id');
$stmt->execute(['id'=>$id]);
$user = $stmt->fetch();
if (!$user) { header('Location: list.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '') $errors[] = 'El usuario es requerido.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';

    // verificar usuario único (excepto el actual)
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
        header('Location: list.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Editar Usuario</title>
</head>
<body>
    <h1>Editar Usuario</h1>
    <p><a href="list.php">Volver a lista</a></p>
    <?php if ($errors): ?><ul style="color:red;"> <?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?> </ul><?php endif; ?>
    <form method="post">
        <label>Usuario: <input type="text" name="usuario" value="<?php echo htmlspecialchars($user['usuario']); ?>" required></label><br>
        <label>Email: <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"></label><br>
        <label>Contraseña (dejar en blanco para no cambiar): <input type="password" name="password"></label><br>
        <button type="submit">Guardar</button>
    </form>
</body>
</html>