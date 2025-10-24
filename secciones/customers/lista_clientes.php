<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

// Verificar si se solicita generar PDF
$generarPDF = isset($_GET['pdf']) && $_GET['pdf'] == '1';
$id = $_GET['id'] ?? null;

if (!$generarPDF || !$id) {
    header('Location: index.php');
    exit;
}

// Obtener datos del cliente específico
$stmt = $pdo->prepare('SELECT * FROM customer WHERE customer_id = :id');
$stmt->execute(['id' => $id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header('Location: index.php');
    exit;
}

// Generar página HTML lista para imprimir (usar Ctrl+P del navegador)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Cliente - <?php echo htmlspecialchars($cliente['first_name'] . ' ' . $cliente['last_name']); ?></title>
    <style>
        @page {
            size: letter;
            margin: 1.5cm;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
        body { font-family: Arial, sans-serif; margin: 30px; color: #333; }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #4CAF50;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        .id-badge {
            background-color: #4CAF50;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            display: inline-block;
            margin-top: 10px;
        }
        .seccion {
            margin-bottom: 25px;
        }
        .seccion h2 {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            margin: 0 0 15px 0;
            font-size: 16px;
        }
        .campo {
            margin-bottom: 12px;
            padding: 8px;
            background-color: #f9f9f9;
            border-left: 4px solid #4CAF50;
        }
        .campo label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 150px;
        }
        .campo span {
            color: #333;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        .btn-print { 
            position: fixed; 
            top: 20px; 
            right: 20px; 
            padding: 10px 20px; 
            background: #4CAF50; 
            color: white; 
            border: none; 
            cursor: pointer; 
            border-radius: 5px;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .btn-print:hover { background: #45a049; }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
    
    <div class="header">
        <h1>Bike Store</h1>
        <p>Ficha de Cliente</p>
        <div class="id-badge">ID: <?php echo htmlspecialchars($cliente['customer_id']); ?></div>
    </div>
    
    <div class="cliente-info">
        <div class="seccion">
            <h2>Información Personal</h2>
            <div class="campo">
                <label>Nombre:</label>
                <span><?php echo htmlspecialchars($cliente['first_name']); ?></span>
            </div>
            <div class="campo">
                <label>Apellido:</label>
                <span><?php echo htmlspecialchars($cliente['last_name']); ?></span>
            </div>
            <div class="campo">
                <label>Nombre Completo:</label>
                <span><?php echo htmlspecialchars($cliente['first_name'] . ' ' . $cliente['last_name']); ?></span>
            </div>
        </div>
        
        <div class="seccion">
            <h2>Información de Contacto</h2>
            <div class="campo">
                <label>Teléfono:</label>
                <span><?php echo htmlspecialchars($cliente['phone'] ?? 'No especificado'); ?></span>
            </div>
            <div class="campo">
                <label>Email:</label>
                <span><?php echo htmlspecialchars($cliente['email']); ?></span>
            </div>
        </div>
        
        <div class="seccion">
            <h2>Dirección</h2>
            <div class="campo">
                <label>Calle:</label>
                <span><?php echo htmlspecialchars($cliente['street'] ?? 'No especificado'); ?></span>
            </div>
            <div class="campo">
                <label>Ciudad:</label>
                <span><?php echo htmlspecialchars($cliente['city'] ?? 'No especificado'); ?></span>
            </div>
            <div class="campo">
                <label>Estado:</label>
                <span><?php echo htmlspecialchars($cliente['state'] ?? 'No especificado'); ?></span>
            </div>
            <div class="campo">
                <label>Código Postal:</label>
                <span><?php echo htmlspecialchars($cliente['postal_code'] ?? 'No especificado'); ?></span>
            </div>
        </div>
    </div>
    
    <div class="footer">
        Bike Store - Sistema de Gestión de Clientes<br>
        Documento generado el <?php echo date('d/m/Y'); ?> a las <?php echo date('H:i:s'); ?><br>
        Este documento es confidencial y de uso exclusivo para Bike Store
    </div>
</body>
</html>
