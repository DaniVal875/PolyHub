<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión principal
$conexion = new mysqli("127.0.0.1", "root", "", "poly_hub");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Inicializar variables de feedback
$mensaje_exito = "";
$mensaje_error = "";

// Variables para el formulario (vacías por defecto para modo "Subir")
$es_edicion = false;
$id_asset_editar = 0;
$titulo = "";
$descripcion = "";
$precio = "0.00";
$id_licencia_sel = "";
$portadaurl = "";
$activo = 1;
$categorias_seleccionadas = [];
$etiquetas_seleccionadas = [];

// ==========================================
// MODO EDICIÓN: Cargar datos si viene ?editar_id=X
// ==========================================
if (isset($_GET['editar_id'])) {
    $es_edicion = true;
    $id_asset_editar = (int)$_GET['editar_id'];

    // 1. Obtener datos básicos del Asset
    $stmt = $conexion->prepare("SELECT * FROM assets WHERE id_asset = ?");
    $stmt->bind_param("i", $id_asset_editar);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($asset = $res->fetch_assoc()) {
        $titulo = $asset['titulo'];
        $descripcion = $asset['descripcion'];
        $precio = $asset['precio'];
        $id_licencia_sel = $asset['id_licencia'];
        $portadaurl = $asset['portadaurl'];
        $activo = $asset['activo'];
    }
    $stmt->close();

    // 2. Obtener categorías activas de este asset
    $resCat = $conexion->query("SELECT id_categoria FROM asset_categorias WHERE id_asset = $id_asset_editar");
    while($c = $resCat->fetch_assoc()) {
        $categorias_seleccionadas[] = $c['id_categoria'];
    }

    // 3. Obtener etiquetas activas de este asset
    $resEtiq = $conexion->query("SELECT id_etiqueta FROM asset_etiquetas WHERE id_asset = $id_asset_editar");
    while($e = $resEtiq->fetch_assoc()) {
        $etiquetas_seleccionadas[] = $e['id_etiqueta'];
    }
}

// ==========================================
// ACCIÓN: ELIMINAR ASSET COMPLETAMENTE
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_eliminar'])) {
    $id_eliminar = (int)$_POST['id_asset'];
    
    if ($id_eliminar > 0) {
        // Borramos relaciones para evitar problemas de llaves foráneas
        $conexion->query("DELETE FROM asset_categorias WHERE id_asset = $id_eliminar");
        $conexion->query("DELETE FROM asset_etiquetas WHERE id_asset = $id_eliminar");
        $conexion->query("DELETE FROM carrito WHERE id_asset = $id_eliminar");
        
        $stmtDel = $conexion->prepare("DELETE FROM assets WHERE id_asset = ?");
        $stmtDel->bind_param("i", $id_eliminar);
        
        if ($stmtDel->execute()) {
            $stmtDel->close();
            $conexion->close();
            header("Location: index.php");
            exit();
        } else {
            $mensaje_error = "Error al intentar eliminar el asset.";
        }
        $stmtDel->close();
    }
}

