<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// 1. Validar control de sesión activa
if (!isset($_SESSION['user_correo'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida o caducada.']);
    exit();
}

// 2. Validar que el nuevo parámetro no venga vacío
if (!isset($_POST['nuevo_nombre']) || empty(trim($_POST['nuevo_nombre']))) {
    echo json_encode(['success' => false, 'message' => 'El nombre no puede estar vacío.']);
    exit();
}

$nuevo_nombre = trim($_POST['nuevo_nombre']);
$user_correo = $_SESSION['user_correo'];

// 3. Credenciales basadas en la arquitectura estándar de tu servidor MariaDB
$host = "localhost";
$user = "root";
$password = "";
$database = "soft_dinner";

$conexion = new mysqli($host, $user, $password, $database);

if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conexion->connect_error]);
    exit();
}

try {
    // 4. Actualizar la columna `username` usando el `email` del usuario en sesión
    $stmt = $conexion->prepare("UPDATE `usuarios` SET `username` = ? WHERE `email` = ?");
    $stmt->bind_param("ss", $nuevo_nombre, $user_correo);
    
    if ($stmt->execute()) {
        // 5. Modificar la variable de sesión activa de inmediato
        $_SESSION['user_nombre'] = $nuevo_nombre;
        
        echo json_encode(['success' => true, 'message' => 'Nombre actualizado con éxito en la base de datos.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se ejecutó la actualización en la base de datos.']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del Servidor: ' . $e->getMessage()]);
}

$conexion->close();
?>