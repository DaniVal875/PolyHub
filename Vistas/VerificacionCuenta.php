<?php require_once '../Controladores/Php/VerificacionCuenta.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación - PolyHub</title>
    <link rel="stylesheet" href="Diseño css/EstiloLogin.css">
</head>
<body>
    
    <main class="contenedor-acceso" role="main">
        <h1>Verificación</h1>
        <p class="subtitulo">Se envió un código de verificación a su correo</p>

        <form action="VerificacionCuenta.php" method="post" id="verifyForm">
            
            <div class="grupo-input">
                <label for="codigo">Código de 6 dígitos</label>
                <input type="text" id="codigo" name="codigo_usuario" maxlength="6" pattern="\d{6}" inputmode="numeric" placeholder="123456" required style="text-align: center; font-size: 1.5rem; letter-spacing: 5px;">
                
                <?php if ($errorCodigo): ?>
                    <p class="mensaje-error" style="text-align: center; margin-top: 10px;">Código incorrecto. Inténtalo de nuevo.</p>
                <?php endif; ?>
            </div>

            <input type="hidden" name="verify" value="1">
            
            <div class="grupo-botones" style="margin-top: 20px;">
                <button type="submit" class="btn btn-principal">Confirmar código</button>
            </div>
            
        </form>

        <p class="texto-ayuda">
            ¿No recibiste el código? <br>
            <a href="#" id="reenviar" class="enlace">Reenviar código</a>
        </p>
    </main>

    <script src="../Controladores/JavaScript/ReenvioCodigo.js"></script>
</body>
</html>