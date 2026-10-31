-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-10-2026 a las 18:51:27
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sherwin_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `telefono`, `email`, `direccion`) VALUES
(1, 'Marcelo', '1122531062', '', ''),
(2, 'Tincho', '112256336', NULL, NULL),
(3, 'Mili', '1150285665', 'mily.alarco@gmail.com', 'rio de janeiro 1251'),
(4, 'Juan', '1135336704', 'juang@gmail.com', 'Juan b Justo 4445');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entregas`
--

CREATE TABLE `entregas` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `observaciones` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `entregas`
--

INSERT INTO `entregas` (`id`, `pedido_id`, `fecha`, `observaciones`) VALUES
(1, 4, '2026-05-06', 'Pedido entregado correctamente'),
(2, 1, '2026-05-06', 'Pedido entregado correctamente'),
(3, 6, '2026-09-09', 'Pedido entregado correctamente'),
(4, 7, '2026-09-09', 'Pedido despachado y entregado correctamente'),
(5, 8, '2026-09-09', 'Pedido despachado y entregado correctamente'),
(6, 12, '2026-09-09', 'Pedido despachado y entregado correctamente'),
(7, 16, '2026-09-09', 'Pedido despachado y entregado correctamente'),
(8, 15, '2026-09-09', 'Pedido despachado y entregado correctamente'),
(9, 17, '2026-09-30', 'Pedido despachado y entregado correctamente'),
(10, 18, '2026-09-30', 'Pedido despachado y entregado correctamente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id`, `pedido_id`, `fecha`, `total`) VALUES
(1, 1, '2026-05-06', 50000.00),
(2, 2, '2026-05-06', 25000.00),
(3, 4, '2026-05-06', 10000.00),
(4, 6, '2026-09-09', 42500.00),
(5, 7, '2026-09-09', 85000.00),
(6, 8, '2026-09-09', 25000.00),
(7, 11, '2026-09-09', 17000.00),
(8, 12, '2026-09-09', 12000.00),
(9, 15, '2026-09-09', 300000.00),
(10, 16, '2026-09-09', 60000.00),
(11, 17, '2026-09-30', 850000.00),
(12, 18, '2026-09-30', 150000.00),
(13, 19, '2026-10-26', 900000.00),
(14, 21, '2026-10-26', 30000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias_primas`
--

CREATE TABLE `materias_primas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `unidad_medida` varchar(50) DEFAULT NULL,
  `stock` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias_primas`
--

INSERT INTO `materias_primas` (`id`, `nombre`, `unidad_medida`, `stock`) VALUES
(11, 'Resina Acrílica Base', 'Litros', 0.00),
(12, 'Dióxido de Titanio (Blanco)', 'Kg', 5375.60),
(13, 'Carbonato de Calcio (Carga)', 'Kg', 714.00),
(14, 'Agua Tratada', 'Litros', 5600.00),
(15, 'Aditivo Antihongos', 'Gramos', 5400.00),
(16, 'Envase de Plástico (20 Litros)', 'Unidades', 4410.00),
(17, 'Envase de Hojalata (4 Litros)', 'Unidades', 4971.00),
(18, 'Tapas', 'Unidades', 4281.00),
(19, 'Etiquetas Impresas', 'Unidades', 4281.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_materias`
--

CREATE TABLE `movimientos_materias` (
  `id` int(11) NOT NULL,
  `materia_id` int(11) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `motivo` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_materias`
--

INSERT INTO `movimientos_materias` (`id`, `materia_id`, `tipo`, `cantidad`, `fecha`, `motivo`) VALUES
(9, 11, 'ingreso', 50.00, '2026-09-09', 'Stock inicial'),
(10, 12, 'ingreso', 20.00, '2026-09-09', 'Stock inicial'),
(11, 13, 'ingreso', 100.00, '2026-09-09', 'Stock inicial'),
(12, 14, 'ingreso', 100.00, '2026-09-09', 'Stock inicial'),
(13, 15, 'ingreso', 500.00, '2026-09-09', 'Stock inicial'),
(14, 16, 'ingreso', 10.00, '2026-09-09', 'Stock inicial'),
(15, 17, 'ingreso', 10.00, '2026-09-09', 'Stock inicial'),
(16, 18, 'ingreso', 20.00, '2026-09-09', 'Stock inicial'),
(17, 19, 'ingreso', 20.00, '2026-09-09', 'Stock inicial'),
(24, 11, 'egreso', 12.50, '2026-09-09', 'Consumo para producción'),
(25, 12, 'egreso', 4.00, '2026-09-09', 'Consumo para producción'),
(26, 17, 'egreso', 5.00, '2026-09-09', 'Consumo para producción'),
(27, 18, 'egreso', 5.00, '2026-09-09', 'Consumo para producción'),
(28, 19, 'egreso', 5.00, '2026-09-09', 'Consumo para producción'),
(29, 17, 'ingreso', 100.00, '2026-09-09', 'Compra a proveedor'),
(30, 11, 'egreso', 25.00, '2026-09-09', 'Consumo para producción'),
(31, 12, 'egreso', 8.00, '2026-09-09', 'Consumo para producción'),
(32, 17, 'egreso', 10.00, '2026-09-09', 'Consumo para producción'),
(33, 18, 'egreso', 10.00, '2026-09-09', 'Consumo para producción'),
(34, 19, 'egreso', 10.00, '2026-09-09', 'Consumo para producción'),
(35, 11, 'egreso', 5.00, '2026-09-09', 'Consumo para producción'),
(36, 12, 'egreso', 2.00, '2026-09-09', 'Consumo para producción'),
(37, 13, 'egreso', 10.00, '2026-09-09', 'Consumo para producción'),
(38, 16, 'egreso', 1.00, '2026-09-09', 'Consumo para producción'),
(39, 18, 'egreso', 1.00, '2026-09-09', 'Consumo para producción'),
(40, 19, 'egreso', 1.00, '2026-09-09', 'Consumo para producción'),
(41, 11, 'egreso', 5.00, '2026-09-09', 'Consumo para producción'),
(42, 12, 'egreso', 1.60, '2026-09-09', 'Consumo para producción'),
(43, 17, 'egreso', 2.00, '2026-09-09', 'Consumo para producción'),
(44, 18, 'egreso', 2.00, '2026-09-09', 'Consumo para producción'),
(45, 19, 'egreso', 2.00, '2026-09-09', 'Consumo para producción'),
(46, 11, 'egreso', 2.00, '2026-09-09', 'Consumo para producción'),
(47, 12, 'egreso', 0.80, '2026-09-09', 'Consumo para producción'),
(48, 13, 'egreso', 4.00, '2026-09-09', 'Consumo para producción'),
(49, 17, 'egreso', 2.00, '2026-09-09', 'Consumo para producción'),
(50, 18, 'egreso', 2.00, '2026-09-09', 'Consumo para producción'),
(51, 19, 'egreso', 2.00, '2026-09-09', 'Consumo para producción'),
(52, 14, 'ingreso', 500.00, '2026-09-09', 'Compra a proveedor'),
(53, 13, 'ingreso', 500.00, '2026-09-09', 'Ingreso manual de stock'),
(54, 12, 'ingreso', 500.00, '2026-09-09', 'Ingreso manual de stock'),
(55, 15, 'egreso', 10.00, '2026-09-09', 'Egreso manual de stock'),
(56, 15, 'egreso', 20.00, '2026-09-09', 'Egreso manual de stock'),
(57, 15, 'ingreso', 30.00, '2026-09-09', 'Ingreso manual de stock'),
(58, 15, 'egreso', 100.00, '2026-09-09', 'Egreso manual de stock'),
(59, 17, 'ingreso', 5000.00, '2026-09-09', 'Ingreso manual de stock'),
(60, 16, 'ingreso', 5000.00, '2026-09-09', 'Ingreso manual de stock'),
(61, 19, 'ingreso', 5000.00, '2026-09-09', 'Ingreso manual de stock'),
(62, 11, 'ingreso', 5000.00, '2026-09-09', 'Ingreso manual de stock'),
(63, 18, 'ingreso', 5000.00, '2026-09-09', 'Ingreso manual de stock'),
(64, 11, 'egreso', 250.00, '2026-09-09', 'Consumo para producción'),
(65, 12, 'egreso', 80.00, '2026-09-09', 'Consumo para producción'),
(66, 17, 'egreso', 100.00, '2026-09-09', 'Consumo para producción'),
(67, 18, 'egreso', 100.00, '2026-09-09', 'Consumo para producción'),
(68, 19, 'egreso', 100.00, '2026-09-09', 'Consumo para producción'),
(69, 11, 'egreso', 160.00, '2026-09-09', 'Consumo para producción'),
(70, 13, 'egreso', 160.00, '2026-09-09', 'Consumo para producción'),
(71, 16, 'egreso', 20.00, '2026-09-09', 'Consumo para producción'),
(72, 18, 'egreso', 20.00, '2026-09-09', 'Consumo para producción'),
(73, 19, 'egreso', 20.00, '2026-09-09', 'Consumo para producción'),
(74, 11, 'egreso', 100.00, '2026-09-09', 'Consumo para producción'),
(75, 12, 'egreso', 40.00, '2026-09-09', 'Consumo para producción'),
(76, 13, 'egreso', 200.00, '2026-09-09', 'Consumo para producción'),
(77, 16, 'egreso', 20.00, '2026-09-09', 'Consumo para producción'),
(78, 18, 'egreso', 20.00, '2026-09-09', 'Consumo para producción'),
(79, 19, 'egreso', 20.00, '2026-09-09', 'Consumo para producción'),
(80, 11, 'egreso', 20.00, '2026-09-09', 'Consumo para producción'),
(81, 12, 'egreso', 8.00, '2026-09-09', 'Consumo para producción'),
(82, 13, 'egreso', 40.00, '2026-09-09', 'Consumo para producción'),
(83, 17, 'egreso', 20.00, '2026-09-09', 'Consumo para producción'),
(84, 18, 'egreso', 20.00, '2026-09-09', 'Consumo para producción'),
(85, 19, 'egreso', 20.00, '2026-09-09', 'Consumo para producción'),
(86, 11, 'egreso', 184.00, '2026-10-26', 'Consumo para producción'),
(87, 13, 'egreso', 184.00, '2026-10-26', 'Consumo para producción'),
(88, 16, 'egreso', 23.00, '2026-10-26', 'Consumo para producción'),
(89, 18, 'egreso', 23.00, '2026-10-26', 'Consumo para producción'),
(90, 19, 'egreso', 23.00, '2026-10-26', 'Consumo para producción'),
(91, 15, 'ingreso', 5000.00, '2026-10-26', 'Ingreso manual de stock'),
(92, 14, 'ingreso', 5000.00, '2026-10-26', 'Ingreso manual de stock'),
(93, 13, 'ingreso', 5000.00, '2026-10-26', 'Ingreso manual de stock'),
(94, 12, 'ingreso', 5000.00, '2026-10-26', 'Ingreso manual de stock'),
(95, 11, 'egreso', 4280.00, '2026-10-26', 'Consumo para producción'),
(96, 13, 'egreso', 4280.00, '2026-10-26', 'Consumo para producción'),
(97, 16, 'egreso', 535.00, '2026-10-26', 'Consumo para producción'),
(98, 18, 'egreso', 535.00, '2026-10-26', 'Consumo para producción'),
(99, 19, 'egreso', 535.00, '2026-10-26', 'Consumo para producción'),
(100, 11, 'ingreso', 1.50, '2026-10-26', 'Ingreso manual de stock'),
(101, 11, 'egreso', 8.00, '2026-10-26', 'Consumo para producción'),
(102, 13, 'egreso', 8.00, '2026-10-26', 'Consumo para producción'),
(103, 16, 'egreso', 1.00, '2026-10-26', 'Consumo para producción'),
(104, 18, 'egreso', 1.00, '2026-10-26', 'Consumo para producción'),
(105, 19, 'egreso', 1.00, '2026-10-26', 'Consumo para producción');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_stock`
--

CREATE TABLE `movimientos_stock` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `motivo` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_stock`
--

INSERT INTO `movimientos_stock` (`id`, `producto_id`, `tipo`, `cantidad`, `fecha`, `motivo`) VALUES
(1, 1, 'egreso', 10, '2026-05-06', 'Venta facturada'),
(2, 1, 'egreso', 5, '2026-05-06', 'Venta facturada'),
(3, 1, 'egreso', 2, '2026-05-06', 'Venta facturada'),
(4, 4, 'ingreso', 5, '2026-09-09', 'Fabricación / Producción interna'),
(5, 4, 'egreso', 5, '2026-09-09', 'Venta facturada'),
(6, 4, 'ingreso', 10, '2026-09-09', 'Fabricación / Producción interna'),
(7, 4, 'egreso', 10, '2026-09-09', 'Venta facturada'),
(8, 2, 'ingreso', 1, '2026-09-09', 'Fabricación / Producción interna'),
(9, 2, 'egreso', 1, '2026-09-09', 'Venta facturada'),
(10, 4, 'ingreso', 2, '2026-09-09', 'Fabricación / Producción interna'),
(11, 4, 'egreso', 2, '2026-09-09', 'Venta facturada'),
(12, 3, 'ingreso', 2, '2026-09-09', 'Fabricación / Producción interna'),
(13, 3, 'egreso', 2, '2026-09-09', 'Venta facturada'),
(14, 4, 'ingreso', 100, '2026-09-09', 'Fabricación / Producción interna'),
(15, 5, 'ingreso', 20, '2026-09-09', 'Fabricación / Producción interna'),
(16, 2, 'ingreso', 20, '2026-09-09', 'Fabricación / Producción interna'),
(17, 3, 'ingreso', 20, '2026-09-09', 'Fabricación / Producción interna'),
(18, 5, 'egreso', 10, '2026-09-09', 'Venta facturada'),
(19, 3, 'egreso', 10, '2026-09-09', 'Venta facturada'),
(20, 4, 'egreso', 100, '2026-09-30', 'Venta facturada'),
(21, 5, 'egreso', 5, '2026-09-30', 'Venta facturada'),
(22, 5, 'ingreso', 23, '2026-10-26', 'Fabricación / Producción interna'),
(23, 5, 'ingreso', 535, '2026-10-26', 'Fabricación / Producción interna'),
(24, 5, 'ingreso', 1, '2026-10-26', 'Fabricación / Producción interna'),
(25, 5, 'egreso', 30, '2026-10-26', 'Venta facturada'),
(26, 3, 'egreso', 5, '2026-10-26', 'Venta facturada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `cliente_id`, `fecha`, `estado`) VALUES
(1, 1, '2026-05-16', 'anulado'),
(2, 2, '2026-05-13', 'facturado'),
(4, 2, '2026-05-06', 'entregado'),
(6, 3, '2026-09-09', 'entregado'),
(7, 2, '2026-09-09', 'entregado'),
(8, 2, '2026-09-09', 'entregado'),
(11, 1, '2026-09-09', 'facturado'),
(12, 3, '2026-09-09', 'entregado'),
(14, 3, '2026-09-09', 'anulado'),
(15, 2, '2026-09-09', 'entregado'),
(16, 1, '2026-09-09', 'entregado'),
(17, 1, '2026-09-09', 'entregado'),
(18, 1, '2026-09-30', 'entregado'),
(19, 4, '2026-10-26', 'facturado'),
(20, 4, '2026-10-26', 'anulado'),
(21, 4, '2026-10-26', 'facturado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_detalle`
--

CREATE TABLE `pedido_detalle` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_detalle`
--

INSERT INTO `pedido_detalle` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`) VALUES
(1, 1, 1, 10, 5000.00),
(6, 6, 4, 5, 8500.00),
(7, 7, 4, 10, 8500.00),
(14, 14, 5, 5, 30000.00),
(15, 15, 5, 10, 30000.00),
(16, 16, 3, 10, 6000.00),
(17, 17, 4, 100, 8500.00),
(18, 18, 5, 5, 30000.00),
(19, 19, 5, 30, 30000.00),
(20, 20, 2, 10, 25000.00),
(21, 21, 3, 5, 6000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `descripcion` varchar(150) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `stock`, `categoria`) VALUES
(2, 'Látex Interior Blanco (Balde 20L)', 'Balde de 20 Litros de pintura látex para interiores', 25000.00, 20, 'Látex'),
(3, 'Látex Interior Blanco (Lata 4L)', 'Lata de 4 Litros de pintura látex para interiores', 6000.00, 5, 'Látex'),
(4, 'Esmalte Sintético Brillante (Lata 4L)', 'Esmalte sintético brillante para metales y maderas', 8500.00, 0, 'Sintéticos'),
(5, 'Impermeabilizante para Techos (Balde 20L)', 'Recubrimiento elástico impermeabilizante para losas y techos', 30000.00, 534, 'Impermeabilizantes');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_terminados`
--

CREATE TABLE `productos_terminados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `presentacion` varchar(100) NOT NULL,
  `stock` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos_terminados`
--

INSERT INTO `productos_terminados` (`id`, `nombre`, `presentacion`, `stock`) VALUES
(2, 'Látex Interior Blanco', 'Balde 20 Litros', 0.00),
(3, 'Látex Interior Blanco', 'Lata 4 Litros', 0.00),
(4, 'Esmalte Sintético Brillante', 'Lata 4 Litros', 0.00),
(5, 'Impermeabilizante para Techos', 'Balde 20 Litros', 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recetas`
--

CREATE TABLE `recetas` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `materia_id` int(11) NOT NULL,
  `cantidad_necesaria` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recetas`
--

INSERT INTO `recetas` (`id`, `producto_id`, `materia_id`, `cantidad_necesaria`) VALUES
(13, 2, 11, 5.00),
(14, 2, 12, 2.00),
(15, 2, 13, 10.00),
(16, 2, 16, 1.00),
(17, 2, 18, 1.00),
(18, 2, 19, 1.00),
(19, 3, 11, 1.00),
(20, 3, 12, 0.40),
(21, 3, 13, 2.00),
(22, 3, 17, 1.00),
(23, 3, 18, 1.00),
(24, 3, 19, 1.00),
(25, 4, 11, 2.50),
(26, 4, 12, 0.80),
(27, 4, 17, 1.00),
(28, 4, 18, 1.00),
(29, 4, 19, 1.00),
(30, 5, 11, 8.00),
(31, 5, 13, 8.00),
(32, 5, 16, 1.00),
(33, 5, 18, 1.00),
(34, 5, 19, 1.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`);

