<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../bd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['store_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $street = $_POST['street'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';

    $ins = $pdo->prepare("INSERT INTO stores (store_name, phone, email, street, city, state) VALUES (?, ?, ?, ?, ?, ?)");
    $ins->execute([$name, $phone, $email, $street, $city, $state]);
    header('Location: index.php'); exit;
}
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Crear tienda</h2>
<form method="post">
    <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input name="store_name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Teléfono</label>
        <input name="phone" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" class="form-control" type="email">
    </div>
    <div class="mb-3">
        <label class="form-label">Calle</label>
        <input name="street" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Ciudad</label>
        <input name="city" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Estado</label>
        <input name="state" class="form-control">
    </div>
    <button class="btn btn-primary">Guardar</button>
    <a class="btn btn-secondary" href="index.php">Cancelar</a>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
