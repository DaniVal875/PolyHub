-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-04-2026 a las 21:51:09
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
-- Base de datos: `poly_hub`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `Comprar_Asset` (IN `p_id_comprador` INT, IN `p_id_asset` INT)   BEGIN
    DECLARE v_precio DECIMAL(10,2);
    DECLARE v_saldo_comprador DECIMAL(10,2);
    DECLARE v_id_creador INT;
    DECLARE v_ya_comprado INT;

    -- Empezamos la transacción para evitar cobros fantasma si algo falla
    START TRANSACTION;

    -- 1. Obtener datos del asset
    SELECT precio, id_creador INTO v_precio, v_id_creador
    FROM ASSETS WHERE id_asset = p_id_asset;

    -- 2. Revisar saldo del comprador
    SELECT saldo INTO v_saldo_comprador
    FROM USUARIOS WHERE id_usuario = p_id_comprador;

    -- 3. Verificar si el usuario ya lo tiene en su librería
    SELECT COUNT(*) INTO v_ya_comprado
    FROM LIBRERIAUSUARIOS 
    WHERE id_usuario = p_id_comprador AND id_asset = p_id_asset;

    -- Lógica de validación
    IF v_ya_comprado > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El usuario ya posee este asset en su librería.';
        ROLLBACK;
    ELSEIF v_saldo_comprador < v_precio THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Saldo insuficiente para realizar la compra.';
        ROLLBACK;
    ELSE
        -- Quitar dinero al comprador
        UPDATE USUARIOS SET saldo = saldo - v_precio WHERE id_usuario = p_id_comprador;
        
        -- Darle el dinero al creador
        UPDATE USUARIOS SET saldo = saldo + v_precio WHERE id_usuario = v_id_creador;

        -- Agregar el asset a la librería del comprador
        INSERT INTO LIBRERIAUSUARIOS (id_usuario, id_asset, montoPagado) 
        VALUES (p_id_comprador, p_id_asset, v_precio);

        -- Registrar ticket de salida de dinero (Comprador)
        INSERT INTO MOVIMIENTOSBILLETERA (id_usuario, tipoMovimiento, monto, id_assetRelacionado, descripcion)
        VALUES (p_id_comprador, 'Compra', v_precio, p_id_asset, 'Pago por adquisición de asset');

        -- Registrar ticket de entrada de dinero (Creador)
        INSERT INTO MOVIMIENTOSBILLETERA (id_usuario, tipoMovimiento, monto, id_assetRelacionado, descripcion)
        VALUES (v_id_creador, 'Venta', v_precio, p_id_asset, 'Ingreso por venta de asset');

        -- Todo salió bien, confirmamos los cambios
        COMMIT;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Publicar_Resena` (IN `p_id_usuario` INT, IN `p_id_asset` INT, IN `p_calificacion` INT, IN `p_comentario` TEXT)   BEGIN
    DECLARE v_tiene_comprado INT;

    -- Revisamos si existe registro en la librería
    SELECT COUNT(*) INTO v_tiene_comprado
    FROM LIBRERIAUSUARIOS
    WHERE id_usuario = p_id_usuario AND id_asset = p_id_asset;

    -- Validaciones
    IF p_calificacion < 1 OR p_calificacion > 5 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: La calificación debe estar entre 1 y 5 estrellas.';
    ELSEIF v_tiene_comprado = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Debes adquirir el asset en tu librería antes de calificarlo.';
    ELSE
        -- Si pasa los filtros, se inserta la reseña
        INSERT INTO RESENAS (id_asset, id_usuario, calificacion, comentario)
        VALUES (p_id_asset, p_id_usuario, p_calificacion, p_comentario);
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Recargar_Saldo` (IN `p_id_usuario` INT, IN `p_monto` DECIMAL(10,2))   BEGIN
    -- Validamos que no intenten recargar 0 o números negativos
    IF p_monto <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El monto de recarga debe ser mayor a 0.';
    ELSE
        -- Usamos transacción por si algo truena a la mitad
        START TRANSACTION;
        
        -- Sumamos el saldo a la cuenta del usuario
        UPDATE USUARIOS 
        SET saldo = saldo + p_monto 
        WHERE id_usuario = p_id_usuario;
        
        -- Creamos el ticket de recarga
        INSERT INTO MOVIMIENTOSBILLETERA (id_usuario, tipoMovimiento, monto, descripcion)
        VALUES (p_id_usuario, 'Recarga', p_monto, 'Recarga de saldo a la billetera');
        
        COMMIT;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Seguir_Usuario` (IN `p_id_seguidor` INT, IN `p_id_seguido` INT)   BEGIN
    DECLARE v_ya_sigue INT;

    -- Validar que no se auto-siga
    IF p_id_seguidor = p_id_seguido THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No te puedes seguir a ti mismo.';
    ELSE
        -- Validar que no lo esté siguiendo ya
        SELECT COUNT(*) INTO v_ya_sigue
        FROM SEGUIDORES
        WHERE id_seguidor = p_id_seguidor AND id_seguido = p_id_seguido;

        IF v_ya_sigue > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Ya estás siguiendo a este creador.';
        ELSE
            -- Se realiza la acción
            INSERT INTO SEGUIDORES (id_seguidor, id_seguido)
            VALUES (p_id_seguidor, p_id_seguido);
        END IF;
    END IF;
END$$

--
-- Funciones
--
CREATE DEFINER=`root`@`localhost` FUNCTION `Aplicar_Descuento` (`precio_original` DECIMAL(10,2), `porcentaje_descuento` INT) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    -- Una sola línea: Calcula y retorna el precio con el descuento aplicado
    RETURN precio_original - (precio_original * (porcentaje_descuento / 100));
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `Calcular_Comision_Plataforma` (`precio` DECIMAL(10,2)) RETURNS DECIMAL(10,2) DETERMINISTIC BEGIN
    -- Una sola línea lógica de retorno
    RETURN precio * 0.10; 
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `Contar_Seguidores_Usuario` (`p_id_usuario` INT) RETURNS INT(11) READS SQL DATA BEGIN
    -- Una sola línea: Cuenta cuántos registros hay donde el usuario es el seguido
    RETURN (SELECT COUNT(*) FROM SEGUIDORES WHERE id_seguido = p_id_usuario);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `Es_Asset_Gratis` (`precio` DECIMAL(10,2)) RETURNS TINYINT(1) DETERMINISTIC BEGIN
    -- Una sola línea: Retorna 1 (TRUE) si el precio es 0, o 0 (FALSE) si es mayor
    RETURN precio <= 0.00;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `Ganancia_Neta_Creador` (`p_id_creador` INT) RETURNS DECIMAL(10,2) READS SQL DATA BEGIN
    DECLARE total_bruto DECIMAL(10,2);
    DECLARE ganancia_neta DECIMAL(10,2);
    
    -- Sumamos todo lo que han pagado por los assets de este creador
    SELECT SUM(LU.montoPagado) INTO total_bruto
    FROM LIBRERIAUSUARIOS LU
    INNER JOIN ASSETS A ON LU.id_asset = A.id_asset
    WHERE A.id_creador = p_id_creador;
    
    -- Si no ha vendido nada, evitamos errores
    IF total_bruto IS NULL THEN
        SET total_bruto = 0.00;
    END IF;
    
    -- Calculamos la ganancia neta (85% para el creador, 15% para la plataforma)
    SET ganancia_neta = total_bruto * 0.85;
    
    RETURN ganancia_neta;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `Nivel_Comprador` (`p_id_usuario` INT) RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_general_ci READS SQL DATA BEGIN
    DECLARE total_compras INT;
    DECLARE rango VARCHAR(20);
    
    -- Contamos cuántas transacciones tiene el usuario
    SELECT COUNT(*) INTO total_compras
    FROM LIBRERIAUSUARIOS
    WHERE id_usuario = p_id_usuario;
    
    -- Lógica condicional para asignar el rango
    IF total_compras = 0 THEN
        SET rango = 'Novato';
    ELSEIF total_compras <= 5 THEN
        SET rango = 'Aficionado';
    ELSEIF total_compras <= 15 THEN
        SET rango = 'Coleccionista';
    ELSE
        SET rango = 'Ballena de Assets';
    END IF;
    
    RETURN rango;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `Nivel_Exito_Asset` (`p_id_asset` INT) RETURNS VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci READS SQL DATA BEGIN
    DECLARE total_ventas INT;
    DECLARE nivel_exito VARCHAR(50);
    
    -- Contamos cuántas veces aparece en la librería de los usuarios
    SELECT COUNT(*) INTO total_ventas
    FROM LIBRERIAUSUARIOS
    WHERE id_asset = p_id_asset;
    
    -- Definimos el éxito basado en las ventas
    IF total_ventas = 0 THEN
        SET nivel_exito = 'Sin ventas';
    ELSEIF total_ventas < 10 THEN
        SET nivel_exito = 'Ventas Bajas';
    ELSEIF total_ventas < 50 THEN
        SET nivel_exito = 'Popular';
    ELSE
        SET nivel_exito = 'Best Seller';
    END IF;
    
    RETURN nivel_exito;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `Reputacion_Creador` (`p_id_creador` INT) RETURNS DECIMAL(3,2) READS SQL DATA BEGIN
    -- Declaramos la variable donde guardaremos el promedio
    DECLARE promedio_general DECIMAL(3,2);
    
    -- Calculamos el promedio cruzando ASSETS y RESENAS
    SELECT AVG(R.calificacion) INTO promedio_general
    FROM ASSETS A
    INNER JOIN RESENAS R ON A.id_asset = R.id_asset
    WHERE A.id_creador = p_id_creador;
    
    -- Si el creador aún no tiene reseñas, el promedio será NULL, lo cambiamos a 0
    IF promedio_general IS NULL THEN
        SET promedio_general = 0.00;
    END IF;
    
    -- Retornamos el resultado
    RETURN promedio_general;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `Tamano_Total_Asset` (`p_id_asset` INT) RETURNS DECIMAL(8,2) READS SQL DATA BEGIN
    DECLARE peso_total DECIMAL(8,2);
    
    -- Sumamos el tamaño de todos los archivos de ese asset
    SELECT SUM(tamanoMB) INTO peso_total
    FROM ARCHIVOSASSET
    WHERE id_asset = p_id_asset;
    
    -- Si el asset no tiene archivos subidos aún, evitamos el NULL
    IF peso_total IS NULL THEN
        SET peso_total = 0.00;
    END IF;
    
    RETURN peso_total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `Total_Ventas_Asset` (`p_id_asset` INT) RETURNS DECIMAL(10,2) READS SQL DATA BEGIN
    -- Múltiples líneas: Declaración de variables, asignaciones y condicionales
    DECLARE total_generado DECIMAL(10,2);
    
    SELECT SUM(montoPagado) INTO total_generado
    FROM LIBRERIAUSUARIOS
    WHERE id_asset = p_id_asset;
    
    -- Si el asset no ha vendido nada, la suma da NULL. Lo cambiamos a 0.
    IF total_generado IS NULL THEN
        SET total_generado = 0.00;
    END IF;
    
    RETURN total_generado;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivosasset`
--

CREATE TABLE `archivosasset` (
  `id_archivo` int(11) NOT NULL,
  `id_asset` int(11) DEFAULT NULL,
  `nombrearchivo` varchar(255) NOT NULL,
  `tamanoMB` decimal(8,2) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `assets`
--

CREATE TABLE `assets` (
  `id_asset` int(11) NOT NULL,
  `id_creador` int(11) DEFAULT NULL,
  `id_licencia` int(11) DEFAULT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT 0.00,
  `portadaurl` varchar(255) DEFAULT NULL,
  `fechaPublicacion` datetime DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `assets`
--

INSERT INTO `assets` (`id_asset`, `id_creador`, `id_licencia`, `titulo`, `descripcion`, `precio`, `portadaurl`, `fechaPublicacion`, `activo`) VALUES
(1, 1, 1, '3 Imgs del Master Chief', 'un set de 3 imagenes bien epicas del master chief, asi farmeando aura machin', 1.17, 'https://i0.wp.com/codigoespagueti.com/wp-content/uploads/2021/12/Halo-Infinite-Master-Chief.jpg', '2026-04-26 12:10:21', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asset_categorias`
--

CREATE TABLE `asset_categorias` (
  `id_asset` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asset_etiquetas`
--

CREATE TABLE `asset_etiquetas` (
  `id_asset` int(11) NOT NULL,
  `id_etiqueta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`) VALUES
(1, 'Modelos 3D'),
(2, 'Sprites 2D / Pixel Art'),
(3, 'Scripts y Código'),
(4, 'Plantillas UI / UX'),
(5, 'Texturas y Materiales'),
(6, 'Audio y Efectos de Sonido (SFX)'),
(7, 'Prototipos IoT y Hardware'),
(8, 'Animaciones'),
(9, 'Módulos de Base de Datos'),
(10, 'Plantillas Web (Front-end)'),
(11, 'Herramientas y Utilidades (Tools)'),
(12, 'Modelos para Impresión 3D'),
(13, 'VFX (Efectos Visuales)'),
(14, 'Música (Soundtracks)'),
(15, 'Documentación y Manuales'),
(16, 'Diagramas y Esquemas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etiquetas`
--

CREATE TABLE `etiquetas` (
  `id_etiqueta` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `etiquetas`
--

INSERT INTO `etiquetas` (`id_etiqueta`, `nombre`) VALUES
(1, 'Godot 4'),
(2, 'Godot 4.4'),
(3, 'GDScript'),
(4, 'Low Poly'),
(5, 'PBR'),
(6, 'Rigged (Con esqueleto)'),
(7, 'Arduino'),
(8, 'ESP32'),
(9, 'Multijugador'),
(10, 'Pixel Art'),
(11, 'Figma'),
(12, 'Sci-Fi'),
(13, 'Fantasía'),
(14, 'Optimizado (Game Ready)'),
(15, 'C#'),
(16, 'C++'),
(17, 'SQL'),
(18, 'MySQL'),
(19, 'HTML/CSS'),
(20, 'Windows Forms'),
(21, 'XAMPP'),
(22, 'CharacterBody3D'),
(23, 'Raycast'),
(24, 'Navegación / Pathfinding'),
(25, 'Generación Procedural'),
(26, 'Sistemas de Inventario'),
(27, 'Tower Defense'),
(28, 'Gestión de Recursos'),
(29, 'Estrategia Espacial'),
(30, 'Post-apocalíptico'),
(31, 'Cyberpunk'),
(32, 'Supervivencia'),
(33, 'Sensores'),
(34, 'RFID'),
(35, 'Ultrasónico'),
(36, 'PIR (Movimiento)'),
(37, 'Domótica'),
(38, 'Prototipado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_asset`
--

CREATE TABLE `imagenes_asset` (
  `id_imagen` int(11) NOT NULL,
  `id_asset` int(11) NOT NULL,
  `url_imagen` varchar(255) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `fechaSubida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `imagenes_asset`
--

INSERT INTO `imagenes_asset` (`id_imagen`, `id_asset`, `url_imagen`, `orden`, `fechaSubida`) VALUES
(1, 1, 'https://i0.wp.com/codigoespagueti.com/wp-content/uploads/2021/12/Halo-Infinite-Master-Chief.jpg', 1, '2026-04-26 12:11:12'),
(2, 1, 'https://static0.polygonimages.com/wordpress/wp-content/uploads/chorus/uploads/chorus_asset/file/11614603/h3_010_68adc78167c54528b438982a40ff04a7.jpg?w=1600&h=900&fit=crop', 2, '2026-04-26 12:11:46'),
(3, 1, 'https://images3.alphacoders.com/161/thumb-1920-16189.jpg', 0, '2026-04-26 12:12:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libreriausuarios`
--

CREATE TABLE `libreriausuarios` (
  `id_transaccion` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_asset` int(11) DEFAULT NULL,
  `montoPagado` decimal(10,2) NOT NULL,
  `fechaAdquisicion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `licencias`
--

CREATE TABLE `licencias` (
  `id_licencia` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `url_terminos` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `licencias`
--

INSERT INTO `licencias` (`id_licencia`, `nombre`, `descripcion`, `url_terminos`) VALUES
(1, 'CC0 (Dominio Público)', 'El creador ha renunciado a todos sus derechos. Puedes copiar, modificar, distribuir y usar el asset, incluso con fines comerciales, sin pedir permiso.', 'https://creativecommons.org/publicdomain/zero/1.0/'),
(2, 'CC-BY 4.0', 'Permite compartir y adaptar el material para cualquier propósito, incluso comercial, siempre y cuando se dé el crédito adecuado al autor original.', 'https://creativecommons.org/licenses/by/4.0/'),
(3, 'MIT License', 'Una licencia de software permisiva, ideal para scripts y código. Permite casi cualquier uso, siempre que se incluya el aviso de copyright original.', 'https://opensource.org/licenses/MIT'),
(4, 'GPLv3', 'Licencia copyleft. Requiere que cualquier proyecto modificado o derivado que se distribuya también use la misma licencia GPL.', 'https://www.gnu.org/licenses/gpl-3.0.html'),
(5, 'Estándar de PolyHub', 'Licencia predeterminada de la plataforma. Permite el uso en proyectos comerciales y personales, pero prohíbe la reventa directa del asset sin modificaciones.', ''),
(6, 'Apache 2.0', 'Permite el uso comercial, modificación y distribución. Requiere conservar los avisos de derechos de autor y renuncias de responsabilidad.', 'https://www.apache.org/licenses/LICENSE-2.0'),
(7, 'CC-BY-SA 4.0', 'Puedes remezclar, transformar y crear a partir del material, incluso con fines comerciales, pero debes distribuir tus contribuciones bajo la misma licencia del original.', 'https://creativecommons.org/licenses/by-sa/4.0/'),
(8, 'Uso Educativo / No Comercial', 'Permite el uso del asset estrictamente para proyectos escolares, académicos o de aprendizaje. Prohibido su uso para monetizar.', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientosbilletera`
--

CREATE TABLE `movimientosbilletera` (
  `id_movimiento` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `tipoMovimiento` varchar(50) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `id_assetRelacionado` int(11) DEFAULT NULL,
  `fechaMovimiento` datetime DEFAULT current_timestamp(),
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resenas`
--

CREATE TABLE `resenas` (
  `id_resena` int(11) NOT NULL,
  `id_asset` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `calificacion` int(11) DEFAULT NULL CHECK (`calificacion` between 1 and 5),
  `comentario` text DEFAULT NULL,
  `fechaCreacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguidores`
--

CREATE TABLE `seguidores` (
  `id_seguidor` int(11) NOT NULL,
  `id_seguido` int(11) NOT NULL,
  `fechaSeguimiento` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar_url` varchar(255) DEFAULT 'uploads/avatars/default.png',
  `saldo` decimal(10,2) DEFAULT 0.00,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `username`, `email`, `password`, `avatar_url`, `saldo`, `fechaRegistro`) VALUES
(1, 'dupstergames', 'spaidermita@gmail.com', 'dani1234', 'https://static-cdn.jtvnw.net/jtv_user_pictures/37c40bdb-ca54-42b5-9071-1753cd0d50b7-profile_image-300x300.png', 67.69, '2026-04-26 11:51:19');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_assets_categorias_etiquetas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_assets_categorias_etiquetas` (
`id_asset` int(11)
,`titulo` varchar(150)
,`categorias` mediumtext
,`etiquetas` mediumtext
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_catalogo_assets`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_catalogo_assets` (
`id_asset` int(11)
,`titulo` varchar(150)
,`precio` decimal(10,2)
,`creador` varchar(50)
,`licencia` varchar(100)
,`calificacion_promedio` decimal(14,4)
,`total_resenas` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_ingresos_creadores`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_ingresos_creadores` (
`id_creador` int(11)
,`creador` varchar(50)
,`total_assets_publicados` bigint(21)
,`total_ventas` bigint(21)
,`ingresos_totales` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_libreria_usuarios`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_libreria_usuarios` (
`id_usuario` int(11)
,`comprador` varchar(50)
,`id_asset` int(11)
,`asset_comprado` varchar(150)
,`fechaAdquisicion` datetime
,`montoPagado` decimal(10,2)
,`nombrearchivo` varchar(255)
,`version` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_assets_categorias_etiquetas`
--
DROP TABLE IF EXISTS `vista_assets_categorias_etiquetas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_assets_categorias_etiquetas`  AS SELECT `a`.`id_asset` AS `id_asset`, `a`.`titulo` AS `titulo`, ifnull(group_concat(distinct `c`.`nombre` separator ', '),'Sin categoría') AS `categorias`, ifnull(group_concat(distinct `e`.`nombre` separator ', '),'Sin etiquetas') AS `etiquetas` FROM ((((`assets` `a` left join `asset_categorias` `ac` on(`a`.`id_asset` = `ac`.`id_asset`)) left join `categorias` `c` on(`ac`.`id_categoria` = `c`.`id_categoria`)) left join `asset_etiquetas` `ae` on(`a`.`id_asset` = `ae`.`id_asset`)) left join `etiquetas` `e` on(`ae`.`id_etiqueta` = `e`.`id_etiqueta`)) GROUP BY `a`.`id_asset`, `a`.`titulo` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_catalogo_assets`
--
DROP TABLE IF EXISTS `vista_catalogo_assets`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_catalogo_assets`  AS SELECT `a`.`id_asset` AS `id_asset`, `a`.`titulo` AS `titulo`, `a`.`precio` AS `precio`, `u`.`username` AS `creador`, `l`.`nombre` AS `licencia`, ifnull(avg(`r`.`calificacion`),0) AS `calificacion_promedio`, count(`r`.`id_resena`) AS `total_resenas` FROM (((`assets` `a` join `usuarios` `u` on(`a`.`id_creador` = `u`.`id_usuario`)) join `licencias` `l` on(`a`.`id_licencia` = `l`.`id_licencia`)) left join `resenas` `r` on(`a`.`id_asset` = `r`.`id_asset`)) WHERE `a`.`activo` = 1 GROUP BY `a`.`id_asset`, `a`.`titulo`, `a`.`precio`, `u`.`username`, `l`.`nombre` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_ingresos_creadores`
--
DROP TABLE IF EXISTS `vista_ingresos_creadores`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_ingresos_creadores`  AS SELECT `u`.`id_usuario` AS `id_creador`, `u`.`username` AS `creador`, count(distinct `a`.`id_asset`) AS `total_assets_publicados`, count(`lu`.`id_transaccion`) AS `total_ventas`, ifnull(sum(`lu`.`montoPagado`),0.00) AS `ingresos_totales` FROM ((`usuarios` `u` left join `assets` `a` on(`u`.`id_usuario` = `a`.`id_creador`)) left join `libreriausuarios` `lu` on(`a`.`id_asset` = `lu`.`id_asset`)) GROUP BY `u`.`id_usuario`, `u`.`username` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_libreria_usuarios`
--
DROP TABLE IF EXISTS `vista_libreria_usuarios`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_libreria_usuarios`  AS SELECT `lu`.`id_usuario` AS `id_usuario`, `u`.`username` AS `comprador`, `a`.`id_asset` AS `id_asset`, `a`.`titulo` AS `asset_comprado`, `lu`.`fechaAdquisicion` AS `fechaAdquisicion`, `lu`.`montoPagado` AS `montoPagado`, `ar`.`nombrearchivo` AS `nombrearchivo`, `ar`.`version` AS `version` FROM (((`libreriausuarios` `lu` join `usuarios` `u` on(`lu`.`id_usuario` = `u`.`id_usuario`)) join `assets` `a` on(`lu`.`id_asset` = `a`.`id_asset`)) left join `archivosasset` `ar` on(`a`.`id_asset` = `ar`.`id_asset`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `archivosasset`
--
ALTER TABLE `archivosasset`
  ADD PRIMARY KEY (`id_archivo`),
  ADD KEY `id_asset` (`id_asset`);

--
-- Indices de la tabla `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id_asset`),
  ADD KEY `id_creador` (`id_creador`),
  ADD KEY `id_licencia` (`id_licencia`);

--
-- Indices de la tabla `asset_categorias`
--
ALTER TABLE `asset_categorias`
  ADD PRIMARY KEY (`id_asset`,`id_categoria`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `asset_etiquetas`
--
ALTER TABLE `asset_etiquetas`
  ADD PRIMARY KEY (`id_asset`,`id_etiqueta`),
  ADD KEY `id_etiqueta` (`id_etiqueta`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  ADD PRIMARY KEY (`id_etiqueta`);

--
-- Indices de la tabla `imagenes_asset`
--
ALTER TABLE `imagenes_asset`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `id_asset` (`id_asset`);

--
-- Indices de la tabla `libreriausuarios`
--
ALTER TABLE `libreriausuarios`
  ADD PRIMARY KEY (`id_transaccion`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_asset` (`id_asset`);

--
-- Indices de la tabla `licencias`
--
ALTER TABLE `licencias`
  ADD PRIMARY KEY (`id_licencia`);

--
-- Indices de la tabla `movimientosbilletera`
--
ALTER TABLE `movimientosbilletera`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_assetRelacionado` (`id_assetRelacionado`);

--
-- Indices de la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD PRIMARY KEY (`id_resena`),
  ADD KEY `id_asset` (`id_asset`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `seguidores`
--
ALTER TABLE `seguidores`
  ADD PRIMARY KEY (`id_seguidor`,`id_seguido`),
  ADD KEY `id_seguido` (`id_seguido`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `archivosasset`
--
ALTER TABLE `archivosasset`
  MODIFY `id_archivo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `assets`
--
ALTER TABLE `assets`
  MODIFY `id_asset` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  MODIFY `id_etiqueta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `imagenes_asset`
--
ALTER TABLE `imagenes_asset`
  MODIFY `id_imagen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `libreriausuarios`
--
ALTER TABLE `libreriausuarios`
  MODIFY `id_transaccion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `licencias`
--
ALTER TABLE `licencias`
  MODIFY `id_licencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `movimientosbilletera`
--
ALTER TABLE `movimientosbilletera`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resenas`
--
ALTER TABLE `resenas`
  MODIFY `id_resena` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `archivosasset`
--
ALTER TABLE `archivosasset`
  ADD CONSTRAINT `archivosasset_ibfk_1` FOREIGN KEY (`id_asset`) REFERENCES `assets` (`id_asset`) ON DELETE CASCADE;

--
-- Filtros para la tabla `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`id_creador`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL,
  ADD CONSTRAINT `assets_ibfk_2` FOREIGN KEY (`id_licencia`) REFERENCES `licencias` (`id_licencia`);

--
-- Filtros para la tabla `asset_categorias`
--
ALTER TABLE `asset_categorias`
  ADD CONSTRAINT `asset_categorias_ibfk_1` FOREIGN KEY (`id_asset`) REFERENCES `assets` (`id_asset`) ON DELETE CASCADE,
  ADD CONSTRAINT `asset_categorias_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE CASCADE;

--
-- Filtros para la tabla `asset_etiquetas`
--
ALTER TABLE `asset_etiquetas`
  ADD CONSTRAINT `asset_etiquetas_ibfk_1` FOREIGN KEY (`id_asset`) REFERENCES `assets` (`id_asset`) ON DELETE CASCADE,
  ADD CONSTRAINT `asset_etiquetas_ibfk_2` FOREIGN KEY (`id_etiqueta`) REFERENCES `etiquetas` (`id_etiqueta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `imagenes_asset`
--
ALTER TABLE `imagenes_asset`
  ADD CONSTRAINT `imagenes_asset_ibfk_1` FOREIGN KEY (`id_asset`) REFERENCES `assets` (`id_asset`) ON DELETE CASCADE;

--
-- Filtros para la tabla `libreriausuarios`
--
ALTER TABLE `libreriausuarios`
  ADD CONSTRAINT `libreriausuarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `libreriausuarios_ibfk_2` FOREIGN KEY (`id_asset`) REFERENCES `assets` (`id_asset`) ON DELETE CASCADE;

--
-- Filtros para la tabla `movimientosbilletera`
--
ALTER TABLE `movimientosbilletera`
  ADD CONSTRAINT `movimientosbilletera_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimientosbilletera_ibfk_2` FOREIGN KEY (`id_assetRelacionado`) REFERENCES `assets` (`id_asset`) ON DELETE SET NULL;

--
-- Filtros para la tabla `resenas`
--
ALTER TABLE `resenas`
  ADD CONSTRAINT `resenas_ibfk_1` FOREIGN KEY (`id_asset`) REFERENCES `assets` (`id_asset`) ON DELETE CASCADE,
  ADD CONSTRAINT `resenas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seguidores`
--
ALTER TABLE `seguidores`
  ADD CONSTRAINT `seguidores_ibfk_1` FOREIGN KEY (`id_seguidor`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `seguidores_ibfk_2` FOREIGN KEY (`id_seguido`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
