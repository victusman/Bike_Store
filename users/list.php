<?php
require_once __DIR__ . '/../db.php';
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT user_id, usuario, email FROM users WHERE usuario LIKE :s OR email LIKE :s ORDER BY user_id DESC");
    $stmt->execute(['s' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT user_id, usuario, email FROM users ORDER BY user_id DESC");
}
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Usuarios - Bike Store</title>
    <style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;} td,th{border:1px solid #ccc;padding:8px;}</style>
</head>
<body>
    <h1>Usuarios</h1>
    <p><a href="../index.php">Volver</a> | <a href="create.php">Crear usuario</a></p>
    <form method="get" style="margin-bottom:10px;">
        <input type="text" name="search" placeholder="Buscar..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Buscar</button>
    </form>
    <table>
        <thead>
            <tr><th>ID</th><th>Usuario</th><th>Email</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php foreach($users as $u): ?>
            <tr>
                <td><?php echo $u['user_id']; ?></td>
                <td><?php echo htmlspecialchars($u['usuario']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $u['user_id']; ?>">Editar</a> |
                    <a href="delete.php?id=<?php echo $u['user_id']; ?>" onclick="return confirm('Eliminar este usuario?');">Borrar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>