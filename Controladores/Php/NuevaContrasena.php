<?php
session_start();

// Configuración de la base de datos (Solo PDO optimizado)
$bdhost = "localhost";
$bduser = "root";
$bdpass = "";
$bdname = "poly_hub"; // Adaptado a tu nueva base de datos

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

$errorMinLength = false;
$errorMismatch = false;
$errorNoSession = false;

// Comprobar que exista el correo en la sesión (viene del proceso de verificación)
if (empty($_SESSION['reset_correo'])) {
    $errorNoSession = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errorNoSession) {
    $pass1 = isset($_POST['nuevaContrasena1']) ? trim($_POST['nuevaContrasena1']) : '';
    $pass2 = isset($_POST['nuevaContrasena2']) ? trim($_POST['nuevaContrasena2']) : '';

    // Validar longitud
    if (mb_strlen($pass1) < 6) {
        $errorMinLength = true;
    } 
    // Validar que ambas contraseñas coincidan
    elseif ($pass1 !== $pass2) {
        $errorMismatch = true;
    } 
    // Si todo está correcto, actualizamos la base de datos
    else {
        $correo = $_SESSION['reset_correo'];
        
        // Se cambiaron las columnas a 'password' y 'email' según la estructura de poly_hub
        $sql = "UPDATE usuarios SET password = :contrasena WHERE email = :correo";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':contrasena' => $pass1, ':correo' => $correo]);

        // Limpiar la sesión por seguridad
        unset($_SESSION['reset_correo']);
        if(isset($_SESSION['reset_codigo'])) {
            unset($_SESSION['reset_codigo']);
        }
        
        // Redirigir al login o a la pantalla de éxito
        header("Location: ../RestablecerContraseña/3-ContrasenaActualizada.php");
        exit;
    }
}
?>