<?php require_once '../../Controladores/Php/NuevaContrasena.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva Contraseña - PolyHub</title>
    <link rel="stylesheet" href="../../Vistas/Diseño css/EstiloLogin.css">
</head>
<body>
    
    <main class="contenedor-acceso" role="main">
        <h1>Nueva contraseña</h1>
        
        <?php if ($errorNoSession): ?>
            <p class="mensaje-error" style="margin-bottom: 20px; text-align: center;">Error: Falta información del proceso. Vuelve a iniciar la recuperación de cuenta.</p>
            <div class="grupo-botones">
                <a href="1-RecuperacionContrasena.php" class="btn btn-secundario" style="text-align: center;">Volver al inicio</a>
            </div>
        <?php else: ?>
            <p class="subtitulo">Crea una nueva contraseña segura. Debe tener al menos 6 caracteres.</p>

            <form action="2-NuevaContrasena.php" method="post">
                
                <div class="grupo-input">
                    <label for="nuevaContrasena1">Nueva contraseña</label>
                    <input type="password" id="nuevaContrasena1" name="nuevaContrasena1" placeholder="Mínimo 6 caracteres" required>
                    <?php if ($errorMinLength): ?>
                        <p class="mensaje-error">La contraseña debe tener al menos 6 caracteres.</p>
                    <?php endif; ?>
                </div>

                <div class="grupo-input">
                    <label for="nuevaContrasena2">Confirmar contraseña</label>
                    <input type="password" id="nuevaContrasena2" name="nuevaContrasena2" placeholder="Repite la contraseña" required>
                    <?php if ($errorMismatch): ?>
                        <p class="mensaje-error">Las contraseñas no coinciden.</p>
                    <?php endif; ?>
                </div>

                <div class="grupo-botones" style="margin-top: 25px;">
                    <button type="submit" class="btn btn-principal">Actualizar contraseña</button>
                    <a href="1-RecuperacionContrasena.php" class="btn btn-secundario" style="text-align: center;">Cancelar</a>
                </div>
                
            </form>
        <?php endif; ?>
    </main>

</body>
</html>