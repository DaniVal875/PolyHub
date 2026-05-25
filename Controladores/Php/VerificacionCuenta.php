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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$mensajeEnviado = "";
$errorCodigo = false;

// Función para enviar el código de verificación
function enviarCodigoPorCorreo($correoDestino, $codigo) {
    global $mensajeEnviado;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'eduardogarcia2024actualizado@gmail.com';
        $mail->Password   = 'abysrgcyjrpsdede';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('eduardogarcia2024actualizado@gmail.com', 'PolyHub');
        $mail->addAddress($correoDestino);

        $mail->isHTML(true);
        $mail->Subject = "Codigo de Verificacion";
        $mail->Body    = nl2br($codigo);

        $mail->send();
        $mensajeEnviado = "Correo enviado correctamente";
        return ['success' => true, 'message' => $mensajeEnviado];
    } catch (Exception $e) {
        $mensajeEnviado = "Error al enviar correo: {$mail->ErrorInfo}";
        return ['success' => false, 'message' => $mensajeEnviado];
    }
}

// Envío inicial automático si venimos del proceso de creación de cuenta
if (!empty($_SESSION['create_correo']) && !empty($_SESSION['create_codigo']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    enviarCodigoPorCorreo($_SESSION['create_correo'], $_SESSION['create_codigo']);
}

// Flujo de peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Caso inicial: llega desde Restablecer
    if (isset($_POST['correo']) && isset($_POST['codigo']) && !isset($_POST['verify']) && !isset($_POST['action'])) {
        $_SESSION['reset_correo'] = $_POST['correo'];
        $_SESSION['reset_codigo'] = $_POST['codigo'];
        enviarCodigoPorCorreo($_SESSION['reset_correo'], $_SESSION['reset_codigo']);
    }

    // 2. Caso reenvío (AJAX fetch desde el botón "Reenviar" en JS)
    if (isset($_POST['action']) && $_POST['action'] === 'resend') {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!empty($_SESSION['reset_correo']) && !empty($_SESSION['reset_codigo'])) {
            echo json_encode(enviarCodigoPorCorreo($_SESSION['reset_correo'], $_SESSION['reset_codigo']));
        } elseif (!empty($_SESSION['create_correo']) && !empty($_SESSION['create_codigo'])) {
            echo json_encode(enviarCodigoPorCorreo($_SESSION['create_correo'], $_SESSION['create_codigo']));
        } else {
            echo json_encode(['success' => false, 'message' => 'No hay datos de reenvío en la sesión.']);
        }
        exit; // Detenemos el script aquí para que solo responda JSON a JS
    }

    // 3. Caso verificación: el usuario envía el código
    if (isset($_POST['verify'])) {
        $codigoUsuario = trim($_POST['codigo_usuario'] ?? '');

        // Flujo: Restablecimiento de contraseña
        if (!empty($_SESSION['reset_codigo']) && $codigoUsuario === $_SESSION['reset_codigo']) {
            header("Location: RestablecerContraseña/2-NuevaContrasena.php");
            exit;
        }

        // Flujo: Creación de cuenta
        if (!empty($_SESSION['create_codigo'])) {
            if ($codigoUsuario === $_SESSION['create_codigo']) {
                $nombre = $_SESSION['create_nombre'] ?? '';
                $correo = $_SESSION['create_correo'] ?? '';
                $contrasena = $_SESSION['create_contrasena'] ?? '';

                if ($nombre !== '' && $correo !== '') {
                    $sql = "INSERT INTO usuarios (nombre, correo, contrasena) VALUES (:nombre, :correo, :contrasena)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':nombre' => $nombre, ':correo' => $correo, ':contrasena' => $contrasena]);

                    // Limpiar sesión
                    unset($_SESSION['create_nombre'], $_SESSION['create_correo'], $_SESSION['create_contrasena'], $_SESSION['create_codigo']);

                    header("Location: ../Vistas/ComienzoDeSesion.php");
                    exit;
                } else {
                    $errorCodigo = true;
                }
            } else {
                $errorCodigo = true;
            }
        } else {
            $errorCodigo = true;
        }
    }
}
?>