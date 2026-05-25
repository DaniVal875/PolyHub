<?php 
session_start();

// Configuración de la base de datos (Solo PDO optimizado)
$bdhost = "localhost";
$bduser = "root";
$bdpass = "";
$bdname = "soft_dinner";

$dsn = "mysql:host=$bdhost;dbname=$bdname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $bduser, $bdpass, $options);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$errorEmail = false;
$correoValor = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correoValor = isset($_POST['correo']) ? trim($_POST['correo']) : '';

    if ($correoValor === '') {
        $errorEmail = true;
    } else {
        // Comprobar si el correo existe en la BD
        $sql = "SELECT id_usuario FROM usuarios WHERE correo = :correo LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':correo' => $correoValor]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            // Correo no registrado
            $errorEmail = true;
        } else {
            // Correo registrado: generar código y reenviar mediante POST a la vista de Verificación
            $codigo = random_int(100000, 999999);
            
            // Crear formulario oculto que se auto-envía para mantener POST
            // (Se le añade un pequeño estilo oscuro por si el usuario alcanza a ver la transición)
            echo '<!DOCTYPE html><html><head><title>Procesando...</title></head>';
            echo '<body style="background-color: #0b0c10; color: #fff; text-align: center; padding-top: 20%; font-family: sans-serif;">';
            echo '<h2>Preparando código de verificación...</h2>';
            
            // NOTA: Asegúrate de que esta ruta apunte a tu archivo de verificación actual
            echo '<form id="forwardForm" action="../VerificacionCuenta.php" method="post">';
            echo '<input type="hidden" name="correo" value="'.htmlspecialchars($correoValor, ENT_QUOTES).'">';
            echo '<input type="hidden" name="codigo" value="'.htmlspecialchars($codigo, ENT_QUOTES).'">';
            echo '</form>';
            echo '<script>document.getElementById("forwardForm").submit();</script>';
            echo '</body></html>';
            exit;
        }
    }
}
?>