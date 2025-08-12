<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html?redirect=' . urlencode('mis_solicitudes.php'));
    exit();
}

// Obtener solicitudes del usuario
$solicitudes = [];
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
            ce.nombre as categoria_nombre,
            ce.icono as categoria_icono,
            ce.color as categoria_color,
            COUNT(aa.id) as total_archivos
        FROM solicitudes_eventos se
        JOIN categorias_eventos ce ON se.categoria_id = ce.id
        LEFT JOIN archivos_adjuntos aa ON se.id = aa.solicitud_id
        WHERE se.usuario_id = ?
        GROUP BY se.id
        ORDER BY se.fecha_creacion DESC
    ');
    
    $stmt->bind_param('i', $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $solicitudes[] = $row;
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $error = 'Error al cargar las solicitudes: ' . $e->getMessage();
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

function obtenerClaseEstado($estado) {
    switch ($estado) {
        case 'Pendiente': return 'estado-pendiente';
        case 'En revisión': return 'estado-revision';
        case 'Aprobado': return 'estado-aprobado';
        case 'Rechazado': return 'estado-rechazado';
        case 'Cancelado': return 'estado-cancelado';
        default: return 'estado-pendiente';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Solicitudes - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .solicitudes-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .solicitudes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .solicitudes-title {
            color: #a259f7;
            font-size: 2rem;
            margin: 0;
        }
        
        .btn-nueva {
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-nueva:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(162,89,247,0.3);
        }
        
        .solicitudes-grid {
            display: grid;
            gap: 20px;
        }
        
        .solicitud-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }
        
        .solicitud-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 70px rgba(0,0,0,0.4);
        }
        
        .solicitud-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .solicitud-titulo {
            color: #a259f7;
            font-size: 1.4rem;
            margin: 0;
            flex: 1;
        }
        
        .estado-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .estado-pendiente {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.4);
        }
        
        .estado-revision {
            background: rgba(0, 123, 255, 0.2);
            color: #007bff;
            border: 1px solid rgba(0, 123, 255, 0.4);
        }
        
        .estado-aprobado {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.4);
        }
        
        .estado-rechazado {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.4);
        }
        
        .estado-cancelado {
            background: rgba(108, 117, 125, 0.2);
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.4);
        }
        
        .solicitud-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-icon {
            color: #a259f7;
            width: 20px;
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
        
        .solicitud-descripcion {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .solicitud-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .fecha-creacion {
            color: #888;
            font-size: 0.85rem;
        }
        
        .archivos-info {
            color: #a259f7;
            font-size: 0.85rem;
        }
        
        .sin-solicitudes {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        
        .sin-solicitudes i {
            font-size: 4rem;
            color: #a259f7;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .sin-solicitudes h3 {
            color: #a259f7;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .solicitudes-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .solicitud-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .solicitud-info {
                grid-template-columns: 1fr;
            }
            
            .solicitud-footer {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="solicitudes-container">
        <header class="solicitudes-header">
            <h1 class="solicitudes-title">Mis Solicitudes</h1>
            <a href="organizar.php" class="btn-nueva">
                <i class="fa fa-plus"></i> Nueva Solicitud
            </a>
        </header>

        <?php if (isset($error)): ?>
            <div style="background: rgba(220,53,69,0.2); color: #ff6b6b; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($solicitudes)): ?>
            <div class="sin-solicitudes">
                <i class="fa fa-calendar-plus"></i>
                <h3>No tienes solicitudes aún</h3>
                <p>Comienza creando tu primera solicitud de evento</p>
                <a href="organizar.php" class="btn-nueva" style="margin-top: 20px; display: inline-block;">
                    <i class="fa fa-plus"></i> Crear Solicitud
                </a>
            </div>
        <?php else: ?>
            <div class="solicitudes-grid">
                <?php foreach ($solicitudes as $solicitud): ?>
                    <div class="solicitud-card">
                        <div class="solicitud-header">
                            <h3 class="solicitud-titulo"><?php echo htmlspecialchars($solicitud['titulo']); ?></h3>
                            <span class="estado-badge <?php echo obtenerClaseEstado($solicitud['estado']); ?>">
                                <?php echo htmlspecialchars($solicitud['estado']); ?>
                            </span>
                        </div>
                        
                        <div class="solicitud-info">
                            <div class="info-item">
                                <i class="fa <?php echo htmlspecialchars($solicitud['categoria_icono']); ?> info-icon"></i>
                                <span class="info-label">Categoría:</span>
                                <span class="info-value"><?php echo htmlspecialchars($solicitud['categoria_nombre']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fa fa-map-marker-alt info-icon"></i>
                                <span class="info-label">Ubicación:</span>
                                <span class="info-value"><?php echo htmlspecialchars($solicitud['ubicacion']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fa fa-calendar info-icon"></i>
                                <span class="info-label">Fecha:</span>
                                <span class="info-value"><?php echo formatearFecha($solicitud['fecha_evento']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fa fa-clock info-icon"></i>
                                <span class="info-label">Hora:</span>
                                <span class="info-value"><?php echo formatearHora($solicitud['hora_evento']); ?></span>
                            </div>
                            
                            <?php if ($solicitud['capacidad']): ?>
                            <div class="info-item">
                                <i class="fa fa-users info-icon"></i>
                                <span class="info-label">Capacidad:</span>
                                <span class="info-value"><?php echo htmlspecialchars($solicitud['capacidad']); ?> personas</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($solicitud['presupuesto']): ?>
                            <div class="info-item">
                                <i class="fa fa-money-bill info-icon"></i>
                                <span class="info-label">Presupuesto:</span>
                                <span class="info-value"><?php echo formatearPresupuesto($solicitud['presupuesto']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-item">
                                <i class="fa fa-eye info-icon"></i>
                                <span class="info-label">Privacidad:</span>
                                <span class="info-value"><?php echo htmlspecialchars($solicitud['privacidad']); ?></span>
                            </div>
                        </div>
                        
                        <div class="solicitud-descripcion">
                            <?php echo htmlspecialchars($solicitud['descripcion']); ?>
                        </div>
                        
                        <div class="solicitud-footer">
                            <div class="fecha-creacion">
                                <i class="fa fa-calendar-plus"></i>
                                Creada el <?php echo formatearFecha($solicitud['fecha_creacion']); ?>
                            </div>
                            
                            <?php if ($solicitud['total_archivos'] > 0): ?>
                            <div class="archivos-info">
                                <i class="fa fa-paperclip"></i>
                                <?php echo $solicitud['total_archivos']; ?> archivo<?php echo $solicitud['total_archivos'] > 1 ? 's' : ''; ?> adjunto<?php echo $solicitud['total_archivos'] > 1 ? 's' : ''; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 