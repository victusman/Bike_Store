-- Script para crear la base de datos Bike_Store y las tablas necesarias
-- Ejecútelo en MySQL Workbench o desde la línea de comandos

CREATE DATABASE IF NOT EXISTS `Bike_Store` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `Bike_Store`;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de productos
CREATE TABLE IF NOT EXISTS `productos` (
  `product_id` INT NOT NULL AUTO_INCREMENT,
  `product_name` VARCHAR(255) NOT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `model_year` YEAR DEFAULT NULL,
  `price` DECIMAL(10,2) DEFAULT 0.00,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos de ejemplo (opcional)
INSERT INTO `productos` (`product_name`, `foto`, `model_year`, `price`) VALUES
('Bicicleta Montaña X100', NULL, 2023, 499.99),
('Bicicleta Urbana U200', NULL, 2022, 299.50);

-- Nota: por seguridad las contraseñas deben crearse desde la aplicación usando password_hash().
