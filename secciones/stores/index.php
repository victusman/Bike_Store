<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../bd.php';

$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM stores WHERE store_name LIKE :s ORDER BY store_id DESC");
    $stmt->execute(['s' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM stores ORDER BY store_id DESC");
}
$stores = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Tiendas</h2>
    <a class="btn btn-primary" href="crear.php">Crear tienda</a>
</div>
<form method="get" class="mb-3 row g-2">
    <div class="col-auto">
        <input class="form-control" type="text" name="search" placeholder="Buscar tienda..." value="<?php echo htmlspecialchars($search); ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary" type="submit">Buscar</button>
    </div>
</form>
<div class="table-responsive">
<table class="table table-striped table-hover align-middle">
    <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Ciudad</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach($stores as $s): ?>
    <tr>
        <td><?php echo $s['store_id']; ?></td>
        <td><?php echo htmlspecialchars($s['store_name']); ?></td>
        <td><?php echo htmlspecialchars($s['phone']); ?></td>
        <td><?php echo htmlspecialchars($s['email']); ?></td>
        <td><?php echo htmlspecialchars($s['city']); ?></td>
        <td>
            <a class="btn btn-sm btn-outline-primary" href="editar.php?id=<?php echo $s['store_id']; ?>">Editar</a>
            <a class="btn btn-sm btn-outline-danger" href="eliminar.php?id=<?php echo $s['store_id']; ?>" onclick="return confirm('Eliminar esta tienda?');">Borrar</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
