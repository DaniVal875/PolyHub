<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Obtener el ID del asset desde la URL
$id_asset_get = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_asset_get <= 0) {
    header("Location: index.php");
    exit();
}

// Conexión a la base de datos oficial poly_hub
$conexion = new mysqli("127.0.0.1", "root", "", "poly_hub");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// 2. Obtener los detalles completos del asset seleccionado (QUITAMOS "AND a.activo = 1")
$asset = null;
$sql = "SELECT 
            a.id_asset,
            a.id_creador,
            a.titulo,
            a.descripcion,
            a.precio,
            a.portadaurl,
            a.fechaPublicacion,
            a.activo,
            u.username AS creador_nombre,
            u.avatar_url AS creador_avatar
        FROM assets a
        INNER JOIN usuarios u ON a.id_creador = u.id_usuario
        WHERE a.id_asset = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_asset_get);
$stmt->execute();
$resultado = $stmt->get_result();

if ($row = $resultado->fetch_assoc()) {
    $asset = $row;
} 
$stmt->close();

// Si el asset ni siquiera existe en la base de datos, regresamos
if (!$asset) {
    $conexion->close();
    header("Location: index.php");
    exit();
}

// 3. Obtener ID del usuario logueado e inicializar estados
$es_mi_propio_asset = false;
$usuario_logueado = false;
$id_usuario_sesion = null;
$ya_en_carrito = false;

if (isset($_SESSION['user_correo'])) {
    $usuario_logueado = true;
    $correo_sesion = $_SESSION['user_correo'];
    
    $stmtU = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmtU->bind_param("s", $correo_sesion);
    $stmtU->execute();
    $resU = $stmtU->get_result();
    if ($rowU = $resU->fetch_assoc()) {
        $id_usuario_sesion = (int)$rowU['id_usuario'];
        
        // Verificar si es dueño del asset
        if ($id_usuario_sesion === (int)$asset['id_creador']) {
            $es_mi_propio_asset = true;
        }
    }
    $stmtU->close();

    // 4. LÓGICA PARA AGREGAR AL CARRITO (POST sin redirección externa)
    if ($id_usuario_sesion && isset($_POST['action_carrito']) && !$es_mi_propio_asset) {
        $check = $conexion->prepare("SELECT id_carrito FROM carrito WHERE id_usuario = ? AND id_asset = ?");
        $check->bind_param("ii", $id_usuario_sesion, $id_asset_get);
        $check->execute();
        $resCheck = $check->get_result();
        
        if ($resCheck->num_rows == 0) {
            $insert_stmt = $conexion->prepare("INSERT INTO carrito (id_usuario, id_asset) VALUES (?, ?)");
            $insert_stmt->bind_param("ii", $id_usuario_sesion, $id_asset_get);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        $check->close();
        
        $conexion->close();
        header("Location: VisualizarAssets.php?id=" . $id_asset_get);
        exit();
    }

    // 5. Verificar si ya se encuentra en el carrito actualmente
    if ($id_usuario_sesion) {
        $checkCart = $conexion->prepare("SELECT id_carrito FROM carrito WHERE id_usuario = ? AND id_asset = ?");
        $checkCart->bind_param("ii", $id_usuario_sesion, $id_asset_get);
        $checkCart->execute();
        if ($checkCart->get_result()->num_rows > 0) {
            $ya_en_carrito = true;
        }
        $checkCart->close();
    }
}

// =========================================================================
// NUEVA COMPROBACIÓN CRÍTICA DE VISIBILIDAD
// Si está inactivo Y NO eres el dueño, te vas al index.
// =========================================================================
if ((int)$asset['activo'] === 0 && !$es_mi_propio_asset) {
    $conexion->close();
    header("Location: index.php");
    exit();
}

$conexion->close();

// Formatear precio y fecha
$precio_num = (float)$asset['precio'];
$precio_texto = ($precio_num <= 0) ? 'Free' : '$' . number_format($precio_num, 2) . ' MXN';
$fecha_formateada = date("d/m/Y", strtotime($asset['fechaPublicacion']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($asset['titulo']); ?> - PolyHub</title>
    <link rel="stylesheet" href="Diseño css/EstiloPaginas.css">
</head>
<body>

    <div class="vista-asset-wrapper">
        <a href="index.php" class="btn-regresar">⬅️ Regresar</a>

        <main class="contenedor-detalle-asset">
            
            <?php if ((int)$asset['activo'] === 0): ?>
                <div style="background: rgba(231, 76, 60, 0.15); border: 1px solid #e74c3c; color: #e74c3c; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; text-align: center;">
                    Este asset se encuentra actualmente Oculto/Inactivo. Solo tú puedes verlo.
                </div>
            <?php endif; ?>
            
            <div class="bloque-superior-layout">
                <div class="zona-imagen-izq">
                    <img src="<?php echo htmlspecialchars($asset['portadaurl']); ?>" alt="<?php echo htmlspecialchars($asset['titulo']); ?>" class="imagen-asset-principal">
                </div>
                
                <div class="zona-acciones-der">
                    <div class="contenedor-botones-flotantes">
                        <?php if ($es_mi_propio_asset): ?>
                            <button onclick="window.location.href='SubirAssets.php?editar_id=<?= $asset['id_asset'] ?>'" class="btn-accion-asset btn-editar-asset">Editar Asset</button>
                        <?php endif; ?>
                        
                        <button class="btn-accion-asset btn-descargar-asset">Descargar</button>

                        <?php if (!$es_mi_propio_asset): ?>
                            
                            <?php if (!$usuario_logueado): ?>
                                <button onclick="window.location.href='../Vistas/ComienzoDeSesion.php'" class="btn-accion-asset btn-carrito-asset">
                                    🛒 Añadir al Carrito
                                </button>
                            
                            <?php elseif ($ya_en_carrito): ?>
                                <button onclick="window.location.href='Carrito.php'" class="btn-accion-asset" style="background-color: var(--accent-blue); border: 1px solid #3ca2d3; color: var(--texto-blanco); cursor: pointer;">
                                    Ver en mi Carrito
                                </button>
                            
                            <?php else: ?>
                                <form method="POST" style="width: 100%; margin: 0; padding: 0;">
                                    <button type="submit" name="action_carrito" class="btn-accion-asset btn-carrito-asset" style="width: 100%;">
                                        🛒 Añadir al Carrito
                                    </button>
                                </form>
                            <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="bloque-inferior-info">
                
                <h1 class="detalle-titulo-asset"><?php echo htmlspecialchars($asset['titulo']); ?></h1>
                
                <p class="detalle-descripcion-asset"><?php echo nl2br(htmlspecialchars($asset['descripcion'])); ?></p>
                
                <div class="detalle-creador-box">
                    <img src="<?php echo htmlspecialchars($asset['creador_avatar']); ?>" alt="Avatar" class="avatar-creador-mini">
                    <div class="info-creador-texto">
                        <span class="label-autor">Publicado por</span>
                        <span class="nombre-autor"><?php echo htmlspecialchars($asset['creador_nombre']); ?></span>
                    </div>
                </div>

                <hr class="separador-metadatos">

                <div class="metadatos-valores-box">
                    <div class="item-meta">
                        <span class="meta-label">Precio</span>
                        <span class="meta-valor <?php echo ($precio_num <= 0) ? 'precio-gratis' : ''; ?>">
                            <?php echo $precio_texto; ?>
                        </span>
                    </div>
                    
                    <div class="item-meta">
                        <span class="meta-label">Fecha de Publicación</span>
                        <span class="meta-valor fecha-texto"><?php echo $fecha_formateada; ?></span>
                    </div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>