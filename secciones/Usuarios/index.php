<?php
require_once __DIR__ . '/../../bd.php';
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT user_id, usuario, email FROM usuarios WHERE usuario LIKE :s OR email LIKE :s ORDER BY user_id DESC");
    $stmt->execute(['s' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT user_id, usuario, email FROM usuarios ORDER BY user_id DESC");
}
$users = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Usuarios</h2>
<p><a href="crear.php">Crear usuario</a></p>
<form method="get" style="margin-bottom:10px;">
    <input type="text" name="search" placeholder="Buscar..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Buscar</button>
</form>
<table>
    <thead><tr><th>ID</th><th>Usuario</th><th>Email</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach($users as $u): ?>
    <tr>
        <td><?php echo $u['user_id']; ?></td>
        <td><?php echo htmlspecialchars($u['usuario']); ?></td>
        <td><?php echo htmlspecialchars($u['email']); ?></td>
        <td>
            <a href="editar.php?id=<?php echo $u['user_id']; ?>">Editar</a> |
            <a href="../../users/delete.php?id=<?php echo $u['user_id']; ?>" onclick="return confirm('Eliminar este usuario?');">Borrar</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
