<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../bd.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php'); exit;
}

$stmt = $pdo->prepare("SELECT * FROM stocks WHERE id_stock = ?");
$stmt->execute([$id]);
$stock = $stmt->fetch();
if (!$stock) {
    header('Location: index.php'); exit;
}

$prods = $pdo->query("SELECT product_id, product_name FROM productos ORDER BY product_name")->fetchAll();
$stores = $pdo->query("SELECT store_id, store_name FROM stores ORDER BY store_name")->fetchAll();

// Manejo seguro si faltan las tablas products o stores
$products_available = true;
$stores_available = true;
try {
    $prods = $pdo->query("SELECT product_id, product_name FROM productos ORDER BY product_name")->fetchAll();
} catch (PDOException $e) {
    $prods = [];
    $products_available = false;
}
try {
    $stores = $pdo->query("SELECT store_id, store_name FROM stores ORDER BY store_name")->fetchAll();
} catch (PDOException $e) {
    $stores = [];
    $stores_available = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_id = (int)($_POST['store_id'] ?? 0);
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);
    $upd = $pdo->prepare("UPDATE stocks SET store_id = ?, product_id = ?, quantity = ?, updated_at = NOW() WHERE id_stock = ?");
    $upd->execute([$store_id, $product_id, $quantity, $id]);
    header('Location: index.php'); exit;
}
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Editar stock #<?php echo $stock['id_stock']; ?></h2>
<form method="post">
    <div class="mb-3">
        <label class="form-label">Tienda</label>
        <?php if ($stores_available): ?>
        <select name="store_id" class="form-select" required>
            <?php foreach($stores as $st): ?>
            <option value="<?php echo $st['store_id']; ?>" <?php echo $st['store_id']==$stock['store_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($st['store_name']); ?></option>
            <?php endforeach; ?>
        </select>
        <?php else: ?>
        <div class="alert alert-warning">La tabla <code>stores</code> no está disponible. Introduce manualmente el ID de tienda.</div>
        <input type="number" name="store_id" class="form-control" value="<?php echo (int)$stock['store_id']; ?>" required>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label">Producto</label>
        <?php if ($products_available): ?>
        <select name="product_id" class="form-select" required>
            <?php foreach($prods as $p): ?>
            <option value="<?php echo $p['product_id']; ?>" <?php echo $p['product_id']==$stock['product_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['product_name']); ?></option>
            <?php endforeach; ?>
        </select>
        <?php else: ?>
        <div class="alert alert-warning">La tabla <code>products</code> no está disponible. Introduce manualmente el ID de producto.</div>
        <input type="number" name="product_id" class="form-control" value="<?php echo (int)$stock['product_id']; ?>" required>
        <div class="small text-muted mt-1">Si no tienes la tabla <code>products</code>, importa <code>mysql.sql</code> para restaurarla.</div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label">Cantidad</label>
        <input type="number" name="quantity" class="form-control" value="<?php echo (int)$stock['quantity']; ?>" required>
    </div>
    <button class="btn btn-primary" type="submit">Guardar</button>
    <a class="btn btn-secondary" href="index.php">Cancelar</a>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
