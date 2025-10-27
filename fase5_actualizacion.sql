-- Script de actualización para FASE 5: PROCESO DE COMPRA
-- Fecha: 27 de octubre de 2025

USE Bike_Store;

-- Verificar si ya existen los campos antes de agregarlos
-- MySQL no soporta IF NOT EXISTS en ALTER TABLE, así que usamos un procedimiento

DELIMITER //

CREATE PROCEDURE agregar_campos_fase5()
BEGIN
    -- Verificar y agregar subtotal
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = 'Bike_Store' 
        AND TABLE_NAME = 'orders' 
        AND COLUMN_NAME = 'subtotal'
    ) THEN
        ALTER TABLE orders ADD COLUMN subtotal DECIMAL(10, 2) DEFAULT 0 COMMENT 'Subtotal antes de descuentos';
    END IF;
    
    -- Verificar y agregar descuento
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = 'Bike_Store' 
        AND TABLE_NAME = 'orders' 
        AND COLUMN_NAME = 'descuento'
    ) THEN
        ALTER TABLE orders ADD COLUMN descuento DECIMAL(10, 2) DEFAULT 0 COMMENT 'Monto total de descuentos aplicados';
    END IF;
    
    -- Verificar y agregar costo_envio
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = 'Bike_Store' 
        AND TABLE_NAME = 'orders' 
        AND COLUMN_NAME = 'costo_envio'
    ) THEN
        ALTER TABLE orders ADD COLUMN costo_envio DECIMAL(10, 2) DEFAULT 0 COMMENT 'Costo del envío';
    END IF;
    
    -- Verificar y agregar total_amount
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = 'Bike_Store' 
        AND TABLE_NAME = 'orders' 
        AND COLUMN_NAME = 'total_amount'
    ) THEN
        ALTER TABLE orders ADD COLUMN total_amount DECIMAL(10, 2) DEFAULT 0 COMMENT 'Total final a pagar';
    END IF;
    
    -- Verificar y agregar metodo_pago
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = 'Bike_Store' 
        AND TABLE_NAME = 'orders' 
        AND COLUMN_NAME = 'metodo_pago'
    ) THEN
        ALTER TABLE orders ADD COLUMN metodo_pago VARCHAR(100) COMMENT 'Método de pago utilizado';
    END IF;
    
    -- Verificar y agregar direccion_envio
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = 'Bike_Store' 
        AND TABLE_NAME = 'orders' 
        AND COLUMN_NAME = 'direccion_envio'
    ) THEN
        ALTER TABLE orders ADD COLUMN direccion_envio TEXT COMMENT 'Dirección completa de envío';
    END IF;
    
    -- Verificar y agregar notas
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = 'Bike_Store' 
        AND TABLE_NAME = 'orders' 
        AND COLUMN_NAME = 'notas'
    ) THEN
        ALTER TABLE orders ADD COLUMN notas TEXT COMMENT 'Notas del cliente sobre el pedido';
    END IF;
END //

DELIMITER ;

-- Ejecutar el procedimiento
CALL agregar_campos_fase5();

-- Eliminar el procedimiento después de usarlo
DROP PROCEDURE IF EXISTS agregar_campos_fase5;

-- Verificar los campos agregados
DESCRIBE orders;

-- Ejemplo de consulta para ver los métodos de pago disponibles
SELECT * FROM metodos_pago WHERE activo = 1;

-- Mensaje de confirmación
SELECT 'Base de datos actualizada exitosamente para FASE 5' AS mensaje;
