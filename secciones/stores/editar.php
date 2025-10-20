<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../bd.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM stores WHERE store_id = ?");
$stmt->execute([$id]);
$store = $stmt->fetch();
if (!$store) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['store_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $street = $_POST['street'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $upd = $pdo->prepare("UPDATE stores SET store_name=?, phone=?, email=?, street=?, city=?, state=? WHERE store_id = ?");
    $upd->execute([$name, $phone, $email, $street, $city, $state, $id]);
    header('Location: index.php'); exit;
}
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Editar tienda</h2>
<form method="post">
    <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input name="store_name" class="form-control" required value="<?php echo htmlspecialchars($store['store_name']); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Teléfono</label>
        <input name="phone" class="form-control" value="<?php echo htmlspecialchars($store['phone']); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" class="form-control" type="email" value="<?php echo htmlspecialchars($store['email']); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Calle</label>
        <input name="street" class="form-control" value="<?php echo htmlspecialchars($store['street']); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Ciudad</label>
        <input name="city" class="form-control" value="<?php echo htmlspecialchars($store['city']); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Estado</label>
        <input name="state" class="form-control" value="<?php echo htmlspecialchars($store['state']); ?>">
    </div>
    <button class="btn btn-primary">Guardar</button>
    <a class="btn btn-secondary" href="index.php">Cancelar</a>
</form>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
