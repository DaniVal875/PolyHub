/**
 * Renderiza de forma dinámica las tarjetas de assets utilizando la estructura unificada
 * @param {Array} lista 
 */
function mostrarAssetsSimulados(lista) {
    const gridContainer = document.getElementById("assets-grid");
    
    if (!gridContainer) return;

    if (!lista || lista.length === 0) {
        return;
    }

    lista.forEach(asset => {
        const card = document.createElement("div");
        card.className = "asset-card";
        // Añadimos cursor pointer para indicar que es clickeable
        card.style.cursor = "pointer"; 
        
        card.innerHTML = `
            <div class="asset-card-image-container">
                <img src="${asset.portadaurl}" alt="${asset.titulo}" class="asset-card-image">
            </div>
            
            <div class="asset-card-info">
                <div class="asset-tags text-truncate-single" title="${asset.etiquetas}">
                    ${asset.etiquetas}
                </div>
                
                <h3 class="asset-name text-truncate-single">${asset.titulo}</h3>
                
                <p class="asset-description text-truncate-multi">
                    ${asset.descripcion}
                </p>
                
                <div class="asset-creator">${asset.creador}</div>
                
                <div class="asset-type">${asset.tipo_asset}</div>
                
                <div class="asset-card-bottom">
                    <div class="asset-os">
                        <small>${asset.plataformas}</small>
                    </div>
                    <div class="asset-price ${asset.es_gratis ? 'free' : ''}">
                        ${asset.precio_formateado}
                    </div>
                </div>
            </div>
        `;

        // Evento de clic para redireccionar enviando el ID del asset por la URL
        card.addEventListener("click", () => {
            window.location.href = `VisualizarAssets.php?id=${asset.id_asset}`;
        });

        // Lógica de Zoom sutil (Reducido a un comportamiento limpio)
        const img = card.querySelector(".asset-card-image");
        let zoomTimeout;

        card.addEventListener("mouseenter", () => {
            zoomTimeout = setTimeout(() => {
                img.style.transform = "scale(1.05)";
            }, 250);
        });

        card.addEventListener("mouseleave", () => {
            clearTimeout(zoomTimeout);
            img.style.transform = "scale(1)";
        });

        gridContainer.appendChild(card);
    });
}

document.addEventListener("DOMContentLoaded", () => {
    if (typeof assetsSimulados !== 'undefined') {
        mostrarAssetsSimulados(assetsSimulados);
    }
});