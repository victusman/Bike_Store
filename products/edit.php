<?php
require_once __DIR__ . '/../db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: list.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = :id');
$stmt->execute(['id'=>$id]);
$product = $stmt->fetch();
if (!$product) { header('Location: list.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name'] ?? '');
    $year = $_POST['model_year'] ?? null;
    $price = $_POST['price'] ?? 0;

    if ($name === '') $errors[] = 'El nombre es requerido.';

    $fotoName = $product['foto'];
    if (!empty($_FILES['foto']['name'])) {
        $uploaddir = __DIR__ . '/../uploads/';
        if (!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fotoName = uniqid('p_') . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $uploaddir . $fotoName);
        // opcional: eliminar foto antigua
        if ($product['foto'] && file_exists($uploaddir . $product['foto'])) {
            @unlink($uploaddir . $product['foto']);
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE products SET product_name=:n, foto=:f, model_year=:y, price=:p WHERE product_id=:id');
        $stmt->execute(['n'=>$name,'f'=>$fotoName,'y'=>$year,'p'=>$price,'id'=>$id]);
        header('Location: list.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Editar Producto</title>
</head>
<body>
    <h1>Editar Producto</h1>
    <p><a href="list.php">Volver a lista</a></p>
    <?php if ($errors): ?><ul style="color:red;"> <?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?> </ul><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Nombre: <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required></label><br>
        <label>Año: <input type="number" name="model_year" value="<?php echo htmlspecialchars($product['model_year']); ?>" min="1900" max="2100"></label><br>
        <label>Precio: <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>"></label><br>
        <p>Foto actual: <?php if ($product['foto']): ?><img src="../uploads/<?php echo htmlspecialchars($product['foto']); ?>" style="max-width:120px;" alt="foto"><?php else: echo '—'; endif; ?></p>
        <label>Nueva foto (opcional): <input type="file" name="foto" accept="image/*"></label><br>
        <button type="submit">Guardar</button>
    </form>
</body>
</html>