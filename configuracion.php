<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html?redirect=' . urlencode('configuracion.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .configuracion-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
        }
        
        .configuracion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding: 20px 0;
            border-bottom: 2px solid rgba(162,89,247,0.2);
        }
        
        .configuracion-title {
            color: #a259f7;
            font-size: 2.5rem;
            margin: 0;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(162,89,247,0.3);
        }
        
        .btn-volver {
            background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(162,89,247,0.3);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-volver:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(162,89,247,0.4);
        }
        
        .configuracion-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .configuracion-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            border-radius: 25px;
            padding: 30px;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(162,89,247,0.2);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        
        .configuracion-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(162,89,247,0.1), transparent);
            transition: left 0.5s;
        }
        
        .configuracion-card:hover::before {
            left: 100%;
        }
        
        .configuracion-card:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: #a259f7;
            box-shadow: 0 20px 40px rgba(162,89,247,0.2);
        }
        
        .configuracion-card h3 {
            color: #a259f7;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.4rem;
            font-weight: 600;
        }
        
        .configuracion-card h3 i {
            font-size: 1.6rem;
            background: linear-gradient(135deg, #a259f7, #7209b7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .configuracion-card p {
            color: #ccc;
            line-height: 1.7;
            margin-bottom: 25px;
            font-size: 1rem;
        }
        
        .configuracion-card a {
            background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(162,89,247,0.3);
            position: relative;
            overflow: hidden;
        }
        
        .configuracion-card a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .configuracion-card a:hover::before {
            left: 100%;
        }
        
        .configuracion-card a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(162,89,247,0.4);
        }
        
        .configuracion-card:nth-child(1) { animation-delay: 0.1s; }
        .configuracion-card:nth-child(2) { animation-delay: 0.2s; }
        .configuracion-card:nth-child(3) { animation-delay: 0.3s; }
        .configuracion-card:nth-child(4) { animation-delay: 0.4s; }
        .configuracion-card:nth-child(5) { animation-delay: 0.5s; }
        .configuracion-card:nth-child(6) { animation-delay: 0.6s; }
        .configuracion-card:nth-child(7) { animation-delay: 0.7s; }
        .configuracion-card:nth-child(8) { animation-delay: 0.8s; }
        
        .configuracion-card {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }
        
        @media (max-width: 768px) {
            .configuracion-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .configuracion-title {
                font-size: 2rem;
            }
            
            .configuracion-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .configuracion-card {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <nav class="catalogo">
        <div class="logo-nombre">
            <img src="img/logo.jpg" alt="Logo PartyExpress" class="logo-img">
            <span class="logo-text">PartyExpress</span>
        </div>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="fiestas.php">Fiestas</a></li>
            <li><a href="lugares.php">Lugares</a></li>
            <li><a href="organizar.php">Organizar fiesta</a></li>
            <li><a href="contacto.php">Contacto</a></li>
        </ul>
        <span class="usuario-menu-container">
            <?php if (isset($_SESSION['usuario'])): ?>
                <div class="usuario-bienvenida">
                    <div class="usuario-avatar">
                        <?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?>
                    </div>
                    <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                </div>
                <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="mis_solicitudes.php" class="menu-item">
                        <i class="fa fa-calendar-check"></i> Mis Solicitudes
                    </a>
                    <a href="perfil.php" class="menu-item">
                        <i class="fa fa-user"></i> Mi Perfil
                    </a>
                    <a href="favoritos.php" class="menu-item">
                        <i class="fa fa-heart"></i> Favoritos
                    </a>
                    <a href="configuracion.php" class="menu-item">
                        <i class="fa fa-cog"></i> Configuración
                    </a>
                    
                    <form method="POST" action="logout.php" style="margin:0;">
                        <button type="submit" class="logout-btn">
                            <i class="fa fa-sign-out-alt"></i> Cerrar sesión
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <a href="login.html" class="login-btn">Iniciar sesión</a>
            <?php endif; ?>
        </span>
    </nav>

    <div class="configuracion-container">
        <header class="configuracion-header">
            <h1 class="configuracion-title">
                <i class="fa fa-cogs"></i> Configuración
            </h1>
            <a href="index.php" class="btn-volver">
                <i class="fa fa-home"></i> Volver al inicio
            </a>
        </header>

        <div class="configuracion-grid">
            <div class="configuracion-card" onclick="window.location.href='configuracion_unificada.php'">
                <h3><i class="fa fa-cogs"></i> Configuración Unificada</h3>
                <p>Accede a todas las opciones de configuración en una interfaz moderna y organizada.</p>
                <a href="configuracion_unificada.php">
                    <i class="fa fa-arrow-right"></i> Ir a configuración
                </a>
            </div>



            <div class="configuracion-card" onclick="window.location.href='perfil.php'">
                <h3><i class="fa fa-user-circle"></i> Mi Perfil</h3>
                <p>Gestiona tu información personal, foto de perfil y datos de cuenta.</p>
                <a href="perfil.php">
                    <i class="fa fa-arrow-right"></i> Ver perfil
                </a>
            </div>

            <div class="configuracion-card" onclick="window.location.href='seguridad.php'">
                <h3><i class="fa fa-shield-alt"></i> Seguridad</h3>
                <p>Cambia contraseñas y configura opciones de seguridad avanzadas.</p>
                <a href="seguridad.php">
                    <i class="fa fa-arrow-right"></i> Configurar seguridad
                </a>
            </div>

            <div class="configuracion-card" onclick="window.location.href='privacidad.php'">
                <h3><i class="fa fa-lock"></i> Privacidad</h3>
                <p>Controla la visibilidad de tu perfil y configuración de privacidad.</p>
                <a href="privacidad.php">
                    <i class="fa fa-arrow-right"></i> Configurar privacidad
                </a>
            </div>

            <div class="configuracion-card" onclick="window.location.href='notificaciones.php'">
                <h3><i class="fa fa-bell"></i> Notificaciones</h3>
                <p>Gestiona qué notificaciones recibir y cómo recibirlas.</p>
                <a href="notificaciones.php">
                    <i class="fa fa-arrow-right"></i> Gestionar notificaciones
                </a>
            </div>

            <div class="configuracion-card" onclick="window.location.href='preferencias.php'">
                <h3><i class="fa fa-sliders-h"></i> Preferencias</h3>
                <p>Personaliza tu experiencia en PartyExpress según tus gustos.</p>
                <a href="preferencias.php">
                    <i class="fa fa-arrow-right"></i> Configurar preferencias
                </a>
            </div>

            <div class="configuracion-card" onclick="window.location.href='ayuda.php'">
                <h3><i class="fa fa-question-circle"></i> Ayuda</h3>
                <p>Encuentra respuestas a preguntas frecuentes y guías de uso.</p>
                <a href="ayuda.php">
                    <i class="fa fa-arrow-right"></i> Ver ayuda
                </a>
            </div>

            <div class="configuracion-card" onclick="window.location.href='soporte.php'">
                <h3><i class="fa fa-headset"></i> Soporte</h3>
                <p>Contacta con nuestro equipo de soporte para obtener ayuda.</p>
                <a href="soporte.php">
                    <i class="fa fa-arrow-right"></i> Contactar soporte
                </a>
            </div>

            <div class="configuracion-card" onclick="window.location.href='acerca_de.php'">
                <h3><i class="fa fa-info-circle"></i> Acerca de</h3>
                <p>Conoce más sobre PartyExpress, nuestro equipo y misión.</p>
                <a href="acerca_de.php">
                    <i class="fa fa-arrow-right"></i> Más información
                </a>
            </div>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById("menuToggle");
        const dropdownMenu = document.getElementById("dropdownMenu");
        
        if (menuToggle && dropdownMenu) {
            menuToggle.onclick = function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
                menuToggle.classList.toggle('active');
            };
            
            document.addEventListener('click', function(e) {
                if (!dropdownMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                    menuToggle.classList.remove('active');
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && dropdownMenu.classList.contains('show')) {
                    dropdownMenu.classList.remove('show');
                    menuToggle.classList.remove('active');
                }
            });
        }

        // Efecto de hover mejorado para las tarjetas
        document.querySelectorAll('.configuracion-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>
