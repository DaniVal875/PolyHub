<?php require_once '../../Controladores/Php/RestablecerContrasena.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer Contraseña - PolyHub</title>
    <link rel="stylesheet" href="../../Vistas/Diseño css/EstiloLogin.css">
</head>
<body>
    
    <main class="contenedor-acceso" role="main">
        <h1>Recuperar cuenta</h1>
        <p class="subtitulo">Ingresa tu correo electrónico asociado y te enviaremos un código de verificación.</p>

        <form action="1-RecuperacionContrasena.php" method="post">
            
            <div class="grupo-input">
                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" value="<?php echo htmlspecialchars($correoValor); ?>" required>
                
                <?php if ($errorEmail): ?>
                    <p class="mensaje-error">Este correo no se encuentra registrado.</p>
                <?php endif; ?>
            </div>

            <div class="grupo-botones" style="margin-top: 25px;">
                <button type="submit" class="btn btn-principal">Confirmar correo</button>
                
                <a href="../../Vistas/InicioSesion.php" class="btn btn-secundario" style="text-align: center;">Cancelar</a>
            </div>
            
        </form>
    </main>

</body>
</html>