// ==========================================
// ACCIÓN: GUARDAR CAMBIOS (SUBIR O ACTUALIZAR)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['accion_eliminar'])) {
    $id_asset_post = isset($_POST['id_asset']) ? (int)$_POST['id_asset'] : 0;
    $titulo_post = $_POST['titulo'];
    $descripcion_post = $_POST['descripcion'];
    $precio_post = (float)$_POST['precio'];
    $id_licencia_post = (int)$_POST['id_licencia'];
    $portadaurl_post = $_POST['portadaurl'];
    $activo_post = isset($_POST['activo']) ? 1 : 0;
    
    $cats_post = isset($_POST['categorias']) ? $_POST['categorias'] : [];
    $etiqs_post = isset($_POST['etiquetas']) ? $_POST['etiquetas'] : [];

    // Recuperamos el ID del usuario en sesión de tu tabla de usuarios
    $id_creador = 1; // Por si acaso, un valor por defecto seguro
    if (isset($_SESSION['user_correo'])) {
        $stmtU = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmtU->bind_param("s", $_SESSION['user_correo']);
        $stmtU->execute();
        $resU = $stmtU->get_result();
        if ($rowU = $resU->fetch_assoc()) {
            $id_creador = (int)$rowU['id_usuario'];
        }
        $stmtU->close();
    }

    if ($id_asset_post > 0) {
        // --- MODO: ACTUALIZAR EXISTENTE (UPDATE) ---
        $sqlUp = "UPDATE assets SET titulo=?, descripcion=?, precio=?, id_licencia=?, portadaurl=?, activo=? WHERE id_asset=?";
        $stmtUp = $conexion->prepare($sqlUp);
        $stmtUp->bind_param("ssdisii", $titulo_post, $descripcion_post, $precio_post, $id_licencia_post, $portadaurl_post, $activo_post, $id_asset_post);
        
        if ($stmtUp->execute()) {
            // Actualizar categorías
            $conexion->query("DELETE FROM asset_categorias WHERE id_asset = $id_asset_post");
            foreach ($cats_post as $cat_id) {
                $conexion->query("INSERT INTO asset_categorias (id_asset, id_categoria) VALUES ($id_asset_post, ".(int)$cat_id.")");
            }
            
            // Actualizar etiquetas
            $conexion->query("DELETE FROM asset_etiquetas WHERE id_asset = $id_asset_post");
            foreach ($etiqs_post as $etiq_id) {
                $conexion->query("INSERT INTO asset_etiquetas (id_asset, id_etiqueta) VALUES ($id_asset_post, ".(int)$etiq_id.")");
            }
            
            $stmtUp->close();
            $conexion->close();
            header("Location: VisualizarAssets.php?id=" . $id_asset_post);
            exit();
        } else {
            $mensaje_error = "Error al actualizar la información.";
        }
        $stmtUp->close();

    } else {
        // --- MODO: SUBIR NUEVO ASSET (INSERT) ---
        $fecha_actual = date("Y-m-d H:i:s");
        $sqlIn = "INSERT INTO assets (id_creador, titulo, descripcion, precio, id_licencia, portadaurl, fechaPublicacion, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtIn = $conexion->prepare($sqlIn);
        $stmtIn->bind_param("issdissi", $id_creador, $titulo_post, $descripcion_post, $precio_post, $id_licencia_post, $portadaurl_post, $fecha_actual, $activo_post);
        
        if ($stmtIn->execute()) {
            $nuevo_id = $stmtIn->insert_id;
            
            // Insertar sus categorías correspondientes
            foreach ($cats_post as $cat_id) {
                $conexion->query("INSERT INTO asset_categorias (id_asset, id_categoria) VALUES ($nuevo_id, ".(int)$cat_id.")");
            }
            
            // Insertar sus etiquetas correspondientes
            foreach ($etiqs_post as $etiq_id) {
                $conexion->query("INSERT INTO asset_etiquetas (id_asset, id_etiqueta) VALUES ($nuevo_id, ".(int)$etiq_id.")");
            }
            
            $stmtIn->close();
            $conexion->close();
            // Redirigir directamente a ver el asset recién creado
            header("Location: VisualizarAssets.php?id=" . $nuevo_id);
            exit();
        } else {
            $mensaje_error = "Error al registrar el nuevo asset.";
        }
        $stmtIn->close();
    }
}

// Cargar catálogos para renderizar las opciones de la interfaz
$licencias = $conexion->query("SELECT * FROM licencias")->fetch_all(MYSQLI_ASSOC);
$categorias = $conexion->query("SELECT * FROM categorias")->fetch_all(MYSQLI_ASSOC);
$etiquetas = $conexion->query("SELECT * FROM etiquetas")->fetch_all(MYSQLI_ASSOC);

$conexion->close();
?>