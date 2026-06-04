<?php require_once '../Controladores/Php/Registros.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear Cuenta - PolyHub</title>
    <link rel="stylesheet" href="Diseño css/EstiloLogin.css">
</head>
<body>
    
    <main class="contenedor-acceso" role="main">
        <h1>Crear cuenta</h1>
        <p class="subtitulo">Únete a la comunidad de PolyHub</p>

        <form method="post" action="">
            
            <div class="grupo-input">
                <label for="nombre">Nombre de usuario</label>
                <input id="nombre" name="nombre" type="text" placeholder="Escriba su nombre de usuario" value="<?php echo htmlspecialchars($nombre); ?>" required>
            </div>

            <div class="grupo-input">
                <label for="correo">Correo electrónico</label>
                <input id="correo" name="correo" type="email" placeholder="usuario@ejemplo.com" value="<?php echo htmlspecialchars($correo); ?>" required>
                <?php if(!empty($mensaje)): ?>
                    <p class="mensaje-error"><?php echo $mensaje; ?></p>
                <?php endif; ?>
            </div>

            <div class="grupo-input">
                <label for="contrasena">Contraseña</label>
                <input id="contrasena" name="contrasena" type="password" placeholder="Mínimo 6 caracteres" minlength="6" value="<?php echo htmlspecialchars($contrasena); ?>" required>
            </div>

            <div class="grupo-input">
                <label for="confirmar">Confirmar contraseña</label>
                <input id="confirmar" name="confirmar" type="password" placeholder="Repite la contraseña" minlength="6" value="<?php echo htmlspecialchars($contrasenaConfirmar); ?>" required>
                <?php if(!empty($contrasenaMensaje)): ?>
                    <p class="mensaje-error"><?php echo $contrasenaMensaje; ?></p>
                <?php endif; ?>
            </div>

            <div class="grupo-botones" style="margin-top: 15px;">
                <button type="submit" class="btn btn-principal">Registrarse</button>
            </div>

        </form>

        <p class="texto-ayuda">
            ¿Ya tienes una cuenta? <br> 
            <a href="InicioSesion.php" class="enlace">¡Inicia Sesión!</a>
        </p>

    </main>

</body>
</html>