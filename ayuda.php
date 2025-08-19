<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html?redirect=' . urlencode('ayuda.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayuda - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .ayuda-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .ayuda-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .ayuda-title {
            color: #a259f7;
            font-size: 2rem;
            margin: 0;
        }
        
        .btn-volver {
            background: rgba(162,89,247,0.2);
            color: #a259f7;
            border: 1px solid rgba(162,89,247,0.4);
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .ayuda-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            margin-bottom: 25px;
        }
        
        .ayuda-card h3 {
            color: #a259f7;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .faq-item {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            margin-bottom: 15px;
            overflow: hidden;
        }
        
        .faq-pregunta {
            background: rgba(162,89,247,0.1);
            padding: 15px 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .faq-pregunta:hover {
            background: rgba(162,89,247,0.2);
        }
        
        .faq-pregunta h5 {
            color: #a259f7;
            margin: 0;
            font-size: 1rem;
        }
        
        .faq-pregunta i {
            color: #a259f7;
            transition: transform 0.3s ease;
        }
        
        .faq-pregunta.active i {
            transform: rotate(180deg);
        }
        
        .faq-respuesta {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .faq-respuesta.active {
            padding: 20px;
            max-height: 300px;
        }
        
        .faq-respuesta p {
            color: #ccc;
            line-height: 1.6;
            margin: 0;
        }
        
        .info-card {
            background: rgba(162,89,247,0.1);
            border: 1px solid rgba(162,89,247,0.3);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-card h4 {
            color: #a259f7;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .info-card p {
            color: #ccc;
            line-height: 1.6;
            margin: 0;
        }
        
        .guia-paso {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #a259f7;
        }
        
        .guia-paso h5 {
            color: #a259f7;
            margin: 0 0 10px 0;
            font-size: 1rem;
        }
        
        .guia-paso p {
            color: #ccc;
            margin: 0;
            line-height: 1.6;
        }
        
        .busqueda-container {
            margin-bottom: 30px;
        }
        
        .busqueda-input {
            width: 100%;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(162,89,247,0.3);
            border-radius: 10px;
            padding: 15px 20px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .busqueda-input:focus {
            outline: none;
            border-color: #a259f7;
            background: rgba(162,89,247,0.1);
        }
        
        .busqueda-input::placeholder {
            color: rgba(255,255,255,0.5);
        }
        
        .categoria-ayuda {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .categoria-item {
            background: rgba(162,89,247,0.1);
            border: 1px solid rgba(162,89,247,0.3);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .categoria-item:hover {
            background: rgba(162,89,247,0.2);
            transform: translateY(-2px);
        }
        
        .categoria-item i {
            font-size: 2rem;
            color: #a259f7;
            margin-bottom: 10px;
        }
        
        .categoria-item h4 {
            color: #a259f7;
            margin: 0 0 5px 0;
        }
        
        .categoria-item p {
            color: #ccc;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .contacto-ayuda {
            background: rgba(162,89,247,0.1);
            border: 1px solid rgba(162,89,247,0.3);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
        }
        
        .contacto-ayuda h4 {
            color: #a259f7;
            margin-bottom: 15px;
        }
        
        .contacto-ayuda p {
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .btn-contacto {
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-contacto:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(162,89,247,0.3);
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

    <div class="ayuda-container">
        <header class="ayuda-header">
            <h1 class="ayuda-title">Centro de Ayuda</h1>
            <a href="configuracion.php" class="btn-volver">
                <i class="fa fa-arrow-left"></i> Volver a configuración
            </a>
        </header>

        <!-- Buscador -->
        <div class="busqueda-container">
            <input type="text" class="busqueda-input" placeholder="🔍 Buscar en la ayuda..." id="busquedaAyuda">
        </div>

        <!-- Categorías de Ayuda -->
        <div class="categoria-ayuda">
            <div class="categoria-item" onclick="mostrarCategoria('eventos')">
                <i class="fa fa-calendar"></i>
                <h4>Eventos</h4>
                <p>Crear y gestionar eventos</p>
            </div>
            
            <div class="categoria-item" onclick="mostrarCategoria('cuenta')">
                <i class="fa fa-user"></i>
                <h4>Cuenta</h4>
                <p>Gestionar tu perfil</p>
            </div>
            
            <div class="categoria-item" onclick="mostrarCategoria('favoritos')">
                <i class="fa fa-heart"></i>
                <h4>Favoritos</h4>
                <p>Guardar eventos favoritos</p>
            </div>
            
            <div class="categoria-item" onclick="mostrarCategoria('tecnico')">
                <i class="fa fa-cog"></i>
                <h4>Técnico</h4>
                <p>Problemas técnicos</p>
            </div>
        </div>

        <!-- Preguntas Frecuentes -->
        <div class="ayuda-card">
            <h3><i class="fa fa-question-circle"></i> Preguntas Frecuentes</h3>
            
            <div class="faq-item">
                <div class="faq-pregunta" onclick="toggleFaq(this)">
                    <h5>¿Cómo crear un evento?</h5>
                    <i class="fa fa-chevron-down"></i>
                </div>
                <div class="faq-respuesta">
                    <p>Para crear un evento, sigue estos pasos:</p>
                    <div class="guia-paso">
                        <h5>1. Navega a "Organizar fiesta"</h5>
                        <p>Haz clic en "Organizar fiesta" en el menú principal.</p>
                    </div>
                    <div class="guia-paso">
                        <h5>2. Completa el formulario</h5>
                        <p>Llena todos los campos requeridos: título, descripción, fecha, hora, ubicación, etc.</p>
                    </div>
                    <div class="guia-paso">
                        <h5>3. Envía la solicitud</h5>
                        <p>Haz clic en "Crear evento" y espera la aprobación de nuestro equipo.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-pregunta" onclick="toggleFaq(this)">
                    <h5>¿Cómo agregar eventos a favoritos?</h5>
                    <i class="fa fa-chevron-down"></i>
                </div>
                <div class="faq-respuesta">
                    <p>Para agregar un evento a tus favoritos:</p>
                    <div class="guia-paso">
                        <h5>1. Busca el evento</h5>
                        <p>Navega por los eventos disponibles o usa la búsqueda.</p>
                    </div>
                    <div class="guia-paso">
                        <h5>2. Haz clic en el corazón</h5>
                        <p>En la página del evento, busca el botón de corazón y haz clic en él.</p>
                    </div>
                    <div class="guia-paso">
                        <h5>3. Verifica en favoritos</h5>
                        <p>El evento aparecerá en tu sección de "Favoritos" del menú.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-pregunta" onclick="toggleFaq(this)">
                    <h5>¿Cómo cambiar mi foto de perfil?</h5>
                    <i class="fa fa-chevron-down"></i>
                </div>
                <div class="faq-respuesta">
                    <p>Para cambiar tu foto de perfil:</p>
                    <div class="guia-paso">
                        <h5>1. Ve a tu perfil</h5>
                        <p>Haz clic en "Mi Perfil" en el menú desplegable.</p>
                    </div>
                    <div class="guia-paso">
                        <h5>2. Haz clic en "Cambiar foto"</h5>
                        <p>Busca el botón "Cambiar foto" debajo de tu foto actual.</p>
                    </div>
                    <div class="guia-paso">
                        <h5>3. Selecciona una imagen</h5>
                        <p>Elige una imagen desde tu dispositivo (JPG, PNG, GIF).</p>
                    </div>
                    <div class="guia-paso">
                        <h5>4. Sube la foto</h5>
                        <p>Haz clic en "Subir foto" y espera a que se procese.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-pregunta" onclick="toggleFaq(this)">
                    <h5>¿Cómo buscar eventos específicos?</h5>
                    <i class="fa fa-chevron-down"></i>
                </div>
                <div class="faq-respuesta">
                    <p>Para buscar eventos específicos:</p>
                    <div class="guia-paso">
                        <h5>1. Usa la barra de búsqueda</h5>
                        <p>En la página principal, escribe palabras clave en la barra de búsqueda.</p>
                    </div>
                    <div class="guia-paso">
                        <h5>2. Aplica filtros</h5>
                        <p>Usa los filtros por categoría, fecha, ubicación o precio.</p>
                    </div>
                    <div class="guia-paso">
                        <h5>3. Explora categorías</h5>
                        <p>Navega por las diferentes categorías de eventos disponibles.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-pregunta" onclick="toggleFaq(this)">
                    <h5>¿Qué hacer si olvidé mi contraseña?</h5>
                    <i class="fa fa-chevron-down"></i>
                </div>
                <div class="faq-respuesta">
                    <p>Si olvidaste tu contraseña:</p>
                    <div class="guia-paso">
                        <h5>1. Ve a la página de login</h5>
                        <p>Haz clic en "Iniciar sesión" en el menú.</p>
                    </div>
                    <div class="guia-paso">
                        <h5>2. Haz clic en "¿Olvidaste tu contraseña?"</h5>
                        <p>Busca este enlace en la página de login.</p>
                    </div>
                    <div class="guia-paso">
                        <h5>3. Ingresa tu email</h5>
                        <p>Escribe la dirección de email asociada a tu cuenta.</p>
                    </div>
                    <div class="guia-paso">
                        <h5>4. Sigue las instrucciones</h5>
                        <p>Revisa tu email y sigue las instrucciones para restablecer tu contraseña.</p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-pregunta" onclick="toggleFaq(this)">
                    <h5>¿Cómo reportar un problema?</h5>
                    <i class="fa fa-chevron-down"></i>
                </div>
                <div class="faq-respuesta">
                    <p>Para reportar un problema:</p>
                    <div class="guia-paso">
                        <h5>1. Ve a la sección de soporte</h5>
                        <p>Haz clic en "Soporte" en la configuración.</p>
                    </div>
                    <div class="guia-paso">
                        <h5>2. Contacta al equipo</h5>
                        <p>Usa el email o teléfono de soporte disponible.</p>
                    </div>
                    <div class="guia-paso">
                        <h5>3. Proporciona detalles</h5>
                        <p>Incluye una descripción detallada del problema que estás experimentando.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guías Rápidas -->
        <div class="ayuda-card">
            <h3><i class="fa fa-book"></i> Guías Rápidas</h3>
            
            <div class="info-card">
                <h4>Primeros Pasos</h4>
                <p>Si es tu primera vez usando PartyExpress, te recomendamos:</p>
                <div class="guia-paso">
                    <h5>1. Completa tu perfil</h5>
                    <p>Agrega una foto y completa tu información personal.</p>
                </div>
                <div class="guia-paso">
                    <h5>2. Explora eventos</h5>
                    <p>Navega por los eventos disponibles y agrega algunos a favoritos.</p>
                </div>
                <div class="guia-paso">
                    <h5>3. Crea tu primer evento</h5>
                    <p>Organiza tu primera fiesta usando nuestro formulario simple.</p>
                </div>
            </div>
            
            <div class="info-card">
                <h4>Consejos para Organizar Eventos</h4>
                <p>Para que tu evento sea exitoso:</p>
                <div class="guia-paso">
                    <h5>• Planifica con anticipación</h5>
                    <p>Publica tu evento al menos 2 semanas antes.</p>
                </div>
                <div class="guia-paso">
                    <h5>• Incluye detalles completos</h5>
                    <p>Describe bien tu evento, incluye fotos y especifica el dress code.</p>
                </div>
                <div class="guia-paso">
                    <h5>• Mantén la comunicación</h5>
                    <p>Responde comentarios y actualiza información cuando sea necesario.</p>
                </div>
            </div>
        </div>

        <!-- Información de Contacto -->
        <div class="contacto-ayuda">
            <h4>¿No encontraste lo que buscabas?</h4>
            <p>Nuestro equipo de soporte está aquí para ayudarte con cualquier pregunta o problema que tengas.</p>
            <a href="soporte.php" class="btn-contacto">
                <i class="fa fa-headset"></i> Contactar Soporte
            </a>
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

        // Funciones para la ayuda
        function toggleFaq(element) {
            const respuesta = element.nextElementSibling;
            const icon = element.querySelector('i');
            
            // Toggle active class
            element.classList.toggle('active');
            respuesta.classList.toggle('active');
        }

        function mostrarCategoria(categoria) {
            // Aquí se implementaría la lógica para mostrar diferentes categorías
            console.log('Mostrando categoría:', categoria);
            
            // Por ahora, simplemente hacemos scroll a las FAQ
            document.querySelector('.faq-item').scrollIntoView({ 
                behavior: 'smooth' 
            });
        }

        // Buscador de ayuda
        document.getElementById('busquedaAyuda').addEventListener('input', function(e) {
            const busqueda = e.target.value.toLowerCase();
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const pregunta = item.querySelector('h5').textContent.toLowerCase();
                const respuesta = item.querySelector('p').textContent.toLowerCase();
                
                if (pregunta.includes(busqueda) || respuesta.includes(busqueda)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