--
-- Indices de la tabla `materias_primas`
--
ALTER TABLE `materias_primas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `movimientos_materias`
--
ALTER TABLE `movimientos_materias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `materia_id` (`materia_id`);

--
-- Indices de la tabla `movimientos_stock`
--
ALTER TABLE `movimientos_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos_terminados`
--
ALTER TABLE `productos_terminados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `materia_id` (`materia_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `entregas`
--
ALTER TABLE `entregas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `materias_primas`
--
ALTER TABLE `materias_primas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `movimientos_materias`
--
ALTER TABLE `movimientos_materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT de la tabla `movimientos_stock`
--
ALTER TABLE `movimientos_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `productos_terminados`
--
ALTER TABLE `productos_terminados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `recetas`
--
ALTER TABLE `recetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `entregas`
--
ALTER TABLE `entregas`
  ADD CONSTRAINT `entregas_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`);

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`);

--
-- Filtros para la tabla `movimientos_materias`
--
ALTER TABLE `movimientos_materias`
  ADD CONSTRAINT `movimientos_materias_ibfk_1` FOREIGN KEY (`materia_id`) REFERENCES `materias_primas` (`id`);

--
-- Filtros para la tabla `movimientos_stock`
--
ALTER TABLE `movimientos_stock`
  ADD CONSTRAINT `movimientos_stock_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  ADD CONSTRAINT `pedido_detalle_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `pedido_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD CONSTRAINT `recetas_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos_terminados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recetas_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias_primas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
