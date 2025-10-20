<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }

// Make the code resilient to different table names
// We'll try several possible customer table names below and use the first that works.
$order = false;
// Try possible customer table names in order of likelihood
$customerTables = ['customers', 'customer'];
foreach ($customerTables as $ct) {
  $sql = "SELECT o.*, c.first_name, c.last_name FROM orders o LEFT JOIN {$ct} c ON o.customer_id = c.customer_id WHERE o.order_id = :id";
  try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $order = $stmt->fetch();
    // if fetch succeeded (even null name) break and use this result
    break;
  } catch (PDOException $e) {
    // table may not exist, try next
    continue;
  }
}
if (!$order) { header('Location: index.php'); exit; }

$items = [];
// Items: try possible products table names, fallback to selecting items only
$productTables = ['products', 'productos', 'producto', 'product'];
$itemsFound = false;
foreach ($productTables as $pt) {
  $sql = "SELECT oi.*, p.product_name FROM order_items oi JOIN {$pt} p ON oi.product_id = p.product_id WHERE oi.order_id = :id";
  try {
    $itStmt = $pdo->prepare($sql);
    $itStmt->execute(['id'=>$id]);
    $items = $itStmt->fetchAll();
    $itemsFound = true;
    break;
  } catch (PDOException $e) {
    // try next product table name
    continue;
  }
}
if (!$itemsFound) {
  // fallback: select items without product_name
  $itStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = :id');
  $itStmt->execute(['id'=>$id]);
  $items = $itStmt->fetchAll();
}
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Detalle pedido #<?php echo $order['order_id']; ?></h2>
<p>Cliente: <?php echo !empty($order['first_name']) ? htmlspecialchars($order['first_name'].' '.($order['last_name'] ?? '')) : htmlspecialchars($order['customer_id']); ?></p>
<p>Fecha: <?php echo htmlspecialchars($order['order_date']); ?></p>
<p>Estado: <?php echo htmlspecialchars($order['estado']); ?></p>
<h4>Items</h4>
<table class="table table-sm">
<thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Descuento</th></tr></thead>
<tbody>
<?php if (empty($items)): ?>
  <tr><td colspan="4">Sin items</td></tr>
<?php else: ?>
  <?php foreach($items as $it): ?>
  <tr>
    <td><?php echo htmlspecialchars($it['product_name'] ?? ('Producto ID '.($it['product_id'] ?? 'N/A'))); ?></td>
    <td><?php echo (int)$it['quantity']; ?></td>
    <td><?php echo number_format($it['price'],2); ?></td>
    <td><?php echo number_format($it['discount'],2); ?></td>
  </tr>
  <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
<p><a class="btn btn-secondary" href="index.php">Volver</a></p>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
