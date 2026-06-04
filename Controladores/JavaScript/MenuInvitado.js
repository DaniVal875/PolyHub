(function() {
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

        #polyhub-menu-wrapper :root {
            --sidebar-width: 320px;
            --dark-bg: #51556b;
            --item-hover: #a7b3d4;
            --accent: #8dd3f6a8;
            --text-main: #ffffff;
            --text-dim: #b6b6b6;
            --nav-height: 70px;
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
            z-index: 9999;
            border-bottom: 1px solid #000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

        #polyhub-menu-wrapper .nav-item-dropdown {
            position: relative;
            padding: 20px 0;
        }

        #polyhub-menu-wrapper .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: #0f0f0f;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border: 1px solid #414141;
            padding: 15px;
            z-index: 10000;
            flex-direction: row;
            gap: 15px;
            white-space: nowrap;
        }

        #polyhub-menu-wrapper .nav-item-dropdown:hover .dropdown-content {
            display: flex;
        }

        #polyhub-menu-wrapper .dropdown-content a {
            color: #dbdbdb !important;
            font-weight: bold;
            font-size: 0.85rem;
            text-decoration: none;
            transition: color 0.2s;
        }

        #polyhub-menu-wrapper .dropdown-content a:hover {
            color: #227daaa8 !important;
            text-decoration: underline;
        }

        #polyhub-menu-wrapper .menu-btn {
            background: #2e2e2e;
            color: #efefef !important;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            z-index: 10001;
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

        #polyhub-menu-wrapper .btn-auth {
            padding: 10px 18px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            background: #2e2e2e;
            color: #efefef !important;
            transition: background 0.2s;
        }

        #polyhub-menu-wrapper .btn-auth:hover {
            background: #1f1f1f !important;
        }

        #polyhub-menu-wrapper .sidebar {
            position: fixed;
            top: 0;
            left: -320px;
            width: 320px;
            height: 100vh;
            background: #161616 !important;
            color: white !important;
            transition: left 0.3s ease;
            z-index: 10005;
            overflow-y: auto;
            padding: 25px;
            overscroll-behavior: contain;
        }

        #polyhub-menu-wrapper .sidebar.open {
            left: 0;
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

        #polyhub-menu-wrapper .group-title {
            font-size: 0.9rem;
            color: #d4d4d4;
            margin-bottom: 10px;
            margin-top: 15px;
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
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(2px);
            display: none;
            z-index: 10004;
        }

        #polyhub-menu-wrapper .overlay.active {
            display: block;
        }
    `;

    const styleSheet = document.createElement("style");
    styleSheet.innerText = css;
    document.head.appendChild(styleSheet);

    const loadNavbar = () => {
        // Guardamos la función de redirección en una constante interna limpia
        const redirigirLogin = "window.location.href='../Vistas/ComienzoDeSesion.php'";

        const navbarHTML = `
        <div id="polyhub-menu-wrapper">
            <header class="main-navbar">
                <button class="menu-btn" onclick="toggleMenu()">☰ Ver Assets</button>

                <nav class="nav-center">
                    <div class="nav-item-dropdown">
                        <span class="nav-link-main">Explorar</span>
                        <div class="dropdown-content">
                            <a href="#">Novedades</a>
                            <a href="#">Tendencias</a>
                            <a href="#">Top Ventas</a>
                        </div>
                    </div>
                    <span class="nav-link-main" onclick="${redirigirLogin}">Subir Asset</span>
                    <span class="nav-link-main" onclick="${redirigirLogin}">Lista de deseos</span>
                    
                    <span onclick="${redirigirLogin}" style="font-size: 1.5rem; cursor: pointer; margin-left: 1px;">🛒</span>
                </nav>

                <div class="top-right-container">
                    <div class="search-wrapper">
                        <input type="text" style="background-color: #242424; color: #c7c7c7" placeholder="Buscar modelos, sprites, plugins...">
                        <span style="position: absolute; right: 12px; top:7px; color: #888;">🔍</span>
                    </div>
                    <button onclick="window.location.href='../Vistas/InicioSesion.php'" class="btn-auth">Acceso</button>
                    <button onclick="window.location.href='../Vistas/CrearCuenta.php'" class="btn-auth">Registro</button>
                </div>
            </header>
            
            <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

            <nav class="sidebar" id="sidebar">
                <div class="filter-section">
                    <h1 class="section-title">Categoría</h1>
                    
                    <div class="filter-group">
                        <h3 class="group-title">2D</h3>
                        <div class="grid-2x2">
                            <span class="filter-item" onclick="${redirigirLogin}">Sprites</span>
                            <span class="filter-item" onclick="${redirigirLogin}">Tilesets</span>
                            <span class="filter-item" onclick="${redirigirLogin}">Fondos</span>
                            <span class="filter-item" onclick="${redirigirLogin}">Personajes</span>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h3 class="group-title">3D</h3>
                        <div class="grid-2x2">
                            <span class="filter-item" onclick="${redirigirLogin}">Modelos</span>
                            <span class="filter-item" onclick="${redirigirLogin}">Texturas</span>
                            <span class="filter-item" onclick="${redirigirLogin}">Low Poly</span>
                            <span class="filter-item" onclick="${redirigirLogin}">Animaciones</span>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h3 class="group-title">Audio</h3>
                        <div class="grid-2x2">
                            <span class="filter-item" onclick="${redirigirLogin}">Música</span>
                            <span class="filter-item" onclick="${redirigirLogin}">Efectos</span>
                            <span class="filter-item" onclick="${redirigirLogin}">Loops</span>
                            <span class="filter-item" onclick="${redirigirLogin}">Voces</span>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h3 class="group-title">Código</h3>
                        <div class="grid-2x2">
                            <span class="filter-item" onclick="${redirigirLogin}">Scripts</span>
                            <span class="filter-item" onclick="${redirigirLogin}">Plugins</span>
                            <span class="filter-item" onclick="${redirigirLogin}">Shaders</span>
                            <span class="filter-item" onclick="${redirigirLogin}">Plantillas</span>
                        </div>
                    </div>
                </div>

                <div class="filter-section">
                    <h1 class="section-title">Precio</h1>
                    <div class="column-layout">
                        <span class="filter-item" onclick="${redirigirLogin}">Gratis</span>
                        <span class="filter-item" onclick="${redirigirLogin}">En Oferta</span>
                        <span class="filter-item" onclick="${redirigirLogin}">De Pago</span>
                    </div>
                </div>

                <div class="filter-section">
                    <h1 class="section-title">Compatibilidad</h1>
                    <div class="column-layout">
                        <span class="filter-item" onclick="${redirigirLogin}">Unity</span>
                        <span class="filter-item" onclick="${redirigirLogin}">Unreal Engine</span>
                        <span class="filter-item" onclick="${redirigirLogin}">Godot</span>
                        <span class="filter-item" onclick="${redirigirLogin}">GameMaker</span>
                    </div>
                </div>

                <div class="filter-section">
                    <h1 class="section-title">Licencia</h1>
                    <div class="column-layout">
                        <span class="filter-item" onclick="${redirigirLogin}">Dominio Público</span>
                        <span class="filter-item" onclick="${redirigirLogin}">Uso Comercial</span>
                        <span class="filter-item" onclick="${redirigirLogin}">Atribución</span>
                    </div>
                </div>

                <div class="filter-section" style="border-bottom: none;">
                    <h1 class="section-title">Estado</h1>
                    <div class="column-layout">
                        <span class="filter-item" onclick="${redirigirLogin}">Completos</span>
                        <span class="filter-item" onclick="${redirigirLogin}">Complementos</span>
                        <span class="filter-item" onclick="${redirigirLogin}">Paquetes</span>
                    </div>
                </div>
            </nav>
        </div>
        `;
        document.body.insertAdjacentHTML('afterbegin', navbarHTML);
    };

    window.toggleMenu = function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        
        if(sidebar && overlay) {
            const isOpen = sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            
            if (isOpen) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
    };

    if (document.readyState === "complete" || document.readyState === "interactive") {
        loadNavbar();
    } else {
        document.addEventListener("DOMContentLoaded", loadNavbar);
    }
})();