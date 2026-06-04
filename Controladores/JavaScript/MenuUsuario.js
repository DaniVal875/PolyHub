(function() {
    if (window.toggleMenu) { delete window.toggleMenu; }
    if (window.toggleAvatarDropdown) { delete window.toggleAvatarDropdown; }

    const avatarUrl = window.userAvatarUrl || 'https://www.w3schools.com/howto/img_avatar.png';

    const css = `
        #polyhub-menu-wrapper div, 
        #polyhub-menu-wrapper nav, 
        #polyhub-menu-wrapper button, 
        #polyhub-menu-wrapper span, 
        #polyhub-menu-wrapper a,
        #polyhub-menu-wrapper header,
        #polyhub-menu-wrapper h1,
        #polyhub-menu-wrapper h3,
        #polyhub-menu-wrapper input {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        #polyhub-menu-wrapper .main-navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100% !important;
            height: 67px !important;
            background: #000000 !important;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: 0 25px;
            z-index: 99999 !important;
            border-bottom: 1px solid #1a1a1a;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        #polyhub-menu-wrapper .nav-center {
            display: flex;
            align-items: center;
            justify-content: flex-start; 
            gap: 30px;
            flex-grow: 1;
            margin-left: 40px; 
        }

        #polyhub-menu-wrapper .nav-link-main {
            font-weight: 800;
            font-size: 0.9rem;
            color: #e1e1e1 !important;
            text-transform: uppercase;
            cursor: pointer;
            text-decoration: none;
            transition: color 0.2s;
        }

        #polyhub-menu-wrapper .nav-link-main:hover {
            color: #227daaa8 !important;
        }

        #polyhub-menu-wrapper .menu-btn {
            background: #2e2e2e;
            color: #efefef !important;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            z-index: 100001 !important;
            transition: background 0.2s;
        }

        #polyhub-menu-wrapper .menu-btn:hover {
            background: #1f1f1f !important;
        }

        #polyhub-menu-wrapper .top-right-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        #polyhub-menu-wrapper .search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        #polyhub-menu-wrapper .search-wrapper input {
            padding: 10px 40px 10px 15px;
            border: 1px solid #717171;
            border-radius: 10px;
            width: 400px;
            height: 40px;
            outline: none;
            transition: all 0.3s;
        }

        #polyhub-menu-wrapper .search-wrapper input:focus {
            border-color: #8dd3f6a8;
            box-shadow: 0 0 8px rgba(0, 168, 255, 0.3);
        }

        #polyhub-menu-wrapper .user-avatar-container {
            position: relative;
            display: inline-block;
        }

        #polyhub-menu-wrapper .avatar-circle {
            width: 42px;
            height: 42px;
            aspect-ratio: 1 / 1;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: border-color 0.2s;
            display: block;
        }

        #polyhub-menu-wrapper .avatar-circle:hover {
            border-color: #227daaa8;
        }

        #polyhub-menu-wrapper .avatar-dropdown {
            display: none;
            position: absolute;
            top: 50px;
            right: -5px;
            background: #0f0f0f;
            min-width: 190px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.7);
            border: 1px solid #2b2b2b;
            border-radius: 10px;
            padding: 6px 0;
            z-index: 100020 !important;
            flex-direction: column;
        }

        #polyhub-menu-wrapper .avatar-dropdown.active {
            display: flex;
        }

        #polyhub-menu-wrapper .avatar-dropdown::before {
            content: '';
            position: absolute;
            top: -6px;
            right: 18px;
            width: 10px;
            height: 10px;
            background: #0f0f0f;
            border-left: 1px solid #2b2b2b;
            border-top: 1px solid #2b2b2b;
            transform: rotate(45deg);
        }

        #polyhub-menu-wrapper .avatar-dropdown a {
            color: #dbdbdb !important;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            padding: 11px 20px;
            transition: background 0.2s, color 0.2s;
            display: block;
        }

        #polyhub-menu-wrapper .avatar-dropdown a:hover {
            background: #1c1c1c;
            color: #8dd3f6 !important;
        }

        #polyhub-menu-wrapper .sidebar {
            position: fixed;
            top: 0;
            left: -320px;
            width: 320px;
            height: 100vh;
            background: #161616 !important;
            color: white !important;
            transition: left 0.3s ease-out;
            z-index: 199999 !important;
            overflow-y: auto;
            padding: 25px;
            overscroll-behavior: contain;
            box-shadow: 5px 0 15px rgba(0,0,0,0.5);
        }

        #polyhub-menu-wrapper .sidebar.open {
            left: 0 !important;
        }

        #polyhub-menu-wrapper .filter-section {
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        #polyhub-menu-wrapper .section-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: #dddddd;
            text-transform: uppercase;
        }

        #polyhub-menu-wrapper .filter-group {
            margin-bottom: 15px;
        }

        #polyhub-menu-wrapper .group-title {
            font-size: 0.9rem;
            color: #d4d4d4;
            margin-bottom: 10px;
            margin-top: 10px;
            font-weight: bold;
        }

        #polyhub-menu-wrapper .filter-item {
            font-size: 0.85rem;
            color: #949494;
            cursor: pointer;
            transition: 0.2s;
            display: inline-block;
        }

        #polyhub-menu-wrapper .filter-item:hover {
            color: #e5e5e5 !important;
            text-decoration: underline;
        }

        #polyhub-menu-wrapper .grid-2x2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding-left: 10px;
        }

        #polyhub-menu-wrapper .column-layout {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding-left: 10px;
        }

        #polyhub-menu-wrapper .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.7) !important;
            backdrop-filter: blur(3px);
            display: none;
            z-index: 199998 !important;
        }

        #polyhub-menu-wrapper .overlay.active {
            display: block !important;
        }
    `;

    const styleSheet = document.createElement("style");
    styleSheet.innerText = css;
    document.head.appendChild(styleSheet);

    const loadNavbar = () => {
        const alternatveDOM = document.getElementById('polyhub-menu-wrapper');
        if (alternatveDOM) { alternatveDOM.remove(); }

        // REMOVIDO EL TEXTO SUELTO QUE DESACOMODABA EL DISEÑO
        const navbarHTML = `
        <div id="polyhub-menu-wrapper">
            <header class="main-navbar">
                <button class="menu-btn" onclick="toggleMenu(event)">☰ Ver Assets</button>

                <nav class="nav-center">
                    <span class="nav-link-main" onclick="window.location.href='Explorar.php'">Explorar</span>
                    <span class="nav-link-main" onclick="window.location.href='SubirAssets.php'">Subir Asset</span>
                    <span class="nav-link-main">Lista de deseos</span>
                    <span onclick="window.location.href='../Vistas/Carrito.php'" style="font-size: 1.5rem; cursor: pointer; margin-left: 1px;">🛒</span>
                </nav>

                <div class="top-right-container">
                    <div class="search-wrapper">
                        <input type="text" style="background-color: #242424; color: #c7c7c7" placeholder="Buscar modelos, sprites, plugins...">
                        <span style="position: absolute; right: 12px; top:7px; color: #888;">🔍</span>
                    </div>
                    <div class="user-avatar-container">
                        <img src="${avatarUrl}" alt="Avatar" class="avatar-circle" onclick="toggleAvatarDropdown(event)">
                        <div class="avatar-dropdown" id="avatarDropdown">
                            <span style="display:block; padding: 12px 20px; color: #8dd3f6; font-size: 0.85rem; font-weight: bold; border-bottom: 1px solid #222; background: #0a0a0a; border-top-left-radius: 10px; border-top-right-radius: 10px;">Saldo: $${window.userSaldo || '0.00'}</span>
                            <a href="PerfilUsuario.php">Ver perfil</a>
                            <a href="AssetsDeUsuario.php">Mis assets</a>
                            <a href="../Controladores/Php/CerrarSesion.php">Cerrar sesión</a>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="overlay" id="overlay" onclick="toggleMenu(event)"></div>
            
            <nav class="sidebar" id="sidebar">
                <div class="filter-section">
                    <h1 class="section-title">Categoría</h1>
                    
                    <div class="filter-group">
                        <h3 class="group-title">2D</h3>
                        <div class="grid-2x2">
                            <span class="filter-item">Sprites</span>
                            <span class="filter-item">Tilesets</span>
                            <span class="filter-item">Fondos</span>
                            <span class="filter-item">Personajes</span>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h3 class="group-title">3D</h3>
                        <div class="grid-2x2">
                            <span class="filter-item">Modelos</span>
                            <span class="filter-item">Texturas</span>
                            <span class="filter-item">Low Poly</span>
                            <span class="filter-item">Animaciones</span>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h3 class="group-title">Audio</h3>
                        <div class="grid-2x2">
                            <span class="filter-item">Música</span>
                            <span class="filter-item">Efectos</span>
                            <span class="filter-item">Loops</span>
                            <span class="filter-item">Voces</span>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h3 class="group-title">Código</h3>
                        <div class="grid-2x2">
                            <span class="filter-item">Scripts</span>
                            <span class="filter-item">Plugins</span>
                            <span class="filter-item">Shaders</span>
                            <span class="filter-item">Plantillas</span>
                        </div>
                    </div>
                </div>

                <div class="filter-section">
                    <h1 class="section-title">Precio</h1>
                    <div class="column-layout">
                        <span class="filter-item">Gratis</span>
                        <span class="filter-item">En Oferta</span>
                        <span class="filter-item">De Pago</span>
                    </div>
                </div>

                <div class="filter-section">
                    <h1 class="section-title">Compatibilidad</h1>
                    <div class="column-layout">
                        <span class="filter-item">Unity</span>
                        <span class="filter-item">Unreal Engine</span>
                        <span class="filter-item">Godot</span>
                        <span class="filter-item">GameMaker</span>
                    </div>
                </div>

                <div class="filter-section">
                    <h1 class="section-title">Licencia</h1>
                    <div class="column-layout">
                        <span class="filter-item">Dominio Público</span>
                        <span class="filter-item">Uso Comercial</span>
                        <span class="filter-item">Atribución</span>
                    </div>
                </div>

                <div class="filter-section" style="border-bottom: none;">
                    <h1 class="section-title">Estado</h1>
                    <div class="column-layout">
                        <span class="filter-item">Completos</span>
                        <span class="filter-item">Complementos</span>
                        <span class="filter-item">Paquetes</span>
                    </div>
                </div>
            </nav>
        </div>
        `;

        document.body.insertAdjacentHTML('afterbegin', navbarHTML);
        
        const oldHeader = document.querySelector('body > header:not(#polyhub-menu-wrapper header)');
        if (oldHeader) oldHeader.remove();
    };

    window.toggleMenu = function(e) {
        if(e) e.stopPropagation();
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        
        if(sidebar && overlay) {
            const isOpen = sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            document.body.style.overflow = isOpen ? 'hidden' : '';
        }
    };

    window.toggleAvatarDropdown = function(e) {
        if(e) e.stopPropagation();
        const dropdown = document.getElementById('avatarDropdown');
        if(dropdown) {
            dropdown.classList.toggle('active');
        }
    };

    document.addEventListener('click', function() {
        const dropdown = document.getElementById('avatarDropdown');
        if(dropdown && dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
        }
    });

    if (document.readyState === "complete" || document.readyState === "interactive") {
        loadNavbar();
    } else {
        document.addEventListener("DOMContentLoaded", loadNavbar);
    }
})();