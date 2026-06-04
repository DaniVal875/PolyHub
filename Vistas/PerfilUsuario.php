<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si el usuario intenta entrar de forma manual y no ha iniciado sesión, se le deniega el acceso
if (!isset($_SESSION['user_correo'])) {
    header("Location: ../Vistas/InicioSesion.php");
    exit();
}

$user_name = $_SESSION['user_nombre'] ?? 'Usuario';
$user_saldo = $_SESSION['user_saldo'] ?? 0.00;
$avatar_url = $_SESSION['user_avatar'] ?? 'https://www.w3schools.com/howto/img_avatar.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Perfil - PolyHub</title>
    <link rel="stylesheet" href="Diseño css/EstiloPaginas.css">
</head>
<body>

    <div class="perfil-wrapper-global">
        
        <a href="index.php" class="btn-regresar">⬅️ Regresar al menú principal</a>

        <main class="contenedor-perfil" role="main">
            <div class="perfil-layout">
                
                <div class="perfil-izquierda">
                    <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Avatar de <?php echo htmlspecialchars($user_name); ?>" class="perfil-avatar">
                </div>

                <div class="perfil-derecha">
                    <div class="perfil-info-superior">
                        <h1 class="perfil-nombre"><?php echo htmlspecialchars($user_name); ?></h1>

                        <div class="perfil-saldo">
                            <span class="saldo-etiqueta">Saldo:</span>
                            <span class="saldo-monto">$<?php echo number_format((float)$user_saldo, 2, '.', ''); ?> MXN</span>
                        </div>
                    </div>
                    
                    <div class="perfil-acciones">
                        <a href="AssetsDeUsuario.php" class="btn-assets">📦 Ver assets</a>
                        <a href="../Controladores/Php/CerrarSesion.php" class="btn-logout">Cerrar sesión</a>
                    </div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>