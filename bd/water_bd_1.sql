-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-11-2024 a las 16:37:06
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
(2, 2, 2, 2, '2023-03-15', 150.00);

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
(2, 2, 200.00, 250.00, '2023-03', 50.00, 'Consumo alto');

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
(2, 2, 60.00, '2023-04-15');

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
(2, 'MarcaB', 'ModeloY', '2023-02-01');

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
(1, '12345678', 'Juan', 'Pérez', '555-1234', '2023-01-15'),
(2, '87654321', 'Ana', 'Gómez', '555-5678', '2023-02-20');

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
  MODIFY `id_asignacion` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `consumo`
--
ALTER TABLE `consumo`
  MODIFY `id_consumo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `deudas`
--
ALTER TABLE `deudas`
  MODIFY `id_deuda` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `medidor`
--
ALTER TABLE `medidor`
  MODIFY `id_medidor` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `socio`
--
ALTER TABLE `socio`
  MODIFY `id_socio` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
