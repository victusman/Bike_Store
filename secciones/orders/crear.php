<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

$errors = [];
// cargar clientes (soporta customer(s))
$customerQuery = null;
foreach (['customers','customer'] as $ct) {
	try {
		$customerQuery = $pdo->query("SELECT customer_id, CONCAT(first_name, ' ', last_name) AS name FROM {$ct} ORDER BY first_name");
		$customers = $customerQuery->fetchAll();
		break;
	} catch (Exception $e) {
		continue;
	}
}
if (!isset($customers)) $customers = [];

// cargar productos (soporta products/productos)
$products = [];
foreach (['products','productos','product'] as $pt) {
	try {
		$pstmt = $pdo->query("SELECT product_id, product_name, price FROM {$pt} ORDER BY product_name");
		$products = $pstmt->fetchAll();
		break;
	} catch (Exception $e) {
		continue;
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$customer_id = $_POST['customer_id'] ?: null;
	$order_date = $_POST['order_date'] ?: date('Y-m-d');
	$estado = $_POST['estado'] ?: 'Pendiente';
	$items = $_POST['items'] ?? []; // items expected as array of arrays with product_id, quantity, price, discount

	if (!$customer_id) $errors[] = 'Seleccione un cliente.';
	if (empty($items)) $errors[] = 'Agregue al menos un item al pedido.';

	// validate items
	foreach ($items as $i => $it) {
		if (empty($it['product_id'])) $errors[] = "Item #".($i+1).": seleccione producto.";
		if (empty($it['quantity']) || (int)$it['quantity'] <= 0) $errors[] = "Item #".($i+1).": cantidad inválida.";
	}

	if (empty($errors)) {
		try {
			$pdo->beginTransaction();
			$ins = $pdo->prepare('INSERT INTO orders (customer_id, order_date, estado, user_id) VALUES (:c,:d,:e,:u)');
			$ins->execute(['c'=>$customer_id,'d'=>$order_date,'e'=>$estado,'u'=>$_SESSION['user_id']]);
			$orderId = $pdo->lastInsertId();

			$oit = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price, discount) VALUES (:o,:p,:q,:pr,:ds)');
			foreach ($items as $it) {
				$price = $it['price'] ?: 0;
				$discount = $it['discount'] ?: 0;
				$oit->execute(['o'=>$orderId, 'p'=>$it['product_id'], 'q'=> (int)$it['quantity'], 'pr'=>$price, 'ds'=>$discount]);
			}

			$pdo->commit();
			header('Location: index.php'); exit;
		} catch (Exception $e) {
			$pdo->rollBack();
			$errors[] = 'Error guardando pedido: '.$e->getMessage();
		}
	}
}

?>
<?php include __DIR__ . '/../../templates/header.php'; ?>
<h2>Crear Pedido</h2>
<?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?></div><?php endif; ?>
<form method="post">
	<div class="mb-3">
		<label class="form-label">Cliente</label>
		<select name="customer_id" class="form-select">
			<option value="">-- selecciona --</option>
			<?php foreach($customers as $c): ?>
				<option value="<?php echo $c['customer_id'] ?>"><?php echo htmlspecialchars($c['name']) ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="mb-3">
		<label class="form-label">Fecha</label>
		<input type="date" name="order_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
	</div>
	<div class="mb-3">
		<label class="form-label">Estado</label>
		<input type="text" name="estado" class="form-control" value="Pendiente">
	</div>
	<h4>Items</h4>
	<div class="mb-3">
		<table class="table" id="items-table">
			<thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Descuento</th><th></th></tr></thead>
			<tbody></tbody>
		</table>
		<button type="button" id="add-item" class="btn btn-outline-secondary">Agregar Item</button>
	</div>

	<input type="hidden" name="items_json" id="items_json">

	<div class="d-flex gap-2">
		<button type="button" class="btn btn-primary" id="save-btn">Crear</button>
		<a href="index.php" class="btn btn-secondary">Volver</a>
		<button type="button" id="cancel-btn" class="btn btn-light">Cancelar</button>
	</div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function(){
	const products = <?php echo json_encode($products); ?> || [];
	const tbody = document.querySelector('#items-table tbody');
	const addBtn = document.getElementById('add-item');
	const saveBtn = document.getElementById('save-btn');
	const cancelBtn = document.getElementById('cancel-btn');

	function makeProductOptions(){
		return products.map(p=>`<option value="${p.product_id}" data-price="${p.price}">${p.product_name}</option>`).join('');
	}

	function addRow(item){
		const tr = document.createElement('tr');
		tr.innerHTML = `
			<td><select class="form-select product-select"> <option value="">-- seleccionar --</option> ${makeProductOptions()} </select></td>
			<td><input type="number" class="form-control qty" value="1" min="1"></td>
			<td><input type="text" class="form-control price"></td>
			<td><input type="text" class="form-control discount" value="0"></td>
			<td><button type="button" class="btn btn-sm btn-danger remove">X</button></td>
		`;
		tbody.appendChild(tr);
		if (item) {
			tr.querySelector('.product-select').value = item.product_id;
			tr.querySelector('.qty').value = item.quantity;
			tr.querySelector('.price').value = item.price;
			tr.querySelector('.discount').value = item.discount;
		}
	}

	addBtn.addEventListener('click', ()=> addRow());
	tbody.addEventListener('click', function(e){
		if (e.target.classList.contains('remove')) e.target.closest('tr').remove();
	});

	// autofill price when product selected
	tbody.addEventListener('change', function(e){
		if (e.target.classList.contains('product-select')){
			const opt = e.target.selectedOptions[0];
			const price = opt ? opt.dataset.price || '' : '';
			e.target.closest('tr').querySelector('.price').value = price;
		}
	});

	saveBtn.addEventListener('click', function(e){
		e.preventDefault();
		const rows = Array.from(tbody.querySelectorAll('tr'));
		const items = rows.map(r=>{
			return {
				product_id: r.querySelector('.product-select').value,
				quantity: r.querySelector('.qty').value,
				price: r.querySelector('.price').value,
				discount: r.querySelector('.discount').value
			};
		}).filter(it=>it.product_id);
		// remove existing dynamic inputs
		document.querySelectorAll('input[name^="items["]').forEach(n=>n.remove());
		items.forEach((it,idx)=>{
			const inp = document.createElement('input'); inp.type='hidden'; inp.name=`items[${idx}][product_id]`; inp.value=it.product_id; document.forms[0].appendChild(inp);
			const inp2 = document.createElement('input'); inp2.type='hidden'; inp2.name=`items[${idx}][quantity]`; inp2.value=it.quantity; document.forms[0].appendChild(inp2);
			const inp3 = document.createElement('input'); inp3.type='hidden'; inp3.name=`items[${idx}][price]`; inp3.value=it.price; document.forms[0].appendChild(inp3);
			const inp4 = document.createElement('input'); inp4.type='hidden'; inp4.name=`items[${idx}][discount]`; inp4.value=it.discount; document.forms[0].appendChild(inp4);
		});
		document.forms[0].submit();
	});

	cancelBtn.addEventListener('click', function(){ tbody.innerHTML=''; });
});
</script>

<?php include __DIR__ . '/../../templates/footer.php'; ?>

