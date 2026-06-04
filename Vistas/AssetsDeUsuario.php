<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bloqueo de seguridad si no hay sesión activa
if (!isset($_SESSION['user_correo'])) {
    header("Location: ../Vistas/InicioSesion.php");
    exit();
}

// Variables de sesión para renderizar el panel superior
$user_name = $_SESSION['user_nombre'] ?? 'Usuario';
$user_saldo = $_SESSION['user_saldo'] ?? 0.00;
$avatar_url = $_SESSION['user_avatar'] ?? 'uploads/avatars/default.png';
$user_correo = $_SESSION['user_correo'];

// Configuración de conexión de tu servidor (Base de datos: poly_hub)
$host = "localhost";
$user = "root";
$password = "";
$database = "poly_hub";

$conexion = new mysqli($host, $user, $password, $database);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// 1. Encontrar el id_usuario en base al correo de la sesión actual
$id_usuario = 0;
$stmtUser = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
$stmtUser->bind_param("s", $user_correo);
$stmtUser->execute();
$resUser = $stmtUser->get_result();
if ($rowUser = $resUser->fetch_assoc()) {
    $id_usuario = $rowUser['id_usuario'];
}
$stmtUser->close();

// 2. Consultar los assets reales de este creador y transformarlos al formato esperado por el JS
$assetsDbMapped = [];
if ($id_usuario > 0) {
    $sql = "SELECT 
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
            WHERE a.id_creador = ?
            GROUP BY a.id_asset
            ORDER BY a.fechaPublicacion DESC";

    $stmtAssets = $conexion->prepare($sql);
    $stmtAssets->bind_param("i", $id_usuario);
    $stmtAssets->execute();
    $resultado = $stmtAssets->get_result();

    while ($row = $resultado->fetch_assoc()) {
        $precio_num = (float)$row['precio'];
        $es_gratis = ($precio_num <= 0);
        
        // Estructura idéntica a tus objetos simulados previos
        $assetsDbMapped[] = [
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
            'plataformas' => 'Windows, Web' // Valor base predeterminado
        ];
    }
    $stmtAssets->close();
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis Assets - PolyHub</title>
    <link rel="stylesheet" href="Diseño css/EstiloPaginas.css">
</head>
<body>

    <div class="assets-wrapper-global">
        
        <a href="index.php" class="btn-regresar">⬅️ Regresar al menú principal</a>

        <header class="panel-usuario-cabecera">
            <div class="bloque-usuario-izq">
                <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Avatar" class="mini-avatar-panel">
                <div class="info-texto-panel">
                    <span class="sub-label">Panel de Creador</span>
                    <h1 class="nombre-usuario-panel"><?php echo htmlspecialchars($user_name); ?></h1>
                </div>
            </div>
            <div class="bloque-usuario-der">
                <div class="billetera-panel">
                    <span class="saldo-label">Tu Saldo</span>
                    <span class="saldo-monto-panel">$<?php echo number_format((float)$user_saldo, 2, '.', ''); ?> MXN</span>
                </div>
            </div>
        </header>

        <main class="seccion-assets-creador">
            <h2 class="titulo-seccion-assets">Mis Assets Publicados</h2>
            
            <div id="no-assets-message" class="mensaje-vacio-box" style="display: <?php echo empty($assetsDbMapped) ? 'block' : 'none'; ?>;">
                <div class="icono-vacio">📦</div>
                <h3>No cuentas con assets subidos</h3>
                <p>Comienza a publicar tus modelos o scripts para verlos reflejados aquí.</p>
            </div>

            <div class="assets-grid" id="assets-grid"></div>
        </main>
    </div>

    <script>
        const assetsSimulados = <?php echo json_encode($assetsDbMapped); ?>;
    </script>
    <script src="../Controladores/JavaScript/Contenedores.js"></script>
</body>
</html>