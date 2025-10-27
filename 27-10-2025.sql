-- ============================================================================
-- Script de actualización de Base de Datos - Bike Store
-- Fecha: 27/10/2025
-- Descripción: Preparación para implementar área pública de clientes
-- ============================================================================

USE `Bike_Store`;

-- ============================================================================
-- PASO 1: AGREGAR CAMPOS A TABLA PRODUCTOS
-- ============================================================================

-- Agregar campo 'destacado' para productos que aparecerán en el carrusel
ALTER TABLE `productos` 
ADD COLUMN `destacado` BOOLEAN NOT NULL DEFAULT FALSE 
COMMENT 'Indica si el producto aparece en el carrusel de destacados' 
AFTER `category_id`;

-- Agregar campo 'descuento' para aplicar descuentos a productos
ALTER TABLE `productos` 
ADD COLUMN `descuento` DECIMAL(5,2) NOT NULL DEFAULT 0.00 
COMMENT 'Porcentaje de descuento aplicado al producto (0.00 - 100.00)' 
AFTER `destacado`;

-- Agregar campo 'stock_disponible' para control de inventario
-- Verificar primero si no existe
ALTER TABLE `productos` 
ADD COLUMN `stock_disponible` INT NOT NULL DEFAULT 0 
COMMENT 'Cantidad disponible en inventario' 
AFTER `descuento`;

-- Agregar campo 'ventas_totales' para identificar productos más vendidos
ALTER TABLE `productos` 
ADD COLUMN `ventas_totales` INT NOT NULL DEFAULT 0 
COMMENT 'Total de unidades vendidas (para productos más vendidos)' 
AFTER `stock_disponible`;

-- ============================================================================
-- PASO 2: CREAR TABLA CARRITO_TEMPORAL
-- ============================================================================

