<!--esto tiene que estar debajo de el booststrap --> 
<!--paginacion de tablas --> 
<!--- <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
agregar  -->
<!--- <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css" />
  
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script> --->
<!DOCTYPE html>
<html lang="es">
<head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Bike Store</title>
        <!-- Bootstrap 5 CSS: preferir local en assets/ si existe, si no usar CDN -->
        <!-- Usar siempre la copia local de Bootstrap (versión 5) descargada en assets/ -->
        <link href="/Bike_Store/assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="/Bike_Store/assets/css/custom.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
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
                    <li class="nav-item"><a class="nav-link" href="/Bike_Store/secciones/Categorias/index.php">Categorías</a></li>
                    <li class="nav-item"><a class="nav-link" href="/Bike_Store/secciones/customers/index.php">Clientes</a></li>
                    <li class="nav-item"><a class="nav-link" href="/Bike_Store/secciones/Productos/index.php">Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="/Bike_Store/secciones/stocks/index.php">Stocks</a></li>
                    <li class="nav-item"><a class="nav-link" href="/Bike_Store/secciones/stores/index.php">Tiendas</a></li>
                    <li class="nav-item"><a class="nav-link" href="/Bike_Store/secciones/Orders/index.php">Pedidos</a></li>
                    <li class="nav-item"><a class="nav-link" href="/Bike_Store/secciones/Usuarios/index.php">Usuarios</a></li>
                    <li class="nav-item"><a class="nav-link" href="/Bike_Store/cerrar.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main class="container my-4">
