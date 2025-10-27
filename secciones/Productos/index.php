<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../bd.php';
// listado de productos (adaptado) — traer también la categoría
$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT p.*, c.descripcion AS categoria FROM productos p LEFT JOIN categoria c ON p.category_id = c.category_id WHERE p.product_name LIKE :s ORDER BY p.product_id DESC");
    $stmt->execute(['s' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT p.*, c.descripcion AS categoria FROM productos p LEFT JOIN categoria c ON p.category_id = c.category_id ORDER BY p.product_id DESC");
}
$products = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Productos</h2>
    <a class="btn btn-primary" href="crear.php">Crear producto</a>
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
    <thead class="table-light"><tr><th>ID</th><th>Nombre</th><th>Foto</th><th>Año</th><th>Precio</th><th>Descuento</th><th>Destacado</th><th>Categoría</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach($products as $p): ?>
    <tr>
        <td><?php echo $p['product_id']; ?></td>
        <td><?php echo htmlspecialchars($p['product_name']); ?></td>

        <td><?php if ($p['foto']): ?><img src="../../uploads/<?php echo htmlspecialchars($p['foto']); ?>" style="max-width:80px;" alt="foto"><?php else: echo '—'; endif; ?></td>
        <td><?php echo $p['model_year']; ?></td>
    <td><?php echo number_format($p['price'],2); ?></td>
    <td><?php echo number_format($p['descuento'] ?? 0,2); ?>%</td>
    <td><?php echo (!empty($p['destacado']) && $p['destacado'] == 1) ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>'; ?></td>

     <td><?php echo $p['categoria'] ? htmlspecialchars($p['categoria']) : '—'; ?></td>
        <td>
            <a class="btn btn-sm btn-outline-primary" href="editar.php?id=<?php echo $p['product_id']; ?>">Editar</a>
            <a class="btn btn-sm btn-outline-danger" href="eliminar.php?id=<?php echo $p['product_id']; ?>" onclick="return confirm('Eliminar este producto?');">Borrar</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
