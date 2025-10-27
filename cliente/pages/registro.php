<?php
session_start();
require_once __DIR__ . '/../../db.php';

// Si ya está autenticado, redirigir al perfil
if (isset($_SESSION['customer_id'])) {
    header('Location: perfil.php');
    exit;
}

$pageTitle = 'Crear Cuenta - Bike Store';
$error = '';
$email_temp = $_SESSION['email_temp'] ?? '';

// Procesar el formulario
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
        $error = 'Por favor completa los campos obligatorios (nombre, apellido y email)';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor ingresa un correo válido';
    } else {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT customer_id FROM customer WHERE email = :email");
        $stmt->execute(['email' => $email]);
        
        if ($stmt->fetch()) {
            $error = 'Este correo ya está registrado. <a href="login_cliente.php">Inicia sesión aquí</a>';
        } else {
            // Insertar nuevo cliente
            try {
                $stmt = $pdo->prepare("INSERT INTO customer 
                    (first_name, last_name, email, phone, street, city, state, zip_code, created_at) 
                    VALUES (:first_name, :last_name, :email, :phone, :street, :city, :state, :zip_code, NOW())");
                
                $stmt->execute([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'street' => $street,
                    'city' => $city,
                    'state' => $state,
                    'zip_code' => $zip_code
                ]);
                
                // Obtener el ID del cliente recién creado
                $customer_id = $pdo->lastInsertId();
                
                // Crear sesión
                $_SESSION['customer_id'] = $customer_id;
                $_SESSION['customer_name'] = $first_name . ' ' . $last_name;
                $_SESSION['customer_email'] = $email;
                
                // Limpiar email temporal
                unset($_SESSION['email_temp']);
                
                // Redirigir
                $redirect = $_GET['redirect'] ?? 'perfil.php';
                header('Location: ' . $redirect . '?registro=exitoso');
                exit;
                
            } catch (Exception $e) {
                $error = 'Error al crear la cuenta. Por favor intenta nuevamente.';
            }
        }
    }
}

include __DIR__ . '/../components/header_publico.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/Bike_Store/cliente/index.php">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="login_cliente.php">Iniciar Sesión</a></li>
                    <li class="breadcrumb-item active">Crear Cuenta</li>
                </ol>
            </nav>
            
            <!-- Card de Registro -->
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white text-center py-4">
                    <i class="fas fa-user-plus fa-3x mb-2"></i>
                    <h3 class="mb-0">Crear Cuenta Nueva</h3>
                    <p class="mb-0 small">Completa tus datos para comenzar</p>
                </div>
                
                <div class="card-body p-4">
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Los campos con <span class="text-danger">*</span> son obligatorios
                    </div>
                    
                    <form method="POST" action="">
                        <!-- Información Personal -->
                        <h5 class="mb-3"><i class="fas fa-user"></i> Información Personal</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label fw-bold">
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="first_name" 
                                       name="first_name"
                                       value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
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
                                       value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                                       required>
                            </div>
                        </div>
                        
                        <!-- Información de Contacto -->
                        <h5 class="mb-3 mt-4"><i class="fas fa-envelope"></i> Información de Contacto</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email"
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? $email_temp); ?>"
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
                                       placeholder="(123) 456-7890"
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <!-- Dirección de Envío (Opcional) -->
                        <h5 class="mb-3 mt-4"><i class="fas fa-map-marker-alt"></i> Dirección de Envío <small class="text-muted">(Opcional)</small></h5>
                        
                        <div class="mb-3">
                            <label for="street" class="form-label fw-bold">Calle y Número</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="street" 
                                   name="street"
                                   placeholder="Ej: Av. Principal 123"
                                   value="<?php echo htmlspecialchars($_POST['street'] ?? ''); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label fw-bold">Ciudad</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="city" 
                                       name="city"
                                       value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label fw-bold">Estado/Provincia</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="state" 
                                       name="state"
                                       value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="zip_code" class="form-label fw-bold">Código Postal</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="zip_code" 
                                       name="zip_code"
                                       value="<?php echo htmlspecialchars($_POST['zip_code'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="terminos" required>
                            <label class="form-check-label" for="terminos">
                                Acepto los <a href="#" target="_blank">Términos y Condiciones</a> y la 
                                <a href="#" target="_blank">Política de Privacidad</a>
                            </label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle"></i> Crear Mi Cuenta
                            </button>
                            <a href="login_cliente.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Login
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="card-footer bg-light text-center">
                    <small class="text-muted">
                        ¿Ya tienes cuenta? <a href="login_cliente.php" class="fw-bold">Inicia sesión aquí</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
</style>

<?php include __DIR__ . '/../components/footer_publico.php'; ?>
