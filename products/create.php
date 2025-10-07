<?php
require_once __DIR__ . '/../db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name'] ?? '');
    $year = $_POST['model_year'] ?? null;
    $price = $_POST['price'] ?? 0;

    if ($name === '') $errors[] = 'El nombre es requerido.';

    $fotoName = null;
    if (!empty($_FILES['foto']['name'])) {
        $uploaddir = __DIR__ . '/../uploads/';
        if (!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fotoName = uniqid('p_') . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $uploaddir . $fotoName);
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO products (product_name, foto, model_year, price) VALUES (:n, :f, :y, :p)");
        $stmt->execute(['n'=>$name, 'f'=>$fotoName, 'y'=>$year, 'p'=>$price]);
        header('Location: list.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Crear Producto</title>
</head>
<body>
    <h1>Crear Producto</h1>
    <p><a href="list.php">Volver a lista</a></p>
    <?php if ($errors): ?><ul style="color:red;"><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Nombre: <input type="text" name="product_name" required></label><br>
        <label>Año: <input type="number" name="model_year" min="1900" max="2100"></label><br>
        <label>Precio: <input type="number" step="0.01" name="price" value="0.00"></label><br>
        <label>Foto: <input type="file" name="foto" accept="image/*"></label><br>
        <button type="submit">Crear</button>
    </form>
</body>
</html>