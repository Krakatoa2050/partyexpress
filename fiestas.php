<?php
session_start();
require_once 'conexion.php';

// Obtener fiestas/eventos desde la base de datos
$fiestas = [];
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
        GROUP BY se.id
        ORDER BY se.fecha_evento ASC
    ');
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $fiestas[] = $row;
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Si hay error, usar datos de ejemplo
    $fiestas = [
        [
            'id' => 1,
            'titulo' => 'Fiesta de Cumpleaños 25',
            'descripcion' => 'Celebración especial con música en vivo y buffet completo',
            'ubicacion' => 'Salón La Casona, Asunción',
            'fecha_evento' => '2024-12-15',
            'hora_evento' => '20:00:00',
            'capacidad' => 150,
            'presupuesto' => 2000000,
            'privacidad' => 'Público',
            'organizador_nombre' => 'María González',
            'categoria_nombre' => 'Cumpleaños',
            'categoria_icono' => 'fa-birthday-cake',
            'total_archivos' => 3
        ],
        [
            'id' => 2,
            'titulo' => 'Graduación Universidad',
            'descripcion' => 'Ceremonia de graduación con cena de gala',
            'ubicacion' => 'Centro de Convenciones, Asunción',
            'fecha_evento' => '2024-12-20',
            'hora_evento' => '19:00:00',
            'capacidad' => 300,
            'presupuesto' => 3500000,
            'privacidad' => 'Público',
            'organizador_nombre' => 'Carlos Rodríguez',
            'categoria_nombre' => 'Graduación',
            'categoria_icono' => 'fa-graduation-cap',
            'total_archivos' => 2
        ]
    ];
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
    <title>Fiestas - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .fiestas-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .fiestas-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .fiestas-title {
            color: #a259f7;
            font-size: 2rem;
            margin: 0;
        }
        
        .btn-filtro {
            background: rgba(162,89,247,0.2);
            color: #a259f7;
            border: 1px solid rgba(162,89,247,0.4);
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            margin: 0 5px;
        }
        
        .btn-filtro:hover,
        .btn-filtro.activo {
            background: #a259f7;
            color: white;
        }
        
        .fiestas-grid {
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
        }
        
        .ver-mas-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(162,89,247,0.3);
        }
        
        .sin-fiestas {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        
        .sin-fiestas i {
            font-size: 4rem;
            color: #a259f7;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .sin-fiestas h3 {
            color: #a259f7;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .fiestas-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .fiestas-grid {
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
    <div class="fiestas-container">
        <header class="fiestas-header">
            <h1 class="fiestas-title">Fiestas y Eventos</h1>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="index.php" class="btn-filtro">
                    <i class="fa fa-home"></i> Inicio
                </a>
                <button class="btn-filtro activo" data-categoria="todos">
                    <i class="fa fa-calendar"></i> Todos
                </button>
                <button class="btn-filtro" data-categoria="Cumpleaños">
                    <i class="fa fa-birthday-cake"></i> Cumpleaños
                </button>
                <button class="btn-filtro" data-categoria="Boda">
                    <i class="fa fa-heart"></i> Bodas
                </button>
                <button class="btn-filtro" data-categoria="Graduación">
                    <i class="fa fa-graduation-cap"></i> Graduaciones
                </button>
            </div>
        </header>

        <?php if (empty($fiestas)): ?>
            <div class="sin-fiestas">
                <i class="fa fa-calendar-times"></i>
                <h3>No hay fiestas públicas disponibles</h3>
                <p>Las fiestas aparecerán aquí cuando sean aprobadas y marcadas como públicas</p>
                <a href="organizar.php" class="btn-filtro" style="margin-top: 20px; display: inline-block;">
                    <i class="fa fa-plus"></i> Organizar mi fiesta
                </a>
            </div>
        <?php else: ?>
            <div class="fiestas-grid">
                <?php foreach ($fiestas as $fiesta): ?>
                    <a href="fiesta_detalle.php?id=<?php echo $fiesta['id']; ?>" class="fiesta-card" data-categoria="<?php echo htmlspecialchars($fiesta['categoria_nombre']); ?>">
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
                            
                            <div class="info-item">
                                <i class="fa fa-eye info-icon"></i>
                                <span class="info-label">Privacidad:</span>
                                <span class="info-value"><?php echo htmlspecialchars($fiesta['privacidad']); ?></span>
                            </div>
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
                        
                        <div class="ver-mas-btn" style="margin-top: 15px;">
                            <i class="fa fa-eye"></i>
                            Ver detalles
                        </div>
                    </a>
                <?php endforeach; ?>
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
                    <li><a href="lugares.php">Lugares</a></li>
                    <li><a href="organizar.php">Organizar Evento</a></li>
                    <li><a href="contacto.php">Contacto</a></li>
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
        // Filtros
        document.querySelectorAll('.btn-filtro[data-categoria]').forEach(btn => {
            btn.addEventListener('click', function() {
                const categoria = this.dataset.categoria;
                
                // Actualizar botones activos
                document.querySelectorAll('.btn-filtro').forEach(b => b.classList.remove('activo'));
                this.classList.add('activo');
                
                // Filtrar fiestas
                document.querySelectorAll('.fiesta-card').forEach(card => {
                    if (categoria === 'todos' || card.dataset.categoria === categoria) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });


    </script>
</body>
</html> 