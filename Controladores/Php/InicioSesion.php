<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de la base de datos
$bdhost = "localhost";
$bduser = "root";
$bdpass = "";
$bdname = "poly_hub"; 

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
        
        // Buscamos al usuario utilizando 'email' según la estructura de poly_hub
        $sql = "SELECT * FROM usuarios WHERE email = :correo LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            // Validación de contraseña directa (tal como lo manejas en tu base de datos)
            if ($contrasena === $usuario['password']) {
                
                // ==========================================
                // VARIABLES DE SESIÓN FIJADAS PARA EL INDEX
                // ==========================================
                $_SESSION['user_correo'] = $usuario['email'];
                $_SESSION['user_nombre'] = $usuario['username'] ?? '';
                $_SESSION['id_usuario']  = (int)$usuario['id_usuario'];
                
                // Sincronización inmediata de datos dinámicos del menú
                $_SESSION['user_avatar'] = !empty($usuario['avatar_url']) ? $usuario['avatar_url'] : 'uploads/avatars/default.png';
                $_SESSION['user_saldo']  = number_format($usuario['saldo'], 2, '.', '');

                // Redirigir limpiamente al index principal
                header("Location: ../Vistas/index.php");
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