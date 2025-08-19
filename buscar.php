<?php
session_start();
require_once 'conexion.php';

// Obtener término de búsqueda
$termino = isset($_GET['q']) ? trim($_GET['q']) : '';
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$ubicacion = isset($_GET['ubicacion']) ? $_GET['ubicacion'] : '';

// Realizar búsqueda
$resultados = [];
$total_resultados = 0;

if (!empty($termino) || !empty($categoria) || !empty($ubicacion)) {
    try {
        $conn = obtenerConexion();
        
        // Construir consulta dinámica
        $sql = '
            SELECT 
                se.id,
                se.titulo,
                se.descripcion,
                se.ubicacion,
                se.fecha_evento,
                se.hora_evento,
                se.capacidad,
                se.presupuesto,
                se.privacidad,
                se.estado,
                se.fecha_creacion,
                u.nombre as organizador_nombre,
                u.usuario as organizador_usuario,
                ce.nombre as categoria_nombre,
                ce.icono as categoria_icono,
                ce.color as categoria_color,
                COUNT(aa.id) as total_archivos
            FROM solicitudes_eventos se
            JOIN usuarios u ON se.usuario_id = u.id
            JOIN categorias_eventos ce ON se.categoria_id = ce.id
            LEFT JOIN archivos_adjuntos aa ON se.id = aa.solicitud_id
            WHERE se.estado = "Aprobado" AND se.privacidad = "Público"
        ';
        
        $params = [];
        $types = '';
        
        // Agregar filtros
        if (!empty($termino)) {
            $sql .= ' AND (se.titulo LIKE ? OR se.descripcion LIKE ? OR se.ubicacion LIKE ?)';
            $search_term = '%' . $termino . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= 'sss';
        }
        
        if (!empty($categoria)) {
            $sql .= ' AND ce.nombre = ?';
            $params[] = $categoria;
            $types .= 's';
        }
        
        if (!empty($ubicacion)) {
            $sql .= ' AND se.ubicacion LIKE ?';
            $params[] = '%' . $ubicacion . '%';
            $types .= 's';
        }
        
        $sql .= ' GROUP BY se.id ORDER BY se.fecha_evento ASC';
        
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $resultados[] = $row;
        }
        
        $total_resultados = count($resultados);
        
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        // Error en la base de datos
    }
}

// Obtener categorías para el filtro
$categorias = [];
try {
    $conn = obtenerConexion();
    $stmt = $conn->prepare('SELECT nombre FROM categorias_eventos WHERE activa = TRUE ORDER BY nombre');
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row['nombre'];
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Error en la base de datos
}

function formatearFecha($fecha) {
    return date('d/m/Y', strtotime($fecha));
}

function formatearHora($hora) {
    return date('H:i', strtotime($hora));
}

function formatearPresupuesto($presupuesto) {
    if (!$presupuesto) return 'No especificado';
    return 'Gs. ' . number_format($presupuesto, 0, ',', '.');
}

