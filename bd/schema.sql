-- ============================================================================
-- SCRIPT DE BASE DE DATOS: Atelier Élie
-- Proyecto: Tienda Web Dinámica
-- Motor: MySQL 8.0+
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `mydatabase` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mydatabase`;

-- Desactivar restricciones temporalmente para permitir un despliegue limpio
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `FAVORITO`;
DROP TABLE IF EXISTS `DETALLE_PEDIDO`;
DROP TABLE IF EXISTS `PEDIDO`;
DROP TABLE IF EXISTS `DIRECCION`;
DROP TABLE IF EXISTS `CORBATA`;
DROP TABLE IF EXISTS `USUARIO`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 1. TABLA: USUARIO
-- ============================================================================
CREATE TABLE `USUARIO` (
  `id_user` INT AUTO_INCREMENT PRIMARY KEY,
  `nombres` VARCHAR(100) NOT NULL,
  `apellidos` VARCHAR(100) NOT NULL,
  `dni` VARCHAR(20) NOT NULL UNIQUE,
  `correo` VARCHAR(150) NOT NULL UNIQUE,
  `telefono` VARCHAR(20) NOT NULL,
  `contrasenia` VARCHAR(255) NOT NULL,
  `intentos_fallidos` INT DEFAULT 0,
  `bloqueado_hasta` DATETIME NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. TABLA: DIRECCION
-- ============================================================================
CREATE TABLE `DIRECCION` (
  `id_direccion` INT AUTO_INCREMENT PRIMARY KEY,
  `id_user` INT NOT NULL,
  `calle` VARCHAR(255) NOT NULL,
  `ciudad` VARCHAR(100) NOT NULL,
  `codigo_postal` VARCHAR(10) NOT NULL,
  CONSTRAINT `fk_direccion_usuario` FOREIGN KEY (`id_user`) REFERENCES `USUARIO` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. TABLA: CORBATA
-- ============================================================================
CREATE TABLE `CORBATA` (
  `id_corbata` INT AUTO_INCREMENT PRIMARY KEY,
  `talla` VARCHAR(50) NOT NULL,
  `color` VARCHAR(50) NOT NULL,
  `material` VARCHAR(50) NOT NULL,
  `marca` VARCHAR(100) NOT NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `imagen` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. TABLA: PEDIDO
-- ============================================================================
CREATE TABLE `PEDIDO` (
  `id_pedido` INT AUTO_INCREMENT PRIMARY KEY,
  `id_user` INT NOT NULL,
  `id_direccion` INT NOT NULL,
  `fecha_pedido` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `total` DECIMAL(10,2) NOT NULL,
  `estado` VARCHAR(50) DEFAULT 'Completado',
  CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`id_user`) REFERENCES `USUARIO` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_direccion` FOREIGN KEY (`id_direccion`) REFERENCES `DIRECCION` (`id_direccion`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. TABLA: DETALLE_PEDIDO
-- ============================================================================
CREATE TABLE `DETALLE_PEDIDO` (
  `id_pedido` INT NOT NULL,
  `id_corbata` INT NOT NULL,
  `cantidad` INT NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id_pedido`, `id_corbata`),
  CONSTRAINT `fk_detalle_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `PEDIDO` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_corbata` FOREIGN KEY (`id_corbata`) REFERENCES `CORBATA` (`id_corbata`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. TABLA: FAVORITO
-- ============================================================================
CREATE TABLE `FAVORITO` (
  `id_user` INT NOT NULL,
  `id_corbata` INT NOT NULL,
  PRIMARY KEY (`id_user`, `id_corbata`),
  CONSTRAINT `fk_favorito_usuario` FOREIGN KEY (`id_user`) REFERENCES `USUARIO` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favorito_corbata` FOREIGN KEY (`id_corbata`) REFERENCES `CORBATA` (`id_corbata`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- POBLADO DE DATOS INICIALES (DATOS DE PRUEBA)
-- ============================================================================

-- Contraseña por defecto para usuarios de prueba: "123456" (Hash BCRYPT)
-- Hash: $2y$10$e0MYzXyjpJS7Pd0RVvHwHe1h.f7A1VbU8N2Nl3a9x3A2W2c9z5X.i

INSERT INTO `USUARIO` (`id_user`, `nombres`, `apellidos`, `dni`, `correo`, `telefono`, `contrasenia`) VALUES
(1, 'Geralt', 'de Rivia', '12345678A', 'geralt@kaermorhen.com', '600111222', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1h.f7A1VbU8N2Nl3a9x3A2W2c9z5X.i'),
(2, 'Prueba', 'Atelier', '87654321B', 'prueba@atelier.com', '611222333', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1h.f7A1VbU8N2Nl3a9x3A2W2c9z5X.i');

INSERT INTO `DIRECCION` (`id_direccion`, `id_user`, `calle`, `ciudad`, `codigo_postal`) VALUES
(1, 1, 'Fortaleza de Kaer Morhen, Torre Principal', 'Kaedwen', '10001'),
(2, 2, 'Plaza del Mercado, N.º 12', 'Oxenfurt', '03690');

INSERT INTO `CORBATA` (`id_corbata`, `talla`, `color`, `material`, `marca`, `precio`, `stock`, `imagen`) VALUES
(1, 'Estándar', 'Sombra de Lobo', 'Seda Jacquard', 'Escuela del Lobo', 49.99, 12, 'assets/img/lobo.webp'),
(2, 'Slim', 'Verde Víbora', 'Satin', 'Atelier Élie', 39.50, 8, 'assets/img/vibora.webp'),
(3, 'Ancha', 'Violeta Yennefer', 'Terciopelo', 'Vengerberg Haute', 59.90, 5, 'assets/img/lilas_grosellas.webp'),
(4, 'Estándar', 'Gris Plateado', 'Seda Natural', 'Atelier Élie', 44.99, 15, 'assets/img/gato.webp'),
(5, 'Slim', 'Azul Temerio', 'Lino', 'Corte de Wyzima', 35.00, 20, 'assets/img/grifo.webp'),
(6, 'Ancha', 'Rojo Redanio', 'Lana Merino', 'Oxenfurt Tailors', 52.00, 6, 'assets/img/skellige.webp');

INSERT INTO `FAVORITO` (`id_user`, `id_corbata`) VALUES
(1, 1),
(1, 3);