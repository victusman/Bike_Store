<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../bd.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM stocks WHERE id_stock = ?");
$stmt->execute([$id]);
$stock = $stmt->fetch();
if (!$stock) { header('Location: index.php'); exit; }

// Intentamos obtener el nombre de la tienda si existe (no dependemos de products)
$storeName = null;
try {
    $st = $pdo->prepare("SELECT store_name FROM stores WHERE store_id = ?");
    $st->execute([$stock['store_id']]);
    $row = $st->fetch();
    $storeName = $row ? $row['store_name'] : null;
} catch (Exception $e) {
    $storeName = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = (int)($_POST['quantity'] ?? 0);
    $upd = $pdo->prepare("UPDATE stocks SET quantity = ?, updated_at = NOW() WHERE id_stock = ?");
    $upd->execute([$quantity, $id]);
    header('Location: index.php'); exit;
}
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Actualizar stock #<?php echo $stock['id_stock']; ?></h2>
<div class="mb-3">
    <label class="form-label">Tienda</label>
    <div><?php echo htmlspecialchars($storeName ?? ('ID ' . $stock['store_id'])); ?></div>
</div>
<div class="mb-3">
    <label class="form-label">Producto (ID)</label>
    <div><?php echo htmlspecialchars($stock['product_id']); ?> <small class="text-muted">(nombre no disponible)</small></div>
</div>
<form method="post">
    <div class="mb-3">
        <label class="form-label">Cantidad</label>
        <input type="number" name="quantity" class="form-control" value="<?php echo (int)$stock['quantity']; ?>" required>
    </div>
    <button class="btn btn-primary" type="submit">Actualizar</button>
    <a class="btn btn-secondary" href="index.php">Cancelar</a>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
