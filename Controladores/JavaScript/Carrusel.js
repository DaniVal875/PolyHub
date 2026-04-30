document.addEventListener("DOMContentLoaded", () => {
    const dbSimuladaCarrusel = [
        "Imagenes/Carruselmg/carrusel1.png",
        "Imagenes/Carruselmg/carrusel2.png",
        "Imagenes/Carruselmg/carrusel3.png",
        "Imagenes/Carruselmg/carrusel4.png",
        "Imagenes/Carruselmg/carrusel5.png"
    ];

    const TIEMPO_AUTO = 3500;
    const TIEMPO_PAUSA = 5000;
    
    let currentIndex = 0;
    let autoPlayInterval = null;
    let pauseTimeout = null;

    const container = document.getElementById("carrusel-container");
    const prevBtn = document.getElementById("prev-btn");
    const nextBtn = document.getElementById("next-btn");

    const inicializarCarrusel = () => {
        dbSimuladaCarrusel.forEach((ruta, index) => {
            const item = document.createElement("div");
            item.className = `carrusel-item ${index === 0 ? 'active' : ''}`;
            
            item.innerHTML = `
                <div class="carrusel-bg-blur" style="background-image: url('${ruta}')"></div>
                <img src="${ruta}" class="carrusel-main-img" alt="Destacado">
            `;
            container.appendChild(item);
        });

        iniciarAutoPlay();
    };

    const cambiarImagen = (nuevoIndex) => {
        const items = document.querySelectorAll(".carrusel-item");
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

    nextBtn.addEventListener("click", () => { proxima(); resetearTemporizadores(); });
    prevBtn.addEventListener("click", () => { anterior(); resetearTemporizadores(); });

    inicializarCarrusel();
});