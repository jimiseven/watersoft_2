-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-11-2024 a las 23:49:09
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `water_bd_1`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion_medidor`
--

CREATE TABLE `asignacion_medidor` (
  `id_asignacion` bigint(20) NOT NULL,
  `id_medidor` bigint(20) DEFAULT NULL,
  `id_socio` bigint(20) DEFAULT NULL,
  `id_zona` bigint(20) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `precio_accion` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignacion_medidor`
--

INSERT INTO `asignacion_medidor` (`id_asignacion`, `id_medidor`, `id_socio`, `id_zona`, `fecha`, `precio_accion`) VALUES
(1, 1, 1, 1, '2023-03-01', 100.00),
(2, 2, 2, 2, '2023-03-15', 150.00),
(3, 4, 1, 1, '2024-11-12', 100.00),
(4, 5, 3, 1, '2024-11-12', 1500.00),
(5, 3, 4, 2, '2024-11-12', 1500.00),
(6, 6, 4, 1, '2024-11-12', 1600.00),
(7, 7, 4, 2, '2024-11-12', 122.00),
(8, 8, 4, 1, '2024-11-12', 2333.00),
(9, 9, 5, 1, '2024-11-12', 50.00),
(10, 10, 5, 2, '2024-11-12', 1500.00),
(11, 11, 6, 1, '2024-11-12', 1200.00),
(12, 12, 7, 1, '2024-11-12', 500.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consumo`
--

CREATE TABLE `consumo` (
  `id_consumo` bigint(20) NOT NULL,
  `id_asignacion` bigint(20) DEFAULT NULL,
  `lectura_anterior` decimal(10,2) DEFAULT NULL,
  `lectura_actual` decimal(10,2) DEFAULT NULL,
  `periodo` varchar(255) DEFAULT NULL,
  `consumo` decimal(10,2) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `consumo`
--

INSERT INTO `consumo` (`id_consumo`, `id_asignacion`, `lectura_anterior`, `lectura_actual`, `periodo`, `consumo`, `observaciones`) VALUES
(1, 1, 100.00, 150.00, '2023-03', 50.00, 'Consumo normal'),
(2, 2, 200.00, 250.00, '2023-03', 50.00, 'Consumo alto'),
(6, 1, 150.00, 161.00, 'November', 11.00, 'sin obs'),
(7, 3, 0.00, 10.00, 'November', 10.00, 'sin obs'),
(8, 4, 0.00, 75.00, 'November', 75.00, 'sin obst'),
(10, 5, 0.00, 79.00, 'November', 79.00, ''),
(11, 6, 0.00, 90.00, 'November', 90.00, ''),
(12, 7, 0.00, 99.00, 'November', 99.00, ''),
(13, 8, 0.00, 78.00, 'November', 78.00, ''),
(14, 9, 0.00, 12.00, 'November', 12.00, ''),
(16, 10, 0.00, 50.00, 'November', 50.00, ''),
(19, 11, 25.00, 30.00, 'November', 5.00, ''),
(20, 12, 0.00, 5.00, 'November', 5.00, 'nuevo socio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deudas`
--

CREATE TABLE `deudas` (
  `id_deuda` bigint(20) NOT NULL,
  `id_consumo` bigint(20) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha_pago` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `deudas`
--

INSERT INTO `deudas` (`id_deuda`, `id_consumo`, `monto`, `fecha_pago`) VALUES
(1, 1, 75.00, '2023-04-01'),
(2, 2, 60.00, '2023-04-15'),
(3, 8, 90.00, '2024-11-12'),
(4, 10, 94.80, '2024-11-12'),
(5, 11, 108.00, '2024-11-12'),
(6, 12, 118.80, '2024-11-12'),
(7, 13, 93.60, '2024-11-12'),
(8, 14, 18.00, '2024-11-12'),
(9, 16, 75.00, '2024-11-12'),
(10, 19, 7.50, '2024-11-12'),
(11, 20, 7.50, '2024-11-12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medidor`
--

CREATE TABLE `medidor` (
  `id_medidor` bigint(20) NOT NULL,
  `marca` varchar(255) DEFAULT NULL,
  `modelo` varchar(255) DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `medidor`
--

INSERT INTO `medidor` (`id_medidor`, `marca`, `modelo`, `fecha`) VALUES
(1, 'MarcaA', 'ModeloX', '2023-01-01'),
(2, 'MarcaB', 'ModeloY', '2023-02-01'),
(3, 'lg engines', '5643TOMIL', '2024-11-13'),
(4, 'LG', 'MARS1', '2024-11-14'),
(5, 'moon', 'mars1', '2024-11-13'),
(6, 'sony', 'mards1', '2024-11-06'),
(7, 'as', 'as', '2024-11-20'),
(8, 'ass', '234s', '2024-11-07'),
(9, 'electro', '12345', '2024-11-12'),
(10, 'mars', 'mons', '2024-11-06'),
(11, 'perini', 'perini1', '2024-11-06'),
(12, 'samsum', '2022', '2024-02-02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socio`
--

CREATE TABLE `socio` (
  `id_socio` bigint(20) NOT NULL,
  `ci` varchar(255) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `apellido` varchar(255) DEFAULT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `socio`
--

INSERT INTO `socio` (`id_socio`, `ci`, `nombre`, `apellido`, `telefono`, `fecha_registro`) VALUES
(1, '77771111', 'Juan Jael', 'Pérez', '555-1234', '2023-01-15'),
(2, '87654321', 'Ana', 'Gómez', '555-5678', '2023-02-20'),
(3, '65498754', 'Jimi', 'Torrico', '76992490', '2024-11-01'),
(4, '32452345', 'Camila ', 'Torrico', '76888888', '2024-10-28'),
(5, '13035706', 'gino ', 'torrico', '454545454', '2024-11-12'),
(6, '23424', 'pedro', 'peredo', '76884444', '2024-09-04'),
(7, '3739743', 'sandra', 'peredo', '79350801', '2024-03-02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas`
--

CREATE TABLE `tarifas` (
  `id_tarifa` bigint(20) NOT NULL,
  `rango_inicio` decimal(10,2) DEFAULT NULL,
  `rango_fin` decimal(10,2) DEFAULT NULL,
  `costo_unitario` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tarifas`
--

INSERT INTO `tarifas` (`id_tarifa`, `rango_inicio`, `rango_fin`, `costo_unitario`) VALUES
(1, 0.00, 50.00, 1.50),
(2, 51.00, 100.00, 1.20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_sis`
--

CREATE TABLE `usuario_sis` (
  `id_usuario` bigint(20) NOT NULL,
  `tipo` varchar(255) DEFAULT NULL,
  `usuario` varchar(255) DEFAULT NULL,
  `contraseña` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario_sis`
--

INSERT INTO `usuario_sis` (`id_usuario`, `tipo`, `usuario`, `contraseña`) VALUES
(1, 'admin', 'admin_user', 'admin_pass'),
(2, 'user', 'regular_user', 'user_pass');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zona`
--

CREATE TABLE `zona` (
  `id_zona` bigint(20) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `zona`
--

INSERT INTO `zona` (`id_zona`, `nombre`) VALUES
(1, 'Zona Norte'),
(2, 'Zona Sur');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignacion_medidor`
--
ALTER TABLE `asignacion_medidor`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD UNIQUE KEY `unique_medidor_assignment` (`id_medidor`),
  ADD KEY `id_socio` (`id_socio`),
  ADD KEY `id_zona` (`id_zona`);

--
-- Indices de la tabla `consumo`
--
ALTER TABLE `consumo`
  ADD PRIMARY KEY (`id_consumo`),
  ADD UNIQUE KEY `unique_consumo_per_month` (`id_asignacion`,`periodo`);

--
-- Indices de la tabla `deudas`
--
ALTER TABLE `deudas`
  ADD PRIMARY KEY (`id_deuda`),
  ADD KEY `id_consumo` (`id_consumo`);

--
-- Indices de la tabla `medidor`
--
ALTER TABLE `medidor`
  ADD PRIMARY KEY (`id_medidor`);

--
-- Indices de la tabla `socio`
--
ALTER TABLE `socio`
  ADD PRIMARY KEY (`id_socio`);

--
-- Indices de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`id_tarifa`);

--
-- Indices de la tabla `usuario_sis`
--
ALTER TABLE `usuario_sis`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indices de la tabla `zona`
--
ALTER TABLE `zona`
  ADD PRIMARY KEY (`id_zona`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignacion_medidor`
--
ALTER TABLE `asignacion_medidor`
  MODIFY `id_asignacion` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `consumo`
--
ALTER TABLE `consumo`
  MODIFY `id_consumo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `deudas`
--
ALTER TABLE `deudas`
  MODIFY `id_deuda` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `medidor`
--
ALTER TABLE `medidor`
  MODIFY `id_medidor` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `socio`
--
ALTER TABLE `socio`
  MODIFY `id_socio` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  MODIFY `id_tarifa` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario_sis`
--
ALTER TABLE `usuario_sis`
  MODIFY `id_usuario` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `zona`
--
ALTER TABLE `zona`
  MODIFY `id_zona` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacion_medidor`
--
ALTER TABLE `asignacion_medidor`
  ADD CONSTRAINT `asignacion_medidor_ibfk_1` FOREIGN KEY (`id_medidor`) REFERENCES `medidor` (`id_medidor`),
  ADD CONSTRAINT `asignacion_medidor_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `socio` (`id_socio`),
  ADD CONSTRAINT `asignacion_medidor_ibfk_3` FOREIGN KEY (`id_zona`) REFERENCES `zona` (`id_zona`);

--
-- Filtros para la tabla `consumo`
--
ALTER TABLE `consumo`
  ADD CONSTRAINT `consumo_ibfk_1` FOREIGN KEY (`id_asignacion`) REFERENCES `asignacion_medidor` (`id_asignacion`);

--
-- Filtros para la tabla `deudas`
--
ALTER TABLE `deudas`
  ADD CONSTRAINT `deudas_ibfk_1` FOREIGN KEY (`id_consumo`) REFERENCES `consumo` (`id_consumo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
