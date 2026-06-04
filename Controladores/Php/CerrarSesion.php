<?php
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la cookie de sesión por completo
if (ini_get("session_use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión en el servidor
session_destroy();

// Redirigir limpiamente a la página principal del ecosistema
header("Location: ../../Vistas/index.php");
exit();
?>