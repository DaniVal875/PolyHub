<?php
session_start();

// Validar inicio de sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ComienzoDeSesion.php");
    exit();
}

// Configuración de la Base de Datos poly_hub
$mysqli = new mysqli("localhost", "root", "", "poly_hub"); 
if ($mysqli->connect_error) {
    die("Error crítico de conexión: " . $mysqli->connect_error);
}

$id_usuario = $_SESSION['id_usuario'];

/* ==========================================
   PROCESAMIENTO DE ACCIONES (DB SQL)
   ========================================== */

// ACCIÓN 1: Eliminar Asset del Carrito y de la Base de Datos
if (isset($_GET['eliminar'])) {
    $id_cart_del = intval($_GET['eliminar']);
    $stmt = $mysqli->prepare("DELETE FROM carrito WHERE id_carrito = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $id_cart_del, $id_usuario);
    $stmt->execute();
    $stmt->close();
    header("Location: Carrito.php");
    exit();
}

// ACCIÓN 2: Intercambio de niveles (Ordenación Cronológica)
if (isset($_GET['mover']) && isset($_GET['id'])) {
    $accion = $_GET['mover'];
    $id_cart_actual = intval($_GET['id']);
    
    $res_items = $mysqli->query("SELECT id_carrito FROM carrito WHERE id_usuario = $id_usuario ORDER BY fechaAgregado ASC");
    $items = [];
    while ($row = $res_items->fetch_assoc()) {
        $items[] = $row['id_carrito'];
    }
    
    $index = array_search($id_cart_actual, $items);
    
    if ($index !== false) {
        $target_index = ($accion === 'subir') ? $index - 1 : $index + 1;
        
        if ($target_index >= 0 && $target_index < count($items)) {
            $id_cart_intercambio = $items[$target_index];
            
            $res_fechas = $mysqli->query("SELECT id_carrito, fechaAgregado FROM carrito WHERE id_carrito IN ($id_cart_actual, $id_cart_intercambio)");
            $fechas = [];
            while($f = $res_fechas->fetch_assoc()) {
                $fechas[$f['id_carrito']] = $f['fechaAgregado'];
            }
            
            $stmt1 = $mysqli->prepare("UPDATE carrito SET fechaAgregado = ? WHERE id_carrito = ?");
            $stmt1->bind_param("si", $fechas[$id_cart_intercambio], $id_cart_actual);
            $stmt1->execute();
            
            $stmt2 = $mysqli->prepare("UPDATE carrito SET fechaAgregado = ? WHERE id_carrito = ?");
            $stmt2->bind_param("si", $fechas[$id_cart_actual], $id_cart_intercambio);
            $stmt2->execute();
            
            $stmt1->close();
            $stmt2->close();
        }
    }
    header("Location: Carrito.php");
    exit();
}

// Cargar elementos vinculando assets
$query = "SELECT c.id_carrito, c.id_asset, a.titulo, a.descripcion, a.precio, a.portadaurl 
          FROM carrito c 
          INNER JOIN assets a ON c.id_asset = a.id_asset 
          WHERE c.id_usuario = ? 
          ORDER BY c.fechaAgregado ASC";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$items_carrito = [];
while ($row = $resultado->fetch_assoc()) {
    $items_carrito[] = $row;
}
$stmt->close();
$total_items = count($items_carrito);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras - PolyHub</title>
    <link rel="stylesheet" href="Diseño css/EstiloPaginas.css">
</head>
<body>

    <div class="cart-wrapper-global">
        
        <a href="javascript:history.back()" class="btn-regresar">⬅️ Regresar</a>
        
        <div class="cart-panel-cabecera">
            <div class="cart-titulos">
                <span class="sub-label">Tu carrito</span>
                <h1>Resumen de Compra</h1>
            </div>
            <?php if ($total_items > 0): ?>
                <button class="btn-download-all" onclick="alert('Iniciando descarga del paquete completo (.zip)...')">Descargar todos</button>
            <?php endif; ?>
        </div>

        <?php if ($total_items === 0): ?>
            <div class="mensaje-vacio-box">
                <div class="icono-vacio">🛒</div>
                <h3>No tienes assets en el carrito</h3>
                <p>Explora poly_hub para añadir nuevos paquetes de código, modelos 3D o sprites a tu cuenta.</p>
            </div>
        <?php else: ?>
            <div class="cart-list">
                <?php foreach ($items_carrito as $index => $item): ?>
                    <div class="asset-card-horizontal">
                        
                        <img class="asset-horizontal-img" src="<?php echo htmlspecialchars($item['portadaurl'] ?? 'https://via.placeholder.com/140x90'); ?>" alt="Asset Cover">
                        
                        <div class="asset-horizontal-details">
                            <h2 class="asset-horizontal-title"><?php echo htmlspecialchars($item['titulo']); ?></h2>
                            <p class="asset-horizontal-description">
                                <?php echo htmlspecialchars($item['descripcion']); ?>
                            </p>
                            <div class="asset-horizontal-price <?php echo ($item['precio'] == 0) ? 'free' : ''; ?>">
                                <?php echo ($item['precio'] == 0) ? 'Gratis' : '$' . number_format($item['precio'], 2); ?>
                            </div>
                        </div>

                        <div class="asset-horizontal-actions">
                            <div class="arrow-level-box">
                                <?php 
                                if ($total_items > 1) {
                                    // Si no es el de arriba (index 0), puede subir de nivel
                                    if ($index > 0) {
                                        echo '<a href="Carrito.php?mover=subir&id='.$item['id_carrito'].'" class="btn-level-arrow">▲</a>';
                                    }
                                    // Si no es el último, puede bajar de nivel
                                    if ($index < $total_items - 1) {
                                        echo '<a href="Carrito.php?mover=bajar&id='.$item['id_carrito'].'" class="btn-level-arrow">▼</a>';
                                    }
                                }
                                ?>
                            </div>

                            <button class="btn-action-cart download-single" title="Instalación Individual" onclick="alert('Descargando archivo independiente del Asset...')">📥</button>

                            <a href="Carrito.php?eliminar=<?php echo $item['id_carrito']; ?>" class="btn-action-cart delete-single" title="Eliminar del Carrito" onclick="return confirm('¿Remover este Asset de tu carrito?')">🗑️</a>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>