-- Tabla para gestionar carritos de clientes no registrados (sesión)
CREATE TABLE IF NOT EXISTS `carrito_temporal` (
  `carrito_id` INT NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(255) NOT NULL COMMENT 'ID de sesión del navegador',
  `product_id` INT NOT NULL COMMENT 'Producto agregado al carrito',
  `quantity` INT NOT NULL DEFAULT 1 COMMENT 'Cantidad del producto',
  `precio_unitario` DECIMAL(10,2) NOT NULL COMMENT 'Precio al momento de agregar',
  `descuento_aplicado` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Descuento vigente al agregar',
  `fecha_agregado` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Cuándo se agregó al carrito',
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`carrito_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_product_id` (`product_id`),
  CONSTRAINT `fk_carrito_product` FOREIGN KEY (`product_id`) 
    REFERENCES `productos` (`product_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Almacena productos en carrito de clientes no autenticados';

-- ============================================================================
-- PASO 3: CREAR TABLA METODOS_PAGO
-- ============================================================================

-- Tabla para gestionar métodos de pago disponibles
CREATE TABLE IF NOT EXISTS `metodos_pago` (
  `metodo_id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL COMMENT 'Nombre del método de pago',
  `descripcion` TEXT COMMENT 'Descripción del método',
  `icono` VARCHAR(255) DEFAULT NULL COMMENT 'Ruta del icono/imagen',
  `activo` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Si el método está disponible',
  `tiene_costo` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Si tiene costo adicional',
  `costo_adicional` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Costo adicional si aplica',
  `orden_visualizacion` INT DEFAULT 0 COMMENT 'Orden de aparición en el listado',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`metodo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Métodos de pago disponibles en la tienda';

-- ============================================================================
-- PASO 4: AGREGAR CAMPO METODO_PAGO A TABLA ORDERS
-- ============================================================================

-- Agregar campo para registrar el método de pago usado en cada pedido
ALTER TABLE `orders` 
ADD COLUMN `metodo_pago_id` INT DEFAULT NULL 
COMMENT 'Método de pago utilizado' 
AFTER `estado`;

-- Crear clave foránea
ALTER TABLE `orders` 
ADD CONSTRAINT `fk_orders_metodo_pago` 
FOREIGN KEY (`metodo_pago_id`) 
REFERENCES `metodos_pago` (`metodo_id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- Agregar índice
ALTER TABLE `orders` 
ADD KEY `idx_metodo_pago` (`metodo_pago_id`);

-- ============================================================================
-- PASO 5: INSERTAR MÉTODOS DE PAGO PREDETERMINADOS
-- ============================================================================

INSERT INTO `metodos_pago` (`nombre`, `descripcion`, `activo`, `tiene_costo`, `costo_adicional`, `orden_visualizacion`) VALUES
('Envío Gratuito', 'Pago contra entrega sin costo adicional', TRUE, FALSE, 0.00, 1),
('Carteras Electrónicas (QR)', 'Pago mediante código QR con billeteras digitales', TRUE, FALSE, 0.00, 2),
('Débito Automático', 'Cargo automático a cuenta bancaria', TRUE, FALSE, 0.00, 3),
('Efectivo Contra Entrega', 'Pago en efectivo al recibir el producto', TRUE, FALSE, 0.00, 4),
('Transferencia Bancaria', 'Transferencia directa a cuenta de la tienda', TRUE, FALSE, 0.00, 5),
('Tarjeta de Crédito/Débito', 'Pago con tarjeta bancaria', TRUE, FALSE, 0.00, 6);

-- ============================================================================
-- PASO 6: MARCAR ALGUNOS PRODUCTOS COMO DESTACADOS (EJEMPLO)
-- ============================================================================

-- Opción A: Marcar productos específicos por ID (recomendado - sin errores)
-- Ajusta los IDs según los productos que existan en tu base de datos
UPDATE `productos` 
SET `destacado` = TRUE, `stock_disponible` = 50
WHERE `product_id` IN (1, 2, 3, 4, 5);

-- Opción B: Si prefieres usar subconsulta (desactivar safe mode temporalmente)
-- SET SQL_SAFE_UPDATES = 0;
-- UPDATE `productos` 
-- SET `destacado` = TRUE, `stock_disponible` = 50
-- WHERE `product_id` IN (
--   SELECT `product_id` FROM (
--     SELECT `product_id` FROM `productos` ORDER BY `product_id` LIMIT 5
--   ) AS temp
-- );
-- SET SQL_SAFE_UPDATES = 1;

-- ============================================================================
-- PASO 7: AGREGAR DESCUENTOS A ALGUNOS PRODUCTOS (EJEMPLO)
-- ============================================================================

-- Opción A: Aplicar descuento a productos específicos (recomendado - sin errores)
UPDATE `productos` 
SET `descuento` = 15.00
WHERE `product_id` IN (2, 4, 6);

-- Aplicar 20% de descuento a otros productos
UPDATE `productos` 
SET `descuento` = 20.00
WHERE `product_id` IN (3, 5);

-- Opción B: Si prefieres aleatorio (desactivar safe mode temporalmente)
-- SET SQL_SAFE_UPDATES = 0;
-- UPDATE `productos` 
-- SET `descuento` = 15.00
-- WHERE `product_id` IN (
--   SELECT `product_id` FROM (
--     SELECT `product_id` FROM `productos` ORDER BY RAND() LIMIT 3
--   ) AS temp2
-- );
-- SET SQL_SAFE_UPDATES = 1;

-- ============================================================================
-- PASO 8: ACTUALIZAR STOCK DISPONIBLE DE TODOS LOS PRODUCTOS
-- ============================================================================

-- Asignar stock manualmente a productos específicos
UPDATE `productos` SET `stock_disponible` = 50 WHERE `product_id` = 1;
UPDATE `productos` SET `stock_disponible` = 30 WHERE `product_id` = 2;
UPDATE `productos` SET `stock_disponible` = 75 WHERE `product_id` = 3;
UPDATE `productos` SET `stock_disponible` = 40 WHERE `product_id` = 4;
UPDATE `productos` SET `stock_disponible` = 60 WHERE `product_id` = 5;
UPDATE `productos` SET `stock_disponible` = 45 WHERE `product_id` = 6;
UPDATE `productos` SET `stock_disponible` = 80 WHERE `product_id` = 7;
UPDATE `productos` SET `stock_disponible` = 35 WHERE `product_id` = 8;
UPDATE `productos` SET `stock_disponible` = 90 WHERE `product_id` = 9;
UPDATE `productos` SET `stock_disponible` = 55 WHERE `product_id` = 10;

-- Opción alternativa: Asignar stock a TODOS los productos que tengan stock = 0
-- (Solo si tienes muchos productos y quieres asignar stock aleatorio)
-- SET SQL_SAFE_UPDATES = 0;
-- UPDATE `productos` 
-- SET `stock_disponible` = FLOOR(10 + (RAND() * 90))
-- WHERE `stock_disponible` = 0 OR `product_id` > 0;
-- SET SQL_SAFE_UPDATES = 1;

-- ============================================================================
-- PASO 9: CREAR ÍNDICES ADICIONALES PARA MEJORAR RENDIMIENTO
-- ============================================================================

-- Índice para búsqueda de productos destacados
CREATE INDEX `idx_productos_destacado` ON `productos` (`destacado`);

-- Índice para productos con descuento
CREATE INDEX `idx_productos_descuento` ON `productos` (`descuento`);

-- Índice para productos con stock
CREATE INDEX `idx_productos_stock` ON `productos` (`stock_disponible`);

-- Índice compuesto para productos destacados activos
CREATE INDEX `idx_destacado_stock` ON `productos` (`destacado`, `stock_disponible`);

-- ============================================================================
-- PASO 10: CREAR TABLA PARA REGISTRO DE SESIONES DE CLIENTES
-- ============================================================================

-- Tabla para gestionar sesiones de clientes (login sin contraseña)
CREATE TABLE IF NOT EXISTS `sesiones_cliente` (
  `sesion_id` INT NOT NULL AUTO_INCREMENT,
  `customer_id` INT NOT NULL COMMENT 'Cliente asociado',
  `session_token` VARCHAR(255) NOT NULL COMMENT 'Token único de sesión',
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP del cliente',
  `user_agent` VARCHAR(255) DEFAULT NULL COMMENT 'Navegador usado',
  `fecha_inicio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_ultimo_acceso` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `activa` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`sesion_id`),
  UNIQUE KEY `uk_session_token` (`session_token`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_activa` (`activa`),
  CONSTRAINT `fk_sesion_customer` 
    FOREIGN KEY (`customer_id`) 
    REFERENCES `customer` (`customer_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Gestión de sesiones de clientes en el frontend público';

-- ============================================================================
-- PASO 11: AGREGAR CAMPO PARA CÓDIGO DE VERIFICACIÓN EN CUSTOMER
-- ============================================================================

-- Campo para código temporal de verificación (login sin contraseña)
ALTER TABLE `customer` 
ADD COLUMN `codigo_verificacion` VARCHAR(10) DEFAULT NULL 
COMMENT 'Código temporal para login sin contraseña' 
AFTER `email`;

ALTER TABLE `customer` 
ADD COLUMN `codigo_expiracion` TIMESTAMP NULL DEFAULT NULL 
COMMENT 'Fecha de expiración del código' 
AFTER `codigo_verificacion`;

-- ============================================================================
-- PASO 12: CREAR VISTA PARA PRODUCTOS MÁS VENDIDOS
-- ============================================================================

CREATE OR REPLACE VIEW `v_productos_mas_vendidos` AS
SELECT 
  p.`product_id`,
  p.`product_name`,
  p.`foto`,
  p.`model_year`,
  p.`price`,
  p.`descuento`,
  p.`stock_disponible`,
  p.`ventas_totales`,
  c.`descripcion` AS categoria,
  ROUND(p.`price` * (1 - p.`descuento` / 100), 2) AS precio_con_descuento
FROM `productos` p
LEFT JOIN `categoria` c ON p.`category_id` = c.`category_id`
WHERE p.`stock_disponible` > 0
ORDER BY p.`ventas_totales` DESC, p.`product_id` DESC
LIMIT 4;

-- ============================================================================
-- PASO 13: CREAR VISTA PARA PRODUCTOS DESTACADOS
-- ============================================================================

CREATE OR REPLACE VIEW `v_productos_destacados` AS
SELECT 
  p.`product_id`,
  p.`product_name`,
  p.`foto`,
  p.`model_year`,
  p.`price`,
  p.`descuento`,
  p.`stock_disponible`,
  c.`descripcion` AS categoria,
  ROUND(p.`price` * (1 - p.`descuento` / 100), 2) AS precio_con_descuento
FROM `productos` p
LEFT JOIN `categoria` c ON p.`category_id` = c.`category_id`
WHERE p.`destacado` = TRUE AND p.`stock_disponible` > 0
ORDER BY p.`product_id` DESC
LIMIT 5;

-- ============================================================================
-- PASO 14: CREAR PROCEDIMIENTO PARA LIMPIAR CARRITOS ANTIGUOS
-- ============================================================================

DELIMITER //

CREATE PROCEDURE `sp_limpiar_carritos_antiguos`()
BEGIN
  -- Eliminar carritos con más de 7 días sin actualización
  DELETE FROM `carrito_temporal` 
  WHERE `fecha_actualizacion` < DATE_SUB(NOW(), INTERVAL 7 DAY);
  
  SELECT CONCAT('Carritos antiguos eliminados: ', ROW_COUNT()) AS resultado;
END //

DELIMITER ;

-- ============================================================================
-- PASO 15: CREAR TRIGGER PARA ACTUALIZAR VENTAS_TOTALES
-- ============================================================================

DELIMITER //

CREATE TRIGGER `tr_actualizar_ventas_producto`
AFTER INSERT ON `order_items`
FOR EACH ROW
BEGIN
  -- Incrementar el contador de ventas del producto
  UPDATE `productos` 
  SET `ventas_totales` = `ventas_totales` + NEW.`quantity`
  WHERE `product_id` = NEW.`product_id`;
END //

DELIMITER ;

-- ============================================================================
-- VERIFICACIÓN FINAL
-- ============================================================================

-- Mostrar resumen de cambios aplicados
SELECT 'Script ejecutado exitosamente!' AS estado;

SELECT 
  'Productos con stock' AS metrica,
  COUNT(*) AS total
FROM `productos` 
WHERE `stock_disponible` > 0
UNION ALL
SELECT 
  'Productos destacados',
  COUNT(*)
FROM `productos` 
WHERE `destacado` = TRUE
UNION ALL
SELECT 
  'Productos con descuento',
  COUNT(*)
FROM `productos` 
WHERE `descuento` > 0
UNION ALL
SELECT 
  'Métodos de pago disponibles',
  COUNT(*)
FROM `metodos_pago`
WHERE `activo` = TRUE;

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
-- Notas:
-- - Ejecutar este script en MySQL Workbench o phpMyAdmin
-- - Asegurarse de tener backup antes de ejecutar
-- - Verificar que XAMPP esté corriendo (Apache + MySQL)
-- - El script es idempotente (se puede ejecutar múltiples veces)
-- ============================================================================
