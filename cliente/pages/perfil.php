<?php
session_start();
require_once __DIR__ . '/../../db.php';

// Verificar autenticación
if (!isset($_SESSION['customer_id'])) {
    header('Location: login_cliente.php?redirect=perfil.php');
    exit;
}

$pageTitle = 'Mi Perfil - Bike Store';
$mensaje = '';
$error = '';

// Obtener datos del cliente
$stmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id = :id");
$stmt->execute(['id' => $_SESSION['customer_id']]);
$cliente = $stmt->fetch();

if (!$cliente) {
    session_destroy();
    header('Location: login_cliente.php');
    exit;
}

// Procesar actualización de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    
    // Validaciones
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = 'Por favor completa los campos obligatorios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor ingresa un correo válido';
    } else {
        // Verificar si el email ya está en uso por otro cliente
        $stmt = $pdo->prepare("SELECT customer_id FROM customer WHERE email = :email AND customer_id != :id");
        $stmt->execute(['email' => $email, 'id' => $_SESSION['customer_id']]);
        
        if ($stmt->fetch()) {
            $error = 'Este correo ya está en uso por otra cuenta';
        } else {
            // Actualizar datos
            try {
                $stmt = $pdo->prepare("UPDATE customer SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    street = :street,
                    city = :city,
                    state = :state,
                    zip_code = :zip_code
                    WHERE customer_id = :id");
                
                $stmt->execute([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'street' => $street,
                    'city' => $city,
                    'state' => $state,
                    'zip_code' => $zip_code,
                    'id' => $_SESSION['customer_id']
                ]);
                
                // Actualizar sesión
                $_SESSION['customer_name'] = $first_name . ' ' . $last_name;
                $_SESSION['customer_email'] = $email;
                
                $mensaje = 'Perfil actualizado exitosamente';
                
                // Recargar datos
                $stmt = $pdo->prepare("SELECT * FROM customer WHERE customer_id = :id");
                $stmt->execute(['id' => $_SESSION['customer_id']]);
                $cliente = $stmt->fetch();
                
            } catch (Exception $e) {
                $error = 'Error al actualizar el perfil. Por favor intenta nuevamente.';
            }
        }
    }
}

// Obtener estadísticas del cliente
$stmt = $pdo->prepare("SELECT COUNT(*) as total_pedidos, SUM(total_amount) as total_gastado 
                       FROM orders WHERE customer_id = :id");
$stmt->execute(['id' => $_SESSION['customer_id']]);
$stats = $stmt->fetch();

include __DIR__ . '/../components/header_publico.php';
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/Bike_Store/cliente/index.php">Inicio</a></li>
            <li class="breadcrumb-item active">Mi Perfil</li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="avatar-circle bg-primary text-white mb-3">
                        <i class="fas fa-user fa-3x"></i>
                    </div>
                    <h5 class="card-title"><?php echo htmlspecialchars($cliente['first_name'] . ' ' . $cliente['last_name']); ?></h5>
                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($cliente['email']); ?></p>
                    
                    <div class="d-grid gap-2">
                        <a href="mis_pedidos.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-shopping-bag"></i> Mis Pedidos
                        </a>
                        <a href="cerrar_sesion.php" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Mis Estadísticas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Total de Pedidos</small>
                        <h4 class="mb-0 text-primary"><?php echo $stats['total_pedidos'] ?? 0; ?></h4>
                    </div>
                    <div>
                        <small class="text-muted">Total Gastado</small>
                        <h4 class="mb-0 text-success">$<?php echo number_format($stats['total_gastado'] ?? 0, 2); ?></h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenido Principal -->
        <div class="col-md-9">
            <!-- Mensajes -->
            <?php if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <strong>¡Bienvenido!</strong> Tu cuenta ha sido creada exitosamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($mensaje); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Formulario de Perfil -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-edit"></i> Editar Mi Perfil</h4>
                </div>
                
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <!-- Información Personal -->
                        <h5 class="mb-3 border-bottom pb-2">
                            <i class="fas fa-user"></i> Información Personal
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label fw-bold">
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="first_name" 
                                       name="first_name"
                                       value="<?php echo htmlspecialchars($cliente['first_name']); ?>"
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label fw-bold">
                                    Apellido <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="last_name" 
                                       name="last_name"
                                       value="<?php echo htmlspecialchars($cliente['last_name']); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <!-- Información de Contacto -->
                        <h5 class="mb-3 mt-4 border-bottom pb-2">
                            <i class="fas fa-envelope"></i> Información de Contacto
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email"
                                       value="<?php echo htmlspecialchars($cliente['email']); ?>"
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-bold">
                                    Teléfono
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone"
                                       value="<?php echo htmlspecialchars($cliente['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <!-- Dirección de Envío -->
                        <h5 class="mb-3 mt-4 border-bottom pb-2">
                            <i class="fas fa-map-marker-alt"></i> Dirección de Envío
                        </h5>
                        
                        <div class="mb-3">
                            <label for="street" class="form-label fw-bold">Calle y Número</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="street" 
                                   name="street"
                                   placeholder="Ej: Av. Principal 123"
                                   value="<?php echo htmlspecialchars($cliente['street'] ?? ''); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label fw-bold">Ciudad</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="city" 
                                       name="city"
                                       value="<?php echo htmlspecialchars($cliente['city'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label fw-bold">Estado/Provincia</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="state" 
                                       name="state"
                                       value="<?php echo htmlspecialchars($cliente['state'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="zip_code" class="form-label fw-bold">Código Postal</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="zip_code" 
                                       name="zip_code"
                                       value="<?php echo htmlspecialchars($cliente['zip_code'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                            <a href="/Bike_Store/cliente/index.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="card-footer bg-light text-muted">
                    <small>
                        <i class="fas fa-info-circle"></i> 
                        Miembro desde: <?php echo date('d/m/Y', strtotime($cliente['created_at'])); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    
    .card {
        border: none;
        border-radius: 10px;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
</style>

<?php include __DIR__ . '/../components/footer_publico.php'; ?>
