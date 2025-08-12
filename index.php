<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tres Bloques</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="catalogo">
        <div class="logo-nombre">
            <img src="img/logo.jpg" alt="Logo PartyExpress" class="logo-img">
            <span class="logo-text">PartyExpress</span>
        </div>
        <ul>
            <li><a href="#">Inicio</a></li>
            <li><a href="#">Fiestas</a></li>
            <li><a href="#">Lugares</a></li>
            <li><a href="#">Organizar fiesta</a></li>
            <li><a href="contacto.php">Contacto</a></li>
        </ul>
        <span class="usuario-menu-container">
            <?php if (isset($_SESSION['usuario'])): ?>
                <span style="color:#fff;">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="mis_solicitudes.php" class="menu-item">
                        <i class="fa fa-calendar-check"></i> Mis Solicitudes
                    </a>
                    <form method="POST" action="logout.php" style="margin:0;">
                        <button type="submit" class="logout-btn">Cerrar sesión</button>
                    </form>
                </div>
            <?php else: ?>
                <a href="login.html" class="login-btn">Iniciar sesión</a>
            <?php endif; ?>
        </span>
    </nav>
    
    <section class="buscador-section">
        <h1>Encuentra fiestas y lugares para celebrar</h1>
        <form class="buscador-form">
            <input type="text" placeholder="Buscar fiestas, lugares o ciudades...">
            <button type="submit">Buscar</button>
        </form>
    </section>
    
    <section class="categorias-section">
        <h2>Categorías populares</h2>
        <div class="categorias-lista">
            <div class="categoria-card">Electrónica</div>
            <div class="categoria-card">Bares</div>
            <div class="categoria-card">Fiestas privadas</div>
            <div class="categoria-card">Conciertos</div>
            <div class="categoria-card">Salones de eventos</div>
        </div>
    </section>
    
    <section class="fiestas-section">
        <h2>Lugares y fiestas destacados en Paraguay</h2>
        <div class="fiestas-lista">
            <div class="fiesta-card">
                <div class="fiesta-img"></div>
                <div class="fiesta-info"></div>
            </div>
            <div class="fiesta-card">
                <div class="fiesta-img"></div>
                <div class="fiesta-info"></div>
            </div>
            <div class="fiesta-card">
                <div class="fiesta-img"></div>
                <div class="fiesta-info"></div>
            </div>
        </div>
    </section>
    
    <section class="organiza-section">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="organizar.php" class="organiza-btn">+ Organiza tu propia fiesta</a>
        <?php else: ?>
            <a href="login.html?redirect=<?php echo urlencode('organizar.php'); ?>" class="organiza-btn">+ Organiza tu propia fiesta</a>
        <?php endif; ?>
    </section>
    
    <script>
        const menuToggle = document.getElementById("menuToggle");
        const dropdownMenu = document.getElementById("dropdownMenu");
        
        if (menuToggle && dropdownMenu) {
            menuToggle.onclick = function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            };
            
            document.addEventListener('click', function(e) {
                if (!dropdownMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                }
            });
        }
    </script>
</body>
</html> 