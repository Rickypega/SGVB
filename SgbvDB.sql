-- ============================================================================
-- Sistema de Gestión de Bibliotecas Virtuales (SGBV)
-- Estructura Limpia Adaptada a InfinityFree
-- Motor: InnoDB | Codificación: UTF8MB4
-- ============================================================================

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
-- Estructura de Tablas
-- ----------------------------------------------------------------------------
CREATE TABLE `roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permisos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rol_permiso` (
  `rol_id` INT NOT NULL,
  `permiso_id` INT NOT NULL,
  PRIMARY KEY (`rol_id`, `permiso_id`),
  CONSTRAINT `fk_rol_permiso_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rol_permiso_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `categorias` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- ESTRUCTURA INICIAL BASE (ROLES, PERMISOS Y CATEGORÍAS)
-- ============================================================================

INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Control total de la plataforma, inventario de recursos y analíticas de usuarios.'),
(2, 'Lector Estándar', 'Usuario con acceso al catálogo para consultar y rentar recursos digitales.');

INSERT INTO `permisos` (`id`, `nombre`, `descripcion`) VALUES
(1, 'gestionar_recursos', 'Crear, editar y eliminar libros, audiolibros y artículos'),
(2, 'ver_analiticas', 'Acceso al dashboard de ganancias, edad promedio y métricas'),
(3, 'rentar_recursos', 'Permiso para solicitar préstamos de recursos'),
(4, 'ver_mis_prestamos', 'Consultar préstamos activos y devoluciones propias');

INSERT INTO `rol_permiso` (`rol_id`, `permiso_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4),
(2, 3), (2, 4);

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'Ciencia Ficción'),
(2, 'Tecnología y Programación'),
(3, 'Novela Histórica'),
(4, 'Desarrollo Personal'),
(5, 'Biografía'),
(6, 'Inteligencia Artificial'),
(7, 'Arte y Diseño');