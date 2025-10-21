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

// Resolve current user display name (prefer human-friendly name over id)
$userName = null;
$userRow = null;
$userTables = ['users', 'Usuarios', 'usuario', 'user', 'Users'];
// prefer the order's creator user_id; fallback to the current session user id
$lookupUserId = $order['user_id'] ?? ($_SESSION['user_id'] ?? null);

// Helper to extract display name from a DB row
function extractNameFromRow($r) {
  if (!$r) return null;
  if (!empty($r['first_name']) || !empty($r['last_name'])) return trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
  if (!empty($r['usuario'])) return $r['usuario'];
  if (!empty($r['username'])) return $r['username'];
  if (!empty($r['user_name'])) return $r['user_name'];
  if (!empty($r['nombre'])) return $r['nombre'];
  if (!empty($r['name'])) return $r['name'];
  if (!empty($r['email'])) return $r['email'];
  return $r['user_id'] ?? $r['id'] ?? null;
}

if ($lookupUserId !== null) {
  foreach ($userTables as $ut) {
    // try numeric id lookup first if lookup value is numeric
    if (is_numeric($lookupUserId)) {
      foreach (['user_id','id','uid'] as $col) {
        try {
          $q = "SELECT * FROM {$ut} WHERE {$col} = :v LIMIT 1";
          $s = $pdo->prepare($q);
          $s->execute(['v' => $lookupUserId]);
          $row = $s->fetch();
          if ($row) { $userRow = $row; break 2; }
        } catch (PDOException $e) { continue; }
      }
    }
    // if not numeric or not found, try matching common username-like columns
    foreach (['usuario','username','user_name','name','email'] as $col) {
      try {
        $q = "SELECT * FROM {$ut} WHERE {$col} = :v LIMIT 1";
        $s = $pdo->prepare($q);
        $s->execute(['v' => $lookupUserId]);
        $row = $s->fetch();
        if ($row) { $userRow = $row; break 2; }
      } catch (PDOException $e) { continue; }
    }
  }
}

// if not found in DB, look for other session keys that may contain a display name
if (!$userRow) {
  foreach (['usuario','username','user','name','user_name','display_name','email'] as $k) {
    if (!empty($_SESSION[$k])) { $userName = $_SESSION[$k]; break; }
  }
}

if ($userRow) $userName = extractNameFromRow($userRow);
?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Detalle pedido #<?php echo $order['order_id']; ?></h2>
<p>Cliente: <?php echo !empty($order['first_name']) ? htmlspecialchars($order['first_name'].' '.($order['last_name'] ?? '')) : htmlspecialchars($order['customer_id']); ?></p>
<p>Fecha: <?php echo htmlspecialchars($order['order_date']); ?></p>
<p>Estado: <?php echo htmlspecialchars($order['estado']); ?></p>
<p>Usuario: <?php echo htmlspecialchars($userName ?? ($order['user_id'] ?? $_SESSION['user_id'])); ?></p>
<h4>Items</h4>
<table class="table table-sm">
<thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Descuento</th><th>Subtotal</th></tr></thead>
<tbody>
<?php if (empty($items)): ?>
  <tr><td colspan="5">Sin items</td></tr>
<?php else: ?>
  <?php
    $total = 0.0;
    foreach($items as $it):
      $qty = (int)($it['quantity'] ?? 0);
      $price = (float)($it['price'] ?? 0);
      $discount = (isset($it['discount']) ? (float)$it['discount'] : 0.0);
      // interpret discount: if between 0 and 1 (inclusive) treat as fraction -> percent; otherwise monetary amount
      $isPercent = ($discount > 0 && $discount <= 1);
      if ($isPercent) {
        $subtotal = max(0, $qty * $price * (1.0 - $discount));
      } else {
        $subtotal = max(0, $qty * $price - $discount);
      }
      $total += $subtotal;
  ?>
  <tr>
    <td><?php echo htmlspecialchars($it['product_name'] ?? ('Producto ID '.($it['product_id'] ?? 'N/A'))); ?></td>
    <td><?php echo $qty; ?></td>
    <td><?php echo '$'.number_format($price,2); ?></td>
    <td><?php echo $isPercent ? (rtrim(rtrim(number_format($discount*100,2), '0'), '.') . '%') : ('$'.number_format($discount,2)); ?></td>
    <td><?php echo '$'.number_format($subtotal,2); ?></td>
  </tr>
  <?php endforeach; ?>
<?php endif; ?>
</tbody>
<tfoot>
  <tr>
    <td colspan="4" class="text-end"><strong>Total Final:</strong></td>
    <td><strong><?php echo '$'.number_format($total ?? 0,2); ?></strong></td>
  </tr>
</tfoot>
</table>
<p><a class="btn btn-secondary" href="index.php">Volver</a></p>
<?php include __DIR__ . '/../../templates/footer.php'; ?>
