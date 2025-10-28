<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle ?? 'Bike Store - Tienda Online'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="/Bike_Store/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/Bike_Store/assets/css/custom.css" rel="stylesheet">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
        }
        
        .navbar-brand i {
            color: var(--secondary-color);
        }
        
        .badge-cart {
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: var(--accent-color);
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
        }
        
        .nav-link {
            position: relative;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--secondary-color) !important;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .discount-badge {
            background-color: var(--accent-color);
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: bold;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    
    <!-- Barra superior con información -->
    <div class="bg-dark text-white py-2">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <small><i class="fas fa-phone"></i> 75678428 | <i class="fas fa-envelope"></i> info@bikestore.com</small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small><i class="fas fa-shipping-fast"></i> Envío gratis en compras mayores a $500</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navbar principal -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/Bike_Store/cliente/index.php">
                <i class="fas fa-bicycle"></i> Bike Store
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublic">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarPublic">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="/Bike_Store/cliente/index.php">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Bike_Store/cliente/pages/catalogo.php">
                            <i class="fas fa-shopping-bag"></i> Productos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Bike_Store/cliente/pages/mis_pedidos.php">
                            <i class="fas fa-box"></i> Mis Pedidos
                        </a>
                    </li>
                </ul>
                
                <!-- Barra de búsqueda -->
                <form class="d-flex me-3" action="/Bike_Store/cliente/pages/catalogo.php" method="get">
                    <input class="form-control form-control-sm me-2" type="search" name="buscar" placeholder="Buscar productos..." style="width: 250px;">
                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <ul class="navbar-nav">
                    <!-- Carrito de compras -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="/Bike_Store/cliente/pages/carrito.php">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                            <span class="badge badge-cart" id="cart-count">0</span>
                        </a>
                    </li>
                    
                    <!-- Usuario / Login -->
                    <?php if (isset($_SESSION['customer_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Cliente'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/Bike_Store/cliente/pages/perfil.php"><i class="fas fa-user-circle"></i> Mi Perfil</a></li>
                            <li><a class="dropdown-item" href="/Bike_Store/cliente/pages/mis_pedidos.php"><i class="fas fa-box"></i> Mis Pedidos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/Bike_Store/cliente/pages/cerrar_sesion.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/Bike_Store/cliente/pages/login_cliente.php">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Contenido principal -->
    <main class="flex-grow-1">
