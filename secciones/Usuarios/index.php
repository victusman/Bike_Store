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
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Usuarios</h2>
    <a class="btn btn-primary" href="crear.php">Crear usuario</a>
</div>
<form method="get" class="mb-3 row g-2">
    <div class="col-auto">
        <input class="form-control" type="text" name="search" placeholder="Buscar..." value="<?php echo htmlspecialchars($search); ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary" type="submit">Buscar</button>
    </div>
</form>
<div class="table-responsive">
<table class="table table-striped table-hover align-middle">
    <thead class="table-light"><tr><th>ID</th><th>Usuario</th><th>Email</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach($users as $u): ?>
    <tr>
        <td><?php echo $u['user_id']; ?></td>
        <td><?php echo htmlspecialchars($u['usuario']); ?></td>
        <td><?php echo htmlspecialchars($u['email']); ?></td>
        <td>
            <a class="btn btn-sm btn-outline-primary" href="editar.php?id=<?php echo $u['user_id']; ?>">Editar</a>
            <a class="btn btn-sm btn-outline-danger" href="eliminar.php?id=<?php echo $u['user_id']; ?>" onclick="return confirm('Eliminar este usuario?');">Borrar</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
