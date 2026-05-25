<?php
session_start();

// Configuración de la base de datos (Usando solo PDO para optimizar recursos y seguridad)
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

// Inicialización de variables para la vista
$mensaje = "";
$contrasenaMensaje = "";
$nombre = "";
$correo = "";
$contrasena = "";
$contrasenaConfirmar = "";

// Solo ejecutamos la lógica si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $contrasenaConfirmar = $_POST['confirmar'] ?? '';

    if ($nombre && $correo && $contrasena && $contrasenaConfirmar) {
        
        // Verificar si el correo ya existe (Optimizamos la consulta seleccionando solo el ID)
        $sql = "SELECT id_usuario FROM usuarios WHERE correo = :correo LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        
        if ($stmt->fetch()) {
            $mensaje = "Este correo ya está en uso.";
        } else if ($contrasenaConfirmar !== $contrasena) {
            $contrasenaMensaje = "Las contraseñas no coinciden.";
        } else {
            // Generar código y guardar datos temporales en sesión
            $codigo = random_int(100000, 999999);
            $_SESSION['create_nombre'] = $nombre;
            $_SESSION['create_correo'] = $correo;
            $_SESSION['create_contrasena'] = $contrasena;
            $_SESSION['create_codigo'] = (string)$codigo;

            // Redirigir a la página de verificación
            header("Location: ../Vistas/VerificacionCuenta.php");
            exit();
        }
    }
}
?>