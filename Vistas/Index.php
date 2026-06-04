<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificamos si la sesión tiene guardado el email
$is_logged = isset($_SESSION['user_correo']);
$avatar_url = 'uploads/avatars/default.png'; 
$saldo_real = '0.00';

// Conexión inicial para el usuario logueado
if ($is_logged) {
    $conexion = new mysqli("127.0.0.1", "root", "", "poly_hub");

    if (!$conexion->connect_error) {
        $correo_usuario = $_SESSION['user_correo'];
        
        $query = "SELECT avatar_url, saldo FROM usuarios WHERE email = ?";
        $stmt = $conexion->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("s", $correo_usuario);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($row = $resultado->fetch_assoc()) {
                if (!empty($row['avatar_url'])) {
                    $avatar_url = $row['avatar_url'];
                }
                $saldo_real = number_format($row['saldo'], 2, '.', '');
                
                $_SESSION['user_avatar'] = $avatar_url;
                $_SESSION['user_saldo'] = $saldo_real;
            }
            $stmt->close();
        }
        $conexion->close();
    }
}

// =========================================================================
// ADAPTACIÓN: Consultar Catálogo Completo y Elementos del Carrusel
// =========================================================================
$conexionDb = new mysqli("127.0.0.1", "root", "", "poly_hub");
$assetsCatalogoMapped = [];
$assets_carrusel = []; // Aquí guardaremos los 5 más recientes activos

if (!$conexionDb->connect_error) {
    
    // --- CONSULTA 1: Los 5 assets más recientes para el Carrusel ---
    $sql_carrusel = "SELECT id_asset, portadaurl FROM assets 
                     WHERE activo = 1 AND portadaurl IS NOT NULL AND portadaurl != '' 
                     ORDER BY fechaPublicacion DESC LIMIT 5";
    $resultado_carrusel = $conexionDb->query($sql_carrusel);
    
    if ($resultado_carrusel) {
        while ($rowC = $resultado_carrusel->fetch_assoc()) {
            $assets_carrusel[] = [
                'id' => (int)$rowC['id_asset'],
                'ruta' => $rowC['portadaurl']
            ];
        }
    }

    // --- CONSULTA 2: Todos los assets para el Catálogo General ---
    $sqlAssets = "SELECT 
                    a.id_asset,
                    a.titulo,
                    a.descripcion,
                    a.precio,
                    a.portadaurl,
                    u.username AS creador,
                    GROUP_CONCAT(DISTINCT c.nombre SEPARATOR ' / ') AS tipo_asset,
                    GROUP_CONCAT(DISTINCT e.nombre SEPARATOR ', ') AS etiquetas
                  FROM assets a
                  INNER JOIN usuarios u ON a.id_creador = u.id_usuario
                  LEFT JOIN asset_categorias ac ON a.id_asset = ac.id_asset
                  LEFT JOIN categorias c ON ac.id_categoria = c.id_categoria
                  LEFT JOIN asset_etiquetas ae ON a.id_asset = ae.id_asset
                  LEFT JOIN etiquetas e ON ae.id_etiqueta = e.id_etiqueta
                  WHERE a.activo = 1
                  GROUP BY a.id_asset
                  ORDER BY a.fechaPublicacion DESC";

    $resAssets = $conexionDb->query($sqlAssets);

    if ($resAssets) {
        while ($row = $resAssets->fetch_assoc()) {
            $precio_num = (float)$row['precio'];
            $es_gratis = ($precio_num <= 0);
            
            $assetsCatalogoMapped[] = [
                'id_asset' => (int)$row['id_asset'],
                'titulo' => $row['titulo'],
                'descripcion' => $row['descripcion'] ?? '',
                'precio' => $precio_num,
                'es_gratis' => $es_gratis,
                'precio_formateado' => $es_gratis ? 'Gratis' : '$' . number_format($precio_num, 2),
                'creador' => $row['creador'],
                'tipo_asset' => $row['tipo_asset'] ?? 'General',
                'etiquetas' => $row['etiquetas'] ?? 'Asset',
                'portadaurl' => $row['portadaurl'] ?? 'uploads/portadas/default.png',
                'plataformas' => 'Windows, Web'
            ];
        }
    }
    $conexionDb->close();
}

// Determinar si el catálogo está vacío para controlar el mensaje en HTML
$mostrar_mensaje_vacio = empty($assetsCatalogoMapped);

// Generador de cambio de versión para evitar dolores de cabeza con la caché del navegador
$cache_buster = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PolyHub - Explorar Assets</title>
    
    <link rel="stylesheet" href="Diseño css/DisenoIndex.css">
</head>
<body>

    <header style="width: 100%; border-bottom: 1px solid #ddd; padding: 10px 0; background: white; font-family: sans-serif;">
    </header>
    
    <h1 style="text-align: center; padding-top: 20px !important; margin: 0; color: #e8e8e8;">Lo Más Popular</h1>
    
    <div class="carrusel-destacado">
        <div id="carrusel-container" class="carrusel-container"></div>
        <button id="prev-btn" class="carrusel-btn btn-prev">&#10094;</button>
        <button id="next-btn" class="carrusel-btn btn-next">&#10095;</button>
    </div>
    
    <main class="main-container">
        <div id="assets-grid" class="assets-grid"></div>

        <div id="no-assets-message" class="no-assets-message" style="display: <?php echo $mostrar_mensaje_vacio ? 'block' : 'none'; ?>;">
            No hay assets disponibles ahora.
        </div>

        <div id="loading-spinner" class="loading-spinner" style="display: none;">
            Cargando más...
        </div>
    </main>

    <script>
        const dbSimuladaCarrusel = <?php echo json_encode($assets_carrusel); ?>;
    </script>

    <script>
        const oldMenu = document.getElementById('polyhub-menu-wrapper');
        if (oldMenu) oldMenu.remove();
    </script>

    <?php if ($is_logged): ?>
        <script>
            window.userAvatarUrl = "<?php echo $avatar_url; ?>";
            window.userSaldo = "<?php echo $saldo_real; ?>";
        </script>
        <script src="../Controladores/JavaScript/MenuUsuario.js?v=<?php echo $cache_buster; ?>"></script>
    <?php else: ?>
        <script src="../Controladores/JavaScript/MenuInvitado.js?v=<?php echo $cache_buster; ?>"></script>
    <?php endif; ?>

    <script>
        const assetsSimulados = <?php echo json_encode($assetsCatalogoMapped); ?>;
    </script>

    <script src="../Controladores/JavaScript/Contenedores.js"></script>
    <script src="../Controladores/JavaScript/Carrusel.js"></script>
</body>
</html>