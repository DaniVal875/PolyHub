document.addEventListener('DOMContentLoaded', () => {
    const btnReenviar = document.getElementById('reenviar');

    if (btnReenviar) {
        btnReenviar.addEventListener('click', async (e) => {
            e.preventDefault();
            
            // Cambiamos el texto temporalmente para dar feedback visual
            const textoOriginal = btnReenviar.textContent;
            btnReenviar.textContent = 'Enviando...';
            btnReenviar.style.pointerEvents = 'none'; // Evitar múltiples clics

            const formData = new FormData();
            formData.append('action', 'resend');

            try {
                // Hacemos la petición a la misma página, que es interceptada por logica_verificacion.php
                const response = await fetch('../Controladores/Php/VerificacionCuenta.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const json = await response.json();

                if (json.success) {
                    btnReenviar.textContent = '¡Código reenviado!';
                } else {
                    btnReenviar.textContent = 'Error al reenviar';
                }
            } catch (error) {
                console.error("Error en la solicitud AJAX:", error);
                btnReenviar.textContent = 'Error de conexión';
            } finally {
                // Restaurar el botón a su estado original después de 3 segundos
                setTimeout(() => {
                    btnReenviar.textContent = textoOriginal;
                    btnReenviar.style.pointerEvents = 'auto';
                }, 3000);
            }
        });
    }
});