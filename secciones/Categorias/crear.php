<?php
require_once __DIR__ . '/../../bd.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $desc = trim($_POST['descripcion'] ?? '');
    if ($desc === '') $errors[] = 'La descripción es requerida.';
    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO categoria (descripcion) VALUES (:d)');
        $stmt->execute(['d'=>$desc]);
        header('Location: index.php'); exit;
    }
}
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Crear Categoría</h2>
<p><a class="btn btn-link" href="index.php">&laquo; Volver a lista</a></p>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?></div><?php endif; ?>
<form method="post" class="row g-3">
    <div class="col-12">
        <label class="form-label">Descripción</label>
        <input class="form-control" type="text" name="descripcion" required>
    </div>
    <div class="col-12">
        <button class="btn btn-primary" type="submit">Crear</button>
        <a class="btn btn-secondary ms-2" href="index.php">Cancelar</a>
    </div>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
