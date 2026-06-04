document.addEventListener("DOMContentLoaded", () => {
    // ELIMINAMOS EL ARREGLO ESTÁTICO VIEJO DE AQUÍ.
    // Ahora dbSimuladaCarrusel se lee directamente desde lo que inyecta index.php

    // Validación de seguridad por si la base de datos está vacía o no cargó la variable
    if (typeof dbSimuladaCarrusel === 'undefined' || !dbSimuladaCarrusel || dbSimuladaCarrusel.length === 0) {
        console.warn("No hay assets disponibles o dbSimuladaCarrusel no está definida.");
        return;
    }

    const TIEMPO_AUTO = 3500;
    const TIEMPO_PAUSA = 5000;
    
    let currentIndex = 0;
    let autoPlayInterval = null;
    let pauseTimeout = null;

    const container = document.getElementById("carrusel-container");
    const prevBtn = document.getElementById("prev-btn");
    const nextBtn = document.getElementById("next-btn");

    const inicializarCarrusel = () => {
        // Limpiamos el contenedor por si acaso tiene HTML basura
        container.innerHTML = "";

        dbSimuladaCarrusel.forEach((asset, index) => {
            const item = document.createElement("div");
            item.className = `carrusel-item ${index === 0 ? 'active' : ''}`;
            
            // Redirección al visualizador real al hacer clic
            item.style.cursor = "pointer";
            item.addEventListener("click", () => {
                window.location.href = `VisualizarAssets.php?id=${asset.id}`;
            });
            
            item.innerHTML = `
                <div class="carrusel-bg-blur" style="background-image: url('${asset.ruta}')"></div>
                <img src="${asset.ruta}" class="carrusel-main-img" alt="Destacado">
            `;
            container.appendChild(item);
        });

        // Solo iniciar autoplay si hay más de una imagen
        if (dbSimuladaCarrusel.length > 1) {
            iniciarAutoPlay();
        } else {
            // Ocultar botones de navegación si solo hay 1 imagen disponible
            if (prevBtn) prevBtn.style.display = "none";
            if (nextBtn) nextBtn.style.display = "none";
        }
    };

    const cambiarImagen = (nuevoIndex) => {
        const items = document.querySelectorAll(".carrusel-item");
        if (items.length === 0) return;

        items[currentIndex].classList.remove("active");

        if (nuevoIndex >= items.length) currentIndex = 0;
        else if (nuevoIndex < 0) currentIndex = items.length - 1;
        else currentIndex = nuevoIndex;

        items[currentIndex].classList.add("active");
    };

    const proxima = () => cambiarImagen(currentIndex + 1);
    const anterior = () => cambiarImagen(currentIndex - 1);

    const iniciarAutoPlay = () => {
        if (autoPlayInterval) clearInterval(autoPlayInterval);
        autoPlayInterval = setInterval(proxima, TIEMPO_AUTO);
    };

    const resetearTemporizadores = () => {
        clearInterval(autoPlayInterval);
        if (pauseTimeout) clearTimeout(pauseTimeout);
        pauseTimeout = setTimeout(iniciarAutoPlay, TIEMPO_PAUSA);
    };

    if (nextBtn && prevBtn) {
        nextBtn.addEventListener("click", () => { proxima(); resetearTemporizadores(); });
        prevBtn.addEventListener("click", () => { anterior(); resetearTemporizadores(); });
    }

    inicializarCarrusel();
});