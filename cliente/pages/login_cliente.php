<?php
session_start();
require_once __DIR__ . '/../../db.php';

// Redirecciones si ya está autenticado
if (!empty($_SESSION['user_id'])) {
    // Admin ya logueado -> área administrativa
    header('Location: /Bike_Store/index.php');
    exit;
}
if (!empty($_SESSION['customer_id'])) {
    // Cliente ya logueado -> área cliente
    header('Location: /Bike_Store/cliente/index.php');
    exit;
}

$pageTitle = 'Iniciar Sesión - Bike Store';
$mensaje = '';
$error = '';

// Procesar el formulario (puede venir login_tipo = 'admin' o 'cliente')
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['login_tipo'] ?? 'cliente';

    if ($tipo === 'admin') {
        // Login de administrador (usuario + password)
        $username = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Usuario y contraseña requeridos.';
        } else {
            $stmt = $pdo->prepare('SELECT user_id, usuario, password FROM usuarios WHERE usuario = :u OR email = :e LIMIT 1');
            $stmt->execute(['u' => $username, 'e' => $username]);
            $user = $stmt->fetch();

            $authenticated = false;
            if ($user) {
                $stored = $user['password'] ?? '';
                if (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0 || stripos($stored, 'argon') !== false) {
                    $authenticated = password_verify($password, $stored);
                } else {
                    if (preg_match('/^[0-9a-f]{64}$/i', $stored)) {
                        if (hash('sha256', $password) === $stored) {
                            $authenticated = true;
                            // Intentar migrar a password_hash
                            try {
                                $newHash = password_hash($password, PASSWORD_DEFAULT);
                                $up = $pdo->prepare('UPDATE usuarios SET password = :p WHERE user_id = :id');
                                $up->execute(['p' => $newHash, 'id' => $user['user_id']]);
                            } catch (Exception $e) {
                                error_log('Password migration failed for user_id ' . $user['user_id'] . ': ' . $e->getMessage());
                            }
                        }
                    }
                }
            }

            if ($authenticated) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['usuario'] = $user['usuario'];
                header('Location: /Bike_Store/index.php');
                exit;
            } else {
                $error = 'Usuario o contraseña inválidos.';
            }
        }

    } else {
        // Login cliente (email, passwordless)
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

                // Redirigir a la URL especificada (checkout u otra), sino al área cliente
                $redirect = $_GET['redirect'] ?? '/Bike_Store/cliente/index.php';
                // Asegurar que las rutas relativas se normalicen
                if (!preg_match('#^https?://#i', $redirect) && strpos($redirect, '/') !== 0) {
                    // ruta relativa dentro de cliente/, convertir a ruta absoluta del cliente
                    $redirect = '/Bike_Store/cliente/' . ltrim($redirect, '/');
                }
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
}

include __DIR__ . '/../components/header_publico.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <i class="fas fa-user-circle fa-3x mb-2"></i>
                    <h3 class="mb-0">Iniciar Sesión</h3>
                    <p class="mb-0 small">Elige iniciar como cliente o administrador</p>
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

                    <ul class="nav nav-tabs mb-3" id="loginTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="cliente-tab" data-bs-toggle="tab" data-bs-target="#clienteTab" type="button" role="tab">Cliente</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#adminTab" type="button" role="tab">Administrador</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="clienteTab" role="tabpanel">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>¡Sin contraseñas!</strong> Solo necesitas tu correo electrónico para acceder.
                            </div>

                            <form method="POST" action="">
                                <input type="hidden" name="login_tipo" value="cliente">
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
                                        <i class="fas fa-sign-in-alt"></i> Continuar como Cliente
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

                        <div class="tab-pane fade" id="adminTab" role="tabpanel">
                            <p class="text-muted">Accede con tu usuario o email y contraseña de administrador.</p>
                            <form method="POST" action="">
                                <input type="hidden" name="login_tipo" value="admin">
                                <div class="mb-3">
                                    <label for="usuario" class="form-label">Usuario o email</label>
                                    <input id="usuario" name="usuario" class="form-control" value="<?php echo htmlspecialchars($_POST['usuario'] ?? '') ?>" required autofocus>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input id="password" name="password" type="password" class="form-control" required>
                                </div>
                                <div class="d-grid">
                                    <button class="btn btn-dark" type="submit"><i class="fas fa-user-shield"></i> Iniciar como Administrador</button>
                                </div>
                            </form>
                        </div>
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
