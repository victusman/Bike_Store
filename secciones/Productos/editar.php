<?php
require_once __DIR__ . '/../../bd.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM productos WHERE product_id = :id');
$stmt->execute(['id'=>$id]);
$product = $stmt->fetch();
if (!$product) { header('Location: index.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name'] ?? '');
    $year = $_POST['model_year'] ?? null;
    $price = $_POST['price'] ?? 0;

    if ($name === '') $errors[] = 'El nombre es requerido.';

    $fotoName = $product['foto'];
    if (!empty($_FILES['foto']['name'])) {
        $uploaddir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fotoName = uniqid('p_') . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $uploaddir . $fotoName);
        if ($product['foto'] && file_exists($uploaddir . $product['foto'])) {
            @unlink($uploaddir . $product['foto']);
        }
    }

    if (empty($errors)) {
    $stmt = $pdo->prepare('UPDATE productos SET product_name=:n, foto=:f, model_year=:y, price=:p WHERE product_id=:id');
        $stmt->execute(['n'=>$name,'f'=>$fotoName,'y'=>$year,'p'=>$price,'id'=>$id]);
        header('Location: index.php');
        exit;
    }
}
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Editar Producto</h2>
<p><a class="btn btn-link" href="index.php">&laquo; Volver a lista</a></p>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="row g-3">
    <div class="col-12">
        <label class="form-label">Nombre</label>
        <input class="form-control" type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Año</label>
        <input class="form-control" type="number" name="model_year" value="<?php echo htmlspecialchars($product['model_year']); ?>" min="1900" max="2100">
    </div>
    <div class="col-md-4">
        <label class="form-label">Precio</label>
        <input class="form-control" type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>">
    </div>
    <div class="col-12">
        <p>Foto actual: <?php if ($product['foto']): ?><img src="../../uploads/<?php echo htmlspecialchars($product['foto']); ?>" style="max-width:120px;" alt="foto"><?php else: echo '—'; endif; ?></p>
    </div>
    <div class="col-12">
        <label class="form-label">Nueva foto (opcional)</label>
        <input class="form-control" type="file" name="foto" accept="image/*">
    </div>
    <div class="col-12">
        <button class="btn btn-success" type="submit">Guardar</button>
    </div>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
