<?php
session_start();
require_once __DIR__ . '/../../db.php';

// Si ya está autenticado, redirigir al perfil
if (isset($_SESSION['customer_id'])) {
    header('Location: perfil.php');
    exit;
}

$pageTitle = 'Iniciar Sesión - Bike Store';
$mensaje = '';
$error = '';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Por favor ingresa tu correo electrónico';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor ingresa un correo válido';
    } else {
        // Buscar cliente por email
        $stmt = $pdo->prepare("SELECT * FROM customer WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $cliente = $stmt->fetch();
        
        if ($cliente) {
            // Cliente encontrado - crear sesión
            $_SESSION['customer_id'] = $cliente['customer_id'];
            $_SESSION['customer_name'] = $cliente['first_name'] . ' ' . $cliente['last_name'];
            $_SESSION['customer_email'] = $cliente['email'];
            
            // Redirigir a checkout si venía del carrito, sino al perfil
            $redirect = $_GET['redirect'] ?? 'perfil.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            // Cliente no existe - redirigir a registro
            $_SESSION['email_temp'] = $email;
            header('Location: registro.php');
            exit;
        }
    }
}

include __DIR__ . '/../components/header_publico.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <!-- Card de Login -->
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <i class="fas fa-user-circle fa-3x mb-2"></i>
                    <h3 class="mb-0">Iniciar Sesión</h3>
                    <p class="mb-0 small">Accede con tu correo electrónico</p>
                </div>
                
                <div class="card-body p-4">
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
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>¡Sin contraseñas!</strong> Solo necesitas tu correo electrónico para acceder.
                    </div>
                    
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">
                                <i class="fas fa-envelope"></i> Correo Electrónico
                            </label>
                            <input type="email" 
                                   class="form-control form-control-lg" 
                                   id="email" 
                                   name="email" 
                                   placeholder="tu@email.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   required 
                                   autofocus>
                            <div class="form-text">
                                Si ya tienes una cuenta, te reconoceremos automáticamente
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Continuar
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="text-muted mb-2">¿Primera vez aquí?</p>
                        <a href="registro.php" class="btn btn-outline-success">
                            <i class="fas fa-user-plus"></i> Crear Cuenta Nueva
                        </a>
                    </div>
                </div>
                
                <div class="card-footer bg-light text-center">
                    <small class="text-muted">
                        <i class="fas fa-lock"></i> Tus datos están protegidos y seguros
                    </small>
                </div>
            </div>
            
            <!-- Beneficios de crear cuenta -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="fas fa-star text-warning"></i> Beneficios de tener una cuenta:</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Proceso de compra más rápido</li>
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Historial de pedidos</li>
                        <li class="mb-2"><i class="fas fa-check text-success"></i> Guardar direcciones de envío</li>
                        <li class="mb-0"><i class="fas fa-check text-success"></i> Ofertas exclusivas</li>
                    </ul>
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
    
    .card-header {
        border: none;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
</style>

<?php include __DIR__ . '/../components/footer_publico.php'; ?>
