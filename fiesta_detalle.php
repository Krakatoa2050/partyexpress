<?php
session_start();
require_once 'conexion.php';

// Obtener ID de la fiesta desde la URL
$fiesta_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$fiesta_id) {
    header('Location: fiestas.php');
    exit;
}

// Obtener información de la fiesta
$fiesta = null;
try {
    $conn = obtenerConexion();
    $stmt = $conn->prepare('
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
            se.contacto,
            se.estado,
            se.fecha_creacion,
            u.nombre as organizador_nombre,
            u.usuario as organizador_usuario,
            u.email as organizador_email,
            ce.nombre as categoria_nombre,
            ce.icono as categoria_icono,
            ce.color as categoria_color,
            ce.descripcion as categoria_descripcion
        FROM solicitudes_eventos se
        JOIN usuarios u ON se.usuario_id = u.id
        JOIN categorias_eventos ce ON se.categoria_id = ce.id
        WHERE se.id = ? AND se.estado = "Aprobado"
    ');
    
    $stmt->bind_param('i', $fiesta_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $fiesta = $result->fetch_assoc();
    }
    
    $stmt->close();
    
    // Obtener archivos adjuntos
    $archivos = [];
    if ($fiesta) {
        $stmt = $conn->prepare('
            SELECT id, nombre_original, tipo_mime, tamano_bytes, fecha_subida
            FROM archivos_adjuntos 
            WHERE solicitud_id = ?
            ORDER BY fecha_subida ASC
        ');
        
        $stmt->bind_param('i', $fiesta_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $archivos[] = $row;
        }
        
        $stmt->close();
    }
    
    $conn->close();
} catch (Exception $e) {
    // Error en la base de datos
}

if (!$fiesta) {
    header('Location: fiestas.php');
    exit;
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

function formatearTamano($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
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
    <title><?php echo htmlspecialchars($fiesta['titulo']); ?> - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .detalle-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .detalle-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .btn-volver {
            background: rgba(162,89,247,0.2);
            color: #a259f7;
            border: 1px solid rgba(162,89,247,0.4);
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-volver:hover {
            background: #a259f7;
            color: white;
        }
        
        .fiesta-hero {
            background: rgba(255,255,255,0.05);
            border-radius: 25px;
            padding: 40px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .fiesta-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
        }
        
        .fiesta-titulo {
            color: #a259f7;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 15px 0;
            line-height: 1.2;
        }
        
        .fiesta-categoria {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 20px;
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
            line-height: 1.8;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        
        .fiesta-detalles {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .detalle-seccion {
            background: rgba(255,255,255,0.03);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(162,89,247,0.1);
        }
        
        .seccion-titulo {
            color: #a259f7;
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .detalle-lista {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .detalle-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(162,89,247,0.1);
        }
        
        .detalle-item:last-child {
            border-bottom: none;
        }
        
        .detalle-label {
            color: #a259f7;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .detalle-valor {
            color: #fff;
            font-size: 0.95rem;
            text-align: right;
        }
        
        .organizador-card {
            background: rgba(162,89,247,0.1);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(162,89,247,0.3);
            text-align: center;
        }
        
        .organizador-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #a259f7, #7209b7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2rem;
            color: white;
        }
        
        .organizador-nombre {
            color: #a259f7;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .organizador-usuario {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .btn-contacto {
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
            color: white;
            padding: 10px 20px;
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
        
        .archivos-seccion {
            margin-top: 30px;
        }
        
        .archivo-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            margin-bottom: 10px;
            border: 1px solid rgba(162,89,247,0.1);
        }
        
        .archivo-icono {
            width: 40px;
            height: 40px;
            background: rgba(162,89,247,0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a259f7;
        }
        
        .archivo-info {
            flex: 1;
        }
        
        .archivo-nombre {
            color: #fff;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .archivo-detalles {
            color: #888;
            font-size: 0.85rem;
        }
        
        .sin-archivos {
            text-align: center;
            padding: 40px 20px;
            color: #888;
        }
        
        .sin-archivos i {
            font-size: 3rem;
            color: #a259f7;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .detalle-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .fiesta-titulo {
                font-size: 2rem;
            }
            
            .fiesta-detalles {
                grid-template-columns: 1fr;
            }
            
            .detalle-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .detalle-valor {
                text-align: left;
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
    <div class="detalle-container">
        <header class="detalle-header">
            <h1 style="color: #a259f7; margin: 0;">Detalles del Evento</h1>
            <a href="fiestas.php" class="btn-volver">
                <i class="fa fa-arrow-left"></i>
                Volver a Fiestas
            </a>
        </header>

        <div class="fiesta-hero">
            <h1 class="fiesta-titulo"><?php echo htmlspecialchars($fiesta['titulo']); ?></h1>
            
            <span class="fiesta-categoria <?php echo obtenerClaseCategoria($fiesta['categoria_nombre']); ?>">
                <i class="fa <?php echo htmlspecialchars($fiesta['categoria_icono']); ?>"></i>
                <?php echo htmlspecialchars($fiesta['categoria_nombre']); ?>
            </span>
            
            <p class="fiesta-descripcion"><?php echo nl2br(htmlspecialchars($fiesta['descripcion'])); ?></p>
        </div>

        <div class="fiesta-detalles">
            <div class="detalle-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa fa-calendar-alt"></i>
                    Información del Evento
                </h3>
                <ul class="detalle-lista">
                    <li class="detalle-item">
                        <span class="detalle-label">Fecha:</span>
                        <span class="detalle-valor"><?php echo formatearFecha($fiesta['fecha_evento']); ?></span>
                    </li>
                    <li class="detalle-item">
                        <span class="detalle-label">Hora:</span>
                        <span class="detalle-valor"><?php echo formatearHora($fiesta['hora_evento']); ?></span>
                    </li>
                    <li class="detalle-item">
                        <span class="detalle-label">Ubicación:</span>
                        <span class="detalle-valor"><?php echo htmlspecialchars($fiesta['ubicacion']); ?></span>
                    </li>
                    <?php if ($fiesta['capacidad']): ?>
                    <li class="detalle-item">
                        <span class="detalle-label">Capacidad:</span>
                        <span class="detalle-valor"><?php echo $fiesta['capacidad']; ?> personas</span>
                    </li>
                    <?php endif; ?>
                    <li class="detalle-item">
                        <span class="detalle-label">Privacidad:</span>
                        <span class="detalle-valor"><?php echo htmlspecialchars($fiesta['privacidad']); ?></span>
                    </li>
                    <li class="detalle-item">
                        <span class="detalle-label">Estado:</span>
                        <span class="detalle-valor"><?php echo htmlspecialchars($fiesta['estado']); ?></span>
                    </li>
                </ul>
            </div>

            <div class="detalle-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa fa-money-bill"></i>
                    Información Financiera
                </h3>
                <ul class="detalle-lista">
                    <li class="detalle-item">
                        <span class="detalle-label">Presupuesto:</span>
                        <span class="detalle-valor"><?php echo formatearPresupuesto($fiesta['presupuesto']); ?></span>
                    </li>
                    <li class="detalle-item">
                        <span class="detalle-label">Fecha de Creación:</span>
                        <span class="detalle-valor"><?php echo formatearFecha($fiesta['fecha_creacion']); ?></span>
                    </li>
                </ul>
            </div>

            <div class="detalle-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa fa-user"></i>
                    Organizador
                </h3>
                <div class="organizador-card">
                    <div class="organizador-avatar">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="organizador-nombre"><?php echo htmlspecialchars($fiesta['organizador_nombre']); ?></div>
                    <div class="organizador-usuario">@<?php echo htmlspecialchars($fiesta['organizador_usuario']); ?></div>
                    <?php if ($fiesta['contacto']): ?>
                    <a href="mailto:<?php echo htmlspecialchars($fiesta['contacto']); ?>" class="btn-contacto">
                        <i class="fa fa-envelope"></i>
                        Contactar Organizador
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($archivos)): ?>
        <div class="archivos-seccion">
            <div class="detalle-seccion">
                <h3 class="seccion-titulo">
                    <i class="fa fa-paperclip"></i>
                    Archivos Adjuntos (<?php echo count($archivos); ?>)
                </h3>
                <?php foreach ($archivos as $archivo): ?>
                <div class="archivo-item">
                    <div class="archivo-icono">
                        <i class="fa fa-file"></i>
                    </div>
                    <div class="archivo-info">
                        <div class="archivo-nombre"><?php echo htmlspecialchars($archivo['nombre_original']); ?></div>
                        <div class="archivo-detalles">
                            <?php echo htmlspecialchars($archivo['tipo_mime']); ?> • 
                            <?php echo formatearTamano($archivo['tamano_bytes']); ?> • 
                            Subido el <?php echo formatearFecha($archivo['fecha_subida']); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
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
</body>
</html> 