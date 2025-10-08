<!DOCTYPE html>
<html lang="es">
<head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Bike Store</title>
        <!-- Bootstrap 5 CSS: preferir local en assets/ si existe, si no usar CDN -->
        <?php if (file_exists(__DIR__ . '/../assets/css/bootstrap.min.css')): ?>
            <!-- Local Bootstrap with SRI (SHA384) -->
            <link href="/Bike_Store/assets/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <?php else: ?>
            <!-- CDN fallback with SRI -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <?php endif; ?>
        <link href="/Bike_Store/assets/css/custom.css" rel="stylesheet">
</head>
<body class="bg-light">
<header class="bg-white shadow-sm">
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <a class="navbar-brand" href="/Bike_Store/index.php">Bike Store</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="/Bike_Store/index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="/Bike_Store/secciones/Productos/index.php">Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="/Bike_Store/secciones/Usuarios/index.php">Usuarios</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main class="container my-4">
