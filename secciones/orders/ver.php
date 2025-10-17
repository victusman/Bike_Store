<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT o.*, c.first_name, c.last_name FROM orders o LEFT JOIN customer c ON o.customer_id = c.customer_id WHERE o.order_id = :id');
$stmt->execute(['id'=>$id]);
$order = $stmt->fetch();
if (!$order) { header('Location: index.php'); exit; }

$items = $pdo->prepare('SELECT oi.*, p.product_name FROM order_items oi JOIN productos p ON oi.product_id = p.product_id WHERE oi.order_id = :id');
$items->execute(['id'=>$id]);
$items = $items->fetchAll();
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Detalle pedido #<?php echo $order['order_id']; ?></h2>
<p>Cliente: <?php echo $order['first_name'] ? htmlspecialchars($order['first_name'].' '.$order['last_name']) : $order['customer_id']; ?></p>
<p>Fecha: <?php echo htmlspecialchars($order['order_date']); ?></p>
<p>Estado: <?php echo htmlspecialchars($order['estado']); ?></p>
<h4>Items</h4>
<table class="table table-sm">
<thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Descuento</th></tr></thead>
<tbody>
<?php foreach($items as $it): ?>
<tr>
  <td><?php echo htmlspecialchars($it['product_name']); ?></td>
  <td><?php echo (int)$it['quantity']; ?></td>
  <td><?php echo number_format($it['price'],2); ?></td>
  <td><?php echo number_format($it['discount'],2); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<p><a class="btn btn-secondary" href="index.php">Volver</a></p>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
