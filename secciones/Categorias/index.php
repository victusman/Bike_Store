<?php
require_once __DIR__ . '/../../bd.php';

$stmt = $pdo->query('SELECT * FROM categoria ORDER BY descripcion');
$cats = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Categorías</h2>
    <a class="btn btn-primary" href="crear.php">Crear categoría</a>
</div>
<div class="table-responsive">
<table class="table table-striped align-middle">
    <thead class="table-light"><tr><th>ID</th><th>Descripción</th><th>Acciones</th></tr></thead>
    <tbody>
    <?php foreach($cats as $c): ?>
    <tr>
        <td><?php echo $c['category_id']; ?></td>
        <td><?php echo htmlspecialchars($c['descripcion']); ?></td>
        <td>
            <a class="btn btn-sm btn-outline-primary" href="editar.php?id=<?php echo $c['category_id']; ?>">Editar</a>
            <a class="btn btn-sm btn-outline-danger" href="eliminar.php?id=<?php echo $c['category_id']; ?>" onclick="return confirm('Eliminar esta categoría?');">Borrar</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
