<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../bd.php';

$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM vw_stocks WHERE product_name LIKE :s OR store_name LIKE :s ORDER BY id_stock DESC");
    $stmt->execute(['s' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM vw_stocks ORDER BY id_stock DESC");
}
$stocks = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Stocks</h2>
    <a class="btn btn-primary" href="crear.php">Crear stock</a>
</div>
<form method="get" class="mb-3 row g-2">
    <div class="col-auto">
        <input class="form-control" type="text" name="search" placeholder="Buscar producto o tienda..." value="<?php echo htmlspecialchars($search); ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary" type="submit">Buscar</button>
    </div>
</form>
<div class="table-responsive">
<table class="table table-striped table-hover align-middle">
    <thead class="table-light"><tr><th>ID</th><th>Tienda</th><th>Producto</th><th>Cantidad</th><th>Actualizado</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach($stocks as $s): ?>
    <tr>
        <td><?php echo $s['id_stock']; ?></td>
        <td><?php echo htmlspecialchars($s['store_name'] ?: '—'); ?></td>
        <td><?php echo htmlspecialchars($s['product_name'] ?: '—'); ?></td>
        <td><?php echo (int)$s['quantity']; ?></td>
        <td><?php echo $s['updated_at']; ?></td>
        <td>
            <a class="btn btn-sm btn-outline-primary" href="editar.php?id=<?php echo $s['id_stock']; ?>">Editar</a>
            <a class="btn btn-sm btn-outline-secondary ms-2" href="actualizar.php?id=<?php echo $s['id_stock']; ?>">Actualizar</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
