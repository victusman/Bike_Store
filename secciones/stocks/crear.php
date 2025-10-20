<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../bd.php';

// obtener listas de productos y tiendas - manejar si las tablas no existen para evitar fatal error
try {
    $prods = $pdo->query("SELECT product_id, product_name FROM productos ORDER BY product_name")->fetchAll();
    $stores = $pdo->query("SELECT store_id, store_name FROM stores ORDER BY store_name")->fetchAll();
} catch (PDOException $e) {
    // Si faltan tablas, mostrar alerta con instrucciones; si la tabla products existe pero no stores
    include __DIR__ . '/../../templates/header.php';
    ?>
    <div class="alert alert-warning" role="alert">
        <h5 class="alert-heading">Atención</h5>
        <p>No se encuentran las tablas necesarias o hay un problema temporal de conexión. Si no ves tiendas en la lista a continuación, primero crea una tienda antes de crear stock.</p>
        <p>Si la base de datos no está importada, importa <code>mysql.sql</code> desde el proyecto usando phpMyAdmin o la línea de comandos.</p>
    </div>
    <?php
    // intentar conseguir productos y tiendas de forma segura: si no existen, inicializar arrays vacíos
    try { $prods = $prods ?? []; } catch (Exception $ex) { $prods = []; }
    try { $stores = $stores ?? []; } catch (Exception $ex) { $stores = []; }
    include __DIR__ . '/../../templates/footer.php';
    // No exit para que la UI muestre el formulario con opción de crear tienda
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_id = (int)($_POST['store_id'] ?? 0);
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);

    // si ya existe un registro para esa tienda/producto, actualizar cantidad
    $stmt = $pdo->prepare("SELECT id_stock FROM stocks WHERE store_id = ? AND product_id = ?");
    $stmt->execute([$store_id, $product_id]);
    $exists = $stmt->fetch();
    if ($exists) {
        $upd = $pdo->prepare("UPDATE stocks SET quantity = ?, updated_at = NOW() WHERE id_stock = ?");
        $upd->execute([$quantity, $exists['id_stock']]);
    } else {
        $ins = $pdo->prepare("INSERT INTO stocks (store_id, product_id, quantity) VALUES (?, ?, ?)");
        $ins->execute([$store_id, $product_id, $quantity]);
    }
    header('Location: index.php');
    exit;
}
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Crear / Actualizar stock</h2>
<form method="post">
    <div class="mb-3">
        <label class="form-label">Tienda</label>
        <select name="store_id" class="form-select" required>
            <option value="">-- Seleccione --</option>
            <?php foreach($stores as $st): ?>
            <option value="<?php echo $st['store_id']; ?>"><?php echo htmlspecialchars($st['store_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Producto</label>
        <select name="product_id" class="form-select" required>
            <option value="">-- Seleccione --</option>
            <?php foreach($prods as $p): ?>
            <option value="<?php echo $p['product_id']; ?>"><?php echo htmlspecialchars($p['product_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Cantidad</label>
        <input type="number" name="quantity" class="form-control" value="0" required>
    </div>
    <button class="btn btn-primary" type="submit">Guardar</button>
    <a class="btn btn-secondary" href="index.php">Cancelar</a>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
