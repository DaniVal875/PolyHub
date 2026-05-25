<?php
session_start();

// Configuración de la base de datos (Solo PDO para máxima eficiencia)
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

$mensaje = "";

// Solo procesamos si el formulario se envió por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if (!empty($correo) && !empty($contrasena)) {
        
        // Buscamos el usuario en la base de datos
        $sql = "SELECT * FROM usuarios WHERE correo = :correo LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            // Nota: En un futuro te recomiendo usar password_verify() en lugar de comparación directa "==" por seguridad
            if ($contrasena === $usuario['contrasena']) {
                
                // Guardar datos relevantes en la sesión
                $_SESSION['user_correo'] = $correo;
                $_SESSION['user_nombre'] = $usuario['nombre'] ?? '';
                $_SESSION['id_usuario'] = isset($usuario['id_usuario']) ? (int)$usuario['id_usuario'] : null;

                // Redirigir al panel principal
                header("Location: ../Vistas/Index.html");
                exit(); 
            } else {
                $mensaje = "Contraseña Incorrecta";
            }
        } else {
            $mensaje = "No Existe el Correo";
        }
    }
}
?>