<?php
require_once __DIR__ . '/../../bd.php';

// cargar categorías para el select
$catStmt = $pdo->query('SELECT * FROM categoria ORDER BY descripcion');
$categorias = $catStmt->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name'] ?? '');
    $year = $_POST['model_year'] ?? null;
    $price = $_POST['price'] ?? 0;
    $category_id = $_POST['category_id'] ?? null;

    if ($name === '') $errors[] = 'El nombre es requerido.';

    $fotoName = null;
    if (!empty($_FILES['foto']['name'])) {
        $uploaddir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fotoName = uniqid('p_') . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $uploaddir . $fotoName);
    }

    if (empty($errors)) {
    $stmt = $pdo->prepare("INSERT INTO productos (product_name, foto, model_year, price, category_id) VALUES (:n, :f, :y, :p, :c)");
        $stmt->execute(['n'=>$name, 'f'=>$fotoName, 'y'=>$year,'p'=>$price,'c'=>$category_id ?: null]);
        header('Location: index.php');
        exit;
    }
}
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Crear Producto</h2>
<p><a class="btn btn-link" href="index.php">&laquo; Volver a lista</a></p>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="row g-3">
    <div class="col-12">
        <label class="form-label">Nombre</label>
        <input class="form-control" type="text" name="product_name" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Año</label>
        <input class="form-control" type="number" name="model_year" min="1900" max="2100">
    </div>
    <div class="col-md-4">
        <label class="form-label">Precio</label>
        <input class="form-control" type="number" step="0.01" name="price" value="0.00">
    </div>
    <div class="col-md-4">
        <label class="form-label">Categoría</label>
        <select class="form-select" name="category_id">
            <option value="">— Sin categoría —</option>
            <?php foreach($categorias as $c): ?>
                <option value="<?php echo $c['category_id']; ?>"><?php echo htmlspecialchars($c['descripcion']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Foto</label>
        <input class="form-control" type="file" name="foto" accept="image/*">
    </div>
    <div class="col-12">
        <button class="btn btn-primary" type="submit">Crear</button>
    </div>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
