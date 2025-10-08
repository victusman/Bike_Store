<?php
require_once __DIR__ . '/../db.php';

$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE product_name LIKE :s ORDER BY product_id DESC");
    $stmt->execute(['s' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM productos ORDER BY product_id DESC");
}
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Productos - Bike Store</title>
    <style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;} td,th{border:1px solid #ccc;padding:8px;} img{max-width:100px;}</style>
</head>
<body>
    <h1>Productos</h1>
    <p><a href="../index.php">Volver</a> | <a href="create.php">Crear producto</a></p>
    <form method="get" style="margin-bottom:10px;">
        <input type="text" name="search" placeholder="Buscar..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Buscar</button>
    </form>
    <table>
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Foto</th><th>Año</th><th>Precio</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php foreach($products as $p): ?>
            <tr>
                <td><?php echo $p['product_id']; ?></td>
                <td><?php echo htmlspecialchars($p['product_name']); ?></td>
                <td><?php if ($p['foto']): ?><img src="../uploads/<?php echo htmlspecialchars($p['foto']); ?>" alt="foto"><?php else: echo '—'; endif; ?></td>
                <td><?php echo $p['model_year']; ?></td>
                <td><?php echo number_format($p['price'],2); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $p['product_id']; ?>">Editar</a> |
                    <a href="delete.php?id=<?php echo $p['product_id']; ?>" onclick="return confirm('Eliminar este producto?');">Borrar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>