<?php
require_once __DIR__ . '/../db.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '') $errors[] = 'El usuario es requerido.';
    if ($password === '') $errors[] = 'La contraseña es requerida.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';

    // verificar usuario único
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE usuario = :u');
    $stmt->execute(['u'=>$usuario]);
    if ($stmt->fetchColumn() > 0) $errors[] = 'El nombre de usuario ya existe.';

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO usuarios (usuario, password, email) VALUES (:u, :p, :e)');
        $stmt->execute(['u'=>$usuario,'p'=>$hash,'e'=>$email]);
        header('Location: list.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Crear Usuario</title>
</head>
<body>
    <h1>Crear Usuario</h1>
    <p><a href="list.php">Volver a lista</a></p>
    <?php if ($errors): ?><ul style="color:red;"> <?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?> </ul><?php endif; ?>
    <form method="post">
        <label>Usuario: <input type="text" name="usuario" required></label><br>
        <label>Email: <input type="email" name="email"></label><br>
        <label>Contraseña: <input type="password" name="password" required></label><br>
        <button type="submit">Crear</button>
    </form>
</body>
</html>