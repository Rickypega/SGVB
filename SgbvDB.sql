-- ============================================================================
-- Sistema de Gestión de Bibliotecas Virtuales (SGBV)
-- Estructura de Base de Datos Relacional y Datos Iniciales (Seeders)
-- Motor: InnoDB | Codificación: UTF8MB4
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `sgbv_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sgbv_db`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `carrito_items`;
DROP TABLE IF EXISTS `suscripciones_recursos`;
DROP TABLE IF EXISTS `prestamos`;
DROP TABLE IF EXISTS `recursos`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `rol_permiso`;
DROP TABLE IF EXISTS `permisos`;
DROP TABLE IF EXISTS `roles`;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- Tabla: roles
-- ----------------------------------------------------------------------------
CREATE TABLE `roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla: permisos
-- ----------------------------------------------------------------------------
CREATE TABLE `permisos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla: rol_permiso
-- ----------------------------------------------------------------------------
CREATE TABLE `rol_permiso` (
  `rol_id` INT NOT NULL,
  `permiso_id` INT NOT NULL,
  PRIMARY KEY (`rol_id`, `permiso_id`),
  CONSTRAINT `fk_rol_permiso_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rol_permiso_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla: usuarios
-- ----------------------------------------------------------------------------
CREATE TABLE `usuarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `correo` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `cedula` VARCHAR(255) NOT NULL UNIQUE,
  `fecha_nacimiento` DATE NOT NULL,
  `rol_id` INT NOT NULL,
  `cedula_verificada` TINYINT(1) NOT NULL DEFAULT 0,
  `correo_verificado` TINYINT(1) NOT NULL DEFAULT 0,
  `saldo` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla: categorias
-- ----------------------------------------------------------------------------
CREATE TABLE `categorias` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla: recursos
-- ----------------------------------------------------------------------------
CREATE TABLE `recursos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `autor` VARCHAR(150) NOT NULL,
  `isbn` VARCHAR(50) NOT NULL UNIQUE,
  `categoria_id` INT NOT NULL,
  `anio_publicacion` INT NOT NULL,
  `tipo` ENUM('libro', 'audiolibro', 'articulo') NOT NULL DEFAULT 'libro',
  `disponibilidad` INT NOT NULL DEFAULT 1,
  `precio_renta` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `portada` VARCHAR(255) NOT NULL DEFAULT 'default_cover.jpg',
  `archivo_pdf` VARCHAR(255) NULL DEFAULT NULL,
  `descripcion` TEXT NULL,
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_recursos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla: suscripciones_recursos (Notificación cuando recurso agotado esté disponible)
-- ----------------------------------------------------------------------------
CREATE TABLE `suscripciones_recursos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `recurso_id` INT NOT NULL,
  `fecha_suscripcion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` ENUM('pendiente', 'notificado') NOT NULL DEFAULT 'pendiente',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuario_recurso` (`usuario_id`, `recurso_id`),
  CONSTRAINT `fk_suscripciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_suscripciones_recurso` FOREIGN KEY (`recurso_id`) REFERENCES `recursos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla: prestamos
-- ----------------------------------------------------------------------------
CREATE TABLE `prestamos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `recurso_id` INT NOT NULL,
  `fecha_prestamo` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_devolucion_limite` DATETIME NOT NULL,
  `fecha_devolucion_real` DATETIME NULL DEFAULT NULL,
  `monto_pagado` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `ha_leido` TINYINT(1) NOT NULL DEFAULT 0,
  `estado` ENUM('reservado', 'activo', 'devuelto') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_prestamos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_prestamos_recurso` FOREIGN KEY (`recurso_id`) REFERENCES `recursos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla: carrito_items (Carrito con Memoria Persistente en Base de Datos)
-- ----------------------------------------------------------------------------
CREATE TABLE `carrito_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `recurso_id` INT NOT NULL,
  `fecha_agregado` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_carrito_user_resource` (`usuario_id`, `recurso_id`),
  CONSTRAINT `fk_carrito_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_carrito_recurso` FOREIGN KEY (`recurso_id`) REFERENCES `recursos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DATOS INICIALES (SEEDERS)
-- ============================================================================

-- Roles iniciales
INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Control total de la plataforma, inventario de recursos y analíticas de usuarios.'),
(2, 'Lector Estándar', 'Usuario con acceso al catálogo para consultar y rentar recursos digitales.');

-- Permisos iniciales
INSERT INTO `permisos` (`id`, `nombre`, `descripcion`) VALUES
(1, 'gestionar_recursos', 'Crear, editar y eliminar libros, audiolibros y artículos'),
(2, 'ver_analiticas', 'Acceso al dashboard de ganancias, edad promedio y métricas'),
(3, 'rentar_recursos', 'Permiso para solicitar préstamos de recursos'),
(4, 'ver_mis_prestamos', 'Consultar préstamos activos y devoluciones propias');

-- Relación Rol - Permiso
INSERT INTO `rol_permiso` (`rol_id`, `permiso_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4),
(2, 3), (2, 4);

-- Categorías literarias
INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'Ciencia Ficción'),
(2, 'Tecnología y Programación'),
(3, 'Novela Histórica'),
(4, 'Desarrollo Personal'),
(5, 'Biografía'),
(6, 'Inteligencia Artificial'),
(7, 'Arte y Diseño');

-- Usuarios iniciales
-- Contraseña admin123 -> $2y$10$qSQmrzKtjVzOCN4HVZ690OpJy/gjn9mPlfB87W.DzHtD3WiyPoc.2
-- Contraseña lector123 -> $2y$10$lOrgedDrQ3HdiMvhp4OxneS4/j7TUU7q9e1ydE.YuOBL7KJi1kooq
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `password`, `cedula`, `fecha_nacimiento`, `rol_id`, `cedula_verificada`, `correo_verificado`, `saldo`) VALUES
(1, 'Administrador General', 'admin@sgbv.com', '$2y$10$qSQmrzKtjVzOCN4HVZ690OpJy/gjn9mPlfB87W.DzHtD3WiyPoc.2', '001-1234567-1', '1985-06-15', 1, 1, 1, 1000.00),
(2, 'Roberto Gómez Lector', 'lector@sgbv.com', '$2y$10$lOrgedDrQ3HdiMvhp4OxneS4/j7TUU7q9e1ydE.YuOBL7KJi1kooq', '001-9876543-2', '1998-03-20', 2, 1, 1, 50.00),
(3, 'Ana Lucía Martínez', 'ana@sgbv.com', '$2y$10$lOrgedDrQ3HdiMvhp4OxneS4/j7TUU7q9e1ydE.YuOBL7KJi1kooq', '002-3344556-3', '2001-11-10', 2, 1, 1, 25.50),
(4, 'Carlos Eduardo Pérez', 'carlos@sgbv.com', '$2y$10$lOrgedDrQ3HdiMvhp4OxneS4/j7TUU7q9e1ydE.YuOBL7KJi1kooq', '001-5566778-4', '1975-01-25', 2, 1, 1, 15.00),
(5, 'Elena Sofía (Menor de Edad)', 'elena@sgbv.com', '$2y$10$lOrgedDrQ3HdiMvhp4OxneS4/j7TUU7q9e1ydE.YuOBL7KJi1kooq', '003-1122334-5', '2010-08-14', 2, 0, 1, 10.00);

-- Recursos digitales
INSERT INTO `recursos` (`id`, `titulo`, `autor`, `isbn`, `categoria_id`, `anio_publicacion`, `tipo`, `disponibilidad`, `precio_renta`, `portada`, `descripcion`) VALUES
(1, 'Clean Code: A Handbook of Agile Software Craftsmanship', 'Robert C. Martin', '978-0132350884', 2, 2008, 'libro', 5, 4.50, 'cleancode.jpg', 'Una guía indispensable para escribir código limpio, escalable y mantenible en cualquier lenguaje.'),
(2, 'Dune', 'Frank Herbert', '978-0441172719', 1, 1965, 'libro', 3, 3.50, 'dune.jpg', 'La épica historia de Paul Atreides en el planeta desértico Arrakis, una obra maestra de la ciencia ficción.'),
(3, 'Sapiens: De animales a dioses', 'Yuval Noah Harari', '978-8499926223', 3, 2011, 'audiolibro', 8, 5.00, 'sapiens.jpg', 'Un recorrido fascinante por la evolución de la humanidad desde la Edad de Piedra hasta la era digital.'),
(4, 'El Arte de la Guerra', 'Sun Tzu', '978-8420691206', 4, 2015, 'libro', 10, 2.00, 'arteguerra.jpg', 'Tratado milenario sobre estrategia militar aplicada hoy a la vida y los negocios.'),
(5, 'Arquitectura Limpia (Clean Architecture)', 'Robert C. Martin', '978-0134494166', 2, 2017, 'libro', 4, 4.99, 'cleanarch.jpg', 'Guía para el diseño de software profesional con independencia de frameworks y bases de datos.'),
(6, 'Inteligencia Artificial Avanzada y Agentes', 'Stuart Russell', '978-8420540030', 6, 2021, 'articulo', 15, 1.99, 'iaagentes.jpg', 'Análisis profundo sobre agentes inteligentes, razonamiento automatizado y aprendizaje profundo.'),
(7, '1984', 'George Orwell', '978-0451524935', 1, 1949, 'libro', 2, 3.00, '1984.jpg', 'Novela distópica clásica sobre la vigilancia del Gran Hermano y el control totalitario.');

-- Préstamos históricos y activos de prueba
INSERT INTO `prestamos` (`id`, `usuario_id`, `recurso_id`, `fecha_prestamo`, `fecha_devolucion_limite`, `fecha_devolucion_real`, `monto_pagado`, `ha_leido`, `estado`) VALUES
(1, 2, 1, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_ADD(DATE_SUB(NOW(), INTERVAL 5 DAY), INTERVAL 14 DAY), NULL, 4.50, 1, 'activo'),
(2, 2, 2, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY), 3.50, 1, 'devuelto'),
(3, 3, 3, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 16 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY), 5.00, 1, 'devuelto'),
(4, 4, 5, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_ADD(DATE_SUB(NOW(), INTERVAL 2 DAY), INTERVAL 14 DAY), NULL, 4.99, 0, 'activo'),
(5, 3, 4, DATE_SUB(NOW(), INTERVAL 40 DAY), DATE_SUB(NOW(), INTERVAL 26 DAY), DATE_SUB(NOW(), INTERVAL 28 DAY), 2.00, 1, 'devuelto');
