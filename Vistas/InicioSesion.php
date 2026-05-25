<?php require_once '../Controladores/Php/InicioSesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - PolyHub</title>
    <link rel="stylesheet" href="Diseño css/EstiloLogin.css">
</head>
<body> 
    
    <main class="contenedor-acceso" role="main">
        <h1>Iniciar Sesión</h1>
        <p class="subtitulo">Bienvenido de nuevo a PolyHub</p>

        <?php if(!empty($mensaje)): ?>
            <p class="mensaje-error" style="text-align: center; margin-bottom: 15px; font-size: 1rem;">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>

        <form method="post" action="InicioSesion.php">
            
            <div class="grupo-input">
                <label for="correo">Correo electrónico</label>
                <input id="correo" type="email" name="correo" placeholder="usuario@ejemplo.com" minlength="12" maxlength="60" required>
            </div>

            <div class="grupo-input">
                <label for="contrasena">Contraseña</label>
                <input id="contrasena" type="password" name="contrasena" placeholder="Ingrese su contraseña" minlength="6" maxlength="60" required>
            </div>

            <div class="contenedor-recuperar">
                <a href="RestablecerContraseña/1-RecuperacionContrasena.php" class="enlace">¿Olvidaste tu contraseña?</a>
            </div>

            <div class="grupo-botones">
                <button type="submit" class="btn btn-principal">Iniciar Sesión</button>
            </div>

        </form>

        <p class="texto-ayuda">
            ¿No tienes una cuenta? <br> 
            <a href="CrearCuenta.php" class="enlace">¡Crea Una!</a>
        </p>

    </main>

</body>
</html>