function obtenerClaseCategoria($categoria) {
    switch ($categoria) {
        case 'Cumpleaños': return 'categoria-cumpleanos';
        case 'Boda': return 'categoria-boda';
        case 'Graduación': return 'categoria-graduacion';
        case 'Aniversario': return 'categoria-aniversario';
        case 'Evento Corporativo': return 'categoria-corporativo';
        case 'Fiesta Temática': return 'categoria-tematica';
        case 'Baby Shower': return 'categoria-babyshower';
        case 'Despedida': return 'categoria-despedida';
        default: return 'categoria-otro';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Búsqueda - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .busqueda-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .busqueda-header {
            margin-bottom: 30px;
        }
        
        .busqueda-titulo {
            color: #a259f7;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .busqueda-subtitulo {
            color: #ccc;
            font-size: 1.1rem;
        }
        
        .filtros-busqueda {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            margin-bottom: 30px;
        }
        
        .filtros-form {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .filtro-grupo {
            display: flex;
            flex-direction: column;
        }
        
        .filtro-label {
            color: #a259f7;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .filtro-input {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(162,89,247,0.3);
            border-radius: 10px;
            padding: 12px 15px;
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .filtro-input:focus {
            outline: none;
            border-color: #a259f7;
            background: rgba(162,89,247,0.1);
        }
        
        .filtro-select {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(162,89,247,0.3);
            border-radius: 10px;
            padding: 12px 15px;
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .filtro-select:focus {
            outline: none;
            border-color: #a259f7;
            background: rgba(162,89,247,0.1);
        }
        
        .filtro-select option {
            background: #2D1950;
            color: #fff;
        }
        
        .btn-buscar {
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-buscar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(162,89,247,0.3);
        }
        
        .resultados-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .resultados-count {
            color: #a259f7;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .resultados-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .fiesta-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .fiesta-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
        }
        
        .fiesta-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 70px rgba(0,0,0,0.4);
            border-color: #a259f7;
        }
        
        .fiesta-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .fiesta-titulo {
            color: #a259f7;
            font-size: 1.4rem;
            font-weight: 600;
            margin: 0;
            flex: 1;
        }
        
        .categoria-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 10px;
        }
        
        .categoria-cumpleanos {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(255, 107, 107, 0.4);
        }
        
        .categoria-boda {
            background: rgba(255, 159, 243, 0.2);
            color: #ff9ff3;
            border: 1px solid rgba(255, 159, 243, 0.4);
        }
        
        .categoria-graduacion {
            background: rgba(84, 160, 255, 0.2);
            color: #54a0ff;
            border: 1px solid rgba(84, 160, 255, 0.4);
        }
        
        .categoria-aniversario {
            background: rgba(95, 39, 205, 0.2);
            color: #5f27cd;
            border: 1px solid rgba(95, 39, 205, 0.4);
        }
        
        .categoria-corporativo {
            background: rgba(0, 210, 211, 0.2);
            color: #00d2d3;
            border: 1px solid rgba(0, 210, 211, 0.4);
        }
        
        .categoria-tematica {
            background: rgba(255, 159, 67, 0.2);
            color: #ff9f43;
            border: 1px solid rgba(255, 159, 67, 0.4);
        }
        
        .categoria-babyshower {
            background: rgba(165, 94, 234, 0.2);
            color: #a55eea;
            border: 1px solid rgba(165, 94, 234, 0.4);
        }
        
        .categoria-despedida {
            background: rgba(38, 222, 129, 0.2);
            color: #26de81;
            border: 1px solid rgba(38, 222, 129, 0.4);
        }
        
        .categoria-otro {
            background: rgba(162, 89, 247, 0.2);
            color: #a259f7;
            border: 1px solid rgba(162, 89, 247, 0.4);
        }
        
        .fiesta-descripcion {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .fiesta-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-icon {
            color: #a259f7;
            width: 18px;
            text-align: center;
        }
        
        .info-label {
            color: #a259f7;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: #fff;
            font-size: 0.9rem;
        }
        
        .fiesta-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .organizador {
            color: #888;
            font-size: 0.85rem;
        }
        
        .organizador strong {
            color: #a259f7;
        }
        
        .fecha-evento {
            color: #28a745;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .ver-mas-btn {
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
        }
        
        .ver-mas-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(162,89,247,0.3);
        }
        
        .sin-resultados {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        
        .sin-resultados i {
            font-size: 4rem;
            color: #a259f7;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .sin-resultados h3 {
            color: #a259f7;
            margin-bottom: 10px;
        }
        
        .sugerencias {
            margin-top: 20px;
            padding: 20px;
            background: rgba(162,89,247,0.1);
            border-radius: 15px;
            border: 1px solid rgba(162,89,247,0.3);
        }
        
        .sugerencias h4 {
            color: #a259f7;
            margin-bottom: 10px;
        }
        
        .sugerencias ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sugerencias li {
            color: #ccc;
            margin-bottom: 5px;
            padding-left: 20px;
            position: relative;
        }
        
        .sugerencias li::before {
            content: '•';
            color: #a259f7;
            position: absolute;
            left: 0;
        }
        
        @media (max-width: 768px) {
            .filtros-form {
                grid-template-columns: 1fr;
            }
            
            .resultados-info {
                flex-direction: column;
                align-items: stretch;
            }
            
            .resultados-grid {
                grid-template-columns: 1fr;
            }
            
            .fiesta-info {
                grid-template-columns: 1fr;
            }
            
            .fiesta-footer {
                flex-direction: column;
                align-items: stretch;
            }
        }
        
        /* Estilos del Footer */
        .footer-section {
            background: rgba(45, 25, 80, 0.9);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(162,89,247,0.3);
            padding: 40px 0 20px;
            margin-top: 60px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .footer-section h3 {
            color: #a259f7;
            font-size: 1.2rem;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .footer-section p {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-section ul li {
            margin-bottom: 8px;
        }
        
        .footer-section ul li a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-section ul li a:hover {
            color: #a259f7;
        }
        
        .redes-sociales {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .red-social {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ccc;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 1px solid rgba(162,89,247,0.2);
        }
        
        .red-social:hover {
            background: rgba(162,89,247,0.1);
            color: #a259f7;
            border-color: #a259f7;
            transform: translateY(-2px);
        }
        
        .red-social i {
            font-size: 1.2rem;
            color: #a259f7;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(162,89,247,0.2);
            margin-top: 30px;
            padding-top: 20px;
            text-align: center;
        }
        
        .footer-bottom p {
            color: #888;
            font-size: 0.9rem;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
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
                        <?php 
                        // Obtener foto de perfil del usuario
                        $foto_perfil = null;
                        try {
                            $conn = obtenerConexion();
                            $stmt = $conn->prepare('SELECT foto_perfil FROM usuarios WHERE id = ?');
                            $stmt->bind_param('i', $_SESSION['usuario_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                $foto_perfil = $result->fetch_assoc()['foto_perfil'];
                            }
                            $stmt->close();
                            $conn->close();
                        } catch (Exception $e) {
                            // Silenciar errores
                        }
                        
                        if ($foto_perfil): ?>
                            <img src="uploads/perfiles/<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil">
                        <?php else: ?>
                            <?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                </div>
                <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
                <div class="dropdown-menu" id="dropdownMenu">
                    <!-- Header con información del usuario -->
                    <div class="menu-header">
                        <div class="menu-user-info">
                            <div class="menu-user-avatar">
                                <?php if ($foto_perfil): ?>
                                    <img src="uploads/perfiles/<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div class="menu-user-details">
                                <h4><?php echo htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['usuario']); ?></h4>
                                <p><?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
                            </div>
                        </div>
                        <div class="menu-stats">
                            <div class="menu-stat">
                                <span class="menu-stat-number">3</span>
                                <span class="menu-stat-label">Eventos</span>
                            </div>
                            <div class="menu-stat">
                                <span class="menu-stat-number">12</span>
                                <span class="menu-stat-label">Días</span>
                            </div>
                            <div class="menu-stat">
                                <span class="menu-stat-number">5</span>
                                <span class="menu-stat-label">Favoritos</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de notificaciones -->
                    <div class="menu-notifications">
                        <div class="menu-notifications-header">
                            <h5 class="menu-notifications-title">Notificaciones</h5>
                            <div class="notification-badge">2</div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fa fa-bell"></i>
                            </div>
                            <div class="notification-content">
                                <h5>Nueva solicitud aprobada</h5>
                                <p>Tu evento "Fiesta de Cumpleaños" fue aprobado</p>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fa fa-heart"></i>
                            </div>
                            <div class="notification-content">
                                <h5>Nuevo lugar disponible</h5>
                                <p>Parque Acuático Aqualandia ahora disponible</p>
                            </div>
                        </div>
                    </div>

                    <!-- Enlaces del menú -->
                    <a href="mis_solicitudes.php" class="menu-item">
                        <i class="fa fa-calendar-check"></i> Mis Solicitudes
                        <span class="menu-item-badge">3</span>
                    </a>
                    <a href="perfil.php" class="menu-item">
                        <i class="fa fa-user"></i> Mi Perfil
                    </a>
                    <a href="favoritos.php" class="menu-item">
                        <i class="fa fa-heart"></i> Favoritos
                        <span class="menu-item-badge">5</span>
                    </a>
                    <a href="configuracion.php" class="menu-item">
                        <i class="fa fa-cog"></i> Configuración
                    </a>
                    
                    <!-- Botón de cerrar sesión -->
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

    <div class="busqueda-container">
        <header class="busqueda-header">
            <h1 class="busqueda-titulo">Resultados de Búsqueda</h1>
            <p class="busqueda-subtitulo">
                <?php if (!empty($termino) || !empty($categoria) || !empty($ubicacion)): ?>
                    Buscando eventos en PartyExpress
                <?php else: ?>
                    Busca fiestas, lugares o ciudades
                <?php endif; ?>
            </p>
        </header>

        <div class="filtros-busqueda">
            <form class="filtros-form" method="GET" action="buscar.php">
                <div class="filtro-grupo">
                    <label class="filtro-label">Buscar</label>
                    <input type="text" name="q" class="filtro-input" placeholder="Fiestas, lugares, ciudades..." value="<?php echo htmlspecialchars($termino); ?>">
                </div>
                
                <div class="filtro-grupo">
                    <label class="filtro-label">Categoría</label>
                    <select name="categoria" class="filtro-select">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($categoria === $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filtro-grupo">
                    <label class="filtro-label">Ubicación</label>
                    <input type="text" name="ubicacion" class="filtro-input" placeholder="Ciudad, barrio..." value="<?php echo htmlspecialchars($ubicacion); ?>">
                </div>
                
                <button type="submit" class="btn-buscar">
                    <i class="fa fa-search"></i>
                    Buscar
                </button>
            </form>
        </div>

        <?php if (!empty($termino) || !empty($categoria) || !empty($ubicacion)): ?>
            <div class="resultados-info">
                <div class="resultados-count">
                    <?php if ($total_resultados > 0): ?>
                        Se encontraron <?php echo $total_resultados; ?> resultado<?php echo $total_resultados !== 1 ? 's' : ''; ?>
                    <?php else: ?>
                        No se encontraron resultados
                    <?php endif; ?>
                </div>
                
                <a href="index.php" class="btn-buscar" style="text-decoration: none;">
                    <i class="fa fa-home"></i>
                    Volver al Inicio
                </a>
            </div>

            <?php if ($total_resultados > 0): ?>
                <div class="resultados-grid">
                    <?php foreach ($resultados as $fiesta): ?>
                        <a href="fiesta_detalle.php?id=<?php echo $fiesta['id']; ?>" class="fiesta-card">
                            <div class="fiesta-header">
                                <h3 class="fiesta-titulo"><?php echo htmlspecialchars($fiesta['titulo']); ?></h3>
                                <span class="categoria-badge <?php echo obtenerClaseCategoria($fiesta['categoria_nombre']); ?>">
                                    <i class="fa <?php echo htmlspecialchars($fiesta['categoria_icono']); ?>"></i>
                                    <?php echo htmlspecialchars($fiesta['categoria_nombre']); ?>
                                </span>
                            </div>
                            
                            <p class="fiesta-descripcion"><?php echo htmlspecialchars($fiesta['descripcion']); ?></p>
                            
                            <div class="fiesta-info">
                                <div class="info-item">
                                    <i class="fa fa-map-marker-alt info-icon"></i>
                                    <span class="info-label">Ubicación:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($fiesta['ubicacion']); ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <i class="fa fa-calendar info-icon"></i>
                                    <span class="info-label">Fecha:</span>
                                    <span class="info-value"><?php echo formatearFecha($fiesta['fecha_evento']); ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <i class="fa fa-clock info-icon"></i>
                                    <span class="info-label">Hora:</span>
                                    <span class="info-value"><?php echo formatearHora($fiesta['hora_evento']); ?></span>
                                </div>
                                
                                <?php if ($fiesta['capacidad']): ?>
                                <div class="info-item">
                                    <i class="fa fa-users info-icon"></i>
                                    <span class="info-label">Capacidad:</span>
                                    <span class="info-value"><?php echo $fiesta['capacidad']; ?> personas</span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($fiesta['presupuesto']): ?>
                                <div class="info-item">
                                    <i class="fa fa-money-bill info-icon"></i>
                                    <span class="info-label">Presupuesto:</span>
                                    <span class="info-value"><?php echo formatearPresupuesto($fiesta['presupuesto']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="fiesta-footer">
                                <div class="organizador">
                                    Organizado por: <strong><?php echo htmlspecialchars($fiesta['organizador_nombre']); ?></strong>
                                </div>
                                
                                <div class="fecha-evento">
                                    <i class="fa fa-calendar-check"></i>
                                    <?php echo formatearFecha($fiesta['fecha_evento']); ?>
                                </div>
                            </div>
                            
                            <div class="ver-mas-btn">
                                <i class="fa fa-eye"></i>
                                Ver detalles
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="sin-resultados">
                    <i class="fa fa-search"></i>
                    <h3>No se encontraron resultados</h3>
                    <p>Intenta con otros términos de búsqueda o filtros</p>
                    
                    <div class="sugerencias">
                        <h4>Sugerencias:</h4>
                        <ul>
                            <li>Verifica la ortografía de las palabras</li>
                            <li>Prueba con términos más generales</li>
                            <li>Usa diferentes categorías</li>
                            <li>Busca por ubicación específica</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <footer class="footer-section">
        <div class="footer-content">
            <div class="footer-section">
                <h3>PartyExpress</h3>
                <p>Tu plataforma para organizar y encontrar los mejores eventos en Paraguay</p>
            </div>
            
            <div class="footer-section">
                <h3>Enlaces Rápidos</h3>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="fiestas.php">Fiestas</a></li>
                    <li><a href="lugares.php">Lugares</a></li>
                    <li><a href="organizar.php">Organizar Evento</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Síguenos</h3>
                <div class="redes-sociales">
                    <a href="https://www.facebook.com/partyexpress.py" target="_blank" class="red-social">
                        <i class="fab fa-facebook-f"></i>
                        <span>@partyexpress.py</span>
                    </a>
                    <a href="https://www.instagram.com/partyexpress_py?utm_source=ig_web_button_share_sheet&igsh=MXF6dWcydmt0dTFuNA==" target="_blank" class="red-social">
                        <i class="fab fa-instagram"></i>
                        <span>@partyexpress_py</span>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 PartyExpress. Todos los derechos reservados.</p>
        </div>
    </footer>

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

            // Cerrar menú con tecla Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && dropdownMenu.classList.contains('show')) {
                    dropdownMenu.classList.remove('show');
                    menuToggle.classList.remove('active');
                }
            });

            // Animación suave al hacer scroll
            let scrollTimeout;
            window.addEventListener('scroll', function() {
                if (dropdownMenu.classList.contains('show')) {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(function() {
                        dropdownMenu.classList.remove('show');
                        menuToggle.classList.remove('active');
                    }, 100);
                }
            });
        }
    </script>
</body>
</html> 