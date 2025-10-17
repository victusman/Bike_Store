<?php
session_start();
require_once __DIR__ . '/db.php';

// Si el usuario ya está autenticado, redirigir al index
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Usuario y contraseña requeridos.';
    } else {
  // Permitir login usando el nombre de usuario o el email
  // Usar nombres de parámetro distintos para cada placeholder para evitar "Invalid parameter number"
  $stmt = $pdo->prepare('SELECT user_id, usuario, password FROM usuarios WHERE usuario = :u OR email = :e LIMIT 1');
  $stmt->execute(['u' => $username, 'e' => $username]);
    $user = $stmt->fetch();

    $authenticated = false;
    if ($user) {
      $stored = $user['password'] ?? '';
      // 1) Si es hash moderno (password_hash / bcrypt / argon), usar password_verify
      if (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0 || stripos($stored, 'argon') !== false) {
        $authenticated = password_verify($password, $stored);
      } else {
        // 2) Caso legacy: hash hex (ej. SHA256) almacenado como 64 hex chars
        if (preg_match('/^[0-9a-f]{64}$/i', $stored)) {
          if (hash('sha256', $password) === $stored) {
            $authenticated = true;
            // Migrar el hash a password_hash() de forma transparente
            try {
              $newHash = password_hash($password, PASSWORD_DEFAULT);
              $up = $pdo->prepare('UPDATE usuarios SET password = :p WHERE user_id = :id');
              $up->execute(['p' => $newHash, 'id' => $user['user_id']]);
            } catch (Exception $e) {
              // No detener el login si la migración falla; solo ignorar
              error_log('Password migration failed for user_id ' . $user['user_id'] . ': ' . $e->getMessage());
            }
          }
        }
      }
    }

    if ($authenticated) {
      // Autenticación correcta: regenerar id de sesión
      session_regenerate_id(true);
      $_SESSION['user_id'] = $user['user_id'];
      $_SESSION['usuario'] = $user['usuario'];
      header('Location: index.php');
      exit;
    } else {
      $error = 'Usuario o contraseña inválidos.';
    }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - Bike Store</title>
  <!-- Bootstrap 5 CDN (uso temporal al quitar header) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="" crossorigin="anonymous">
  <style>body{background:#f8f9fa}</style>
</head>
<body>

<div class="d-flex align-items-center justify-content-center" style="min-height:70vh;">
  <div class="card shadow-sm" style="width:100%;max-width:420px;">
    <div class="card-body">
      <h2 class="card-title mb-3">Acceder</h2>
      <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post" action="">
        <div class="mb-3">
          <label for="usuario" class="form-label">Usuario o email</label>
          <input id="usuario" name="usuario" class="form-control" required autofocus value="<?php echo htmlspecialchars($_POST['usuario'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input id="password" name="password" type="password" class="form-control" required>
        </div>
        <div class="d-grid">
          <button class="btn btn-primary" type="submit">Entrar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
