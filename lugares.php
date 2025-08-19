<?php
session_start();
require_once 'conexion.php';

// Obtener lugares desde la base de datos
$lugares = [];
try {
    $conn = obtenerConexion();
    $stmt = $conn->prepare('SELECT * FROM lugares_eventos WHERE activo = TRUE ORDER BY nombre');
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $lugares[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'descripcion' => $row['descripcion'],
            'categoria' => $row['categoria'],
            'direccion' => $row['direccion'],
            'lat' => floatval($row['latitud']),
            'lng' => floatval($row['longitud']),
            'telefono' => $row['telefono'],
            'email' => $row['email'],
            'capacidad' => $row['capacidad'],
            'precio_min' => floatval($row['precio_minimo']),
            'imagen' => $row['imagen']
        ];
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Si hay error, usar datos de ejemplo
    $lugares = [
        [
            'id' => 1,
            'nombre' => 'Salón de Eventos La Casona',
            'descripcion' => 'Salón elegante para eventos sociales y corporativos',
            'categoria' => 'Salones de eventos',
            'direccion' => 'Av. España 1234, Asunción, Paraguay',
            'lat' => -25.2637,
            'lng' => -57.5759,
            'telefono' => '+595 21 123 456',
            'email' => 'info@lacasona.com.py',
            'capacidad' => 200,
            'precio_min' => 1500000,
            'imagen' => 'img/lugar1.jpg'
        ],
        [
            'id' => 2,
            'nombre' => 'Club Social Paraguayo',
            'descripcion' => 'Club tradicional con salones para fiestas y eventos',
            'categoria' => 'Clubes',
            'direccion' => 'Av. Mariscal López 456, Asunción, Paraguay',
            'lat' => -25.2800,
            'lng' => -57.6300,
            'telefono' => '+595 21 234 567',
            'email' => 'eventos@clubparaguayo.com.py',
            'capacidad' => 150,
            'precio_min' => 1200000,
            'imagen' => 'img/lugar2.jpg'
        ]
    ];
}

function formatearPrecio($precio) {
    return 'Gs. ' . number_format($precio, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lugares - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .lugares-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .lugares-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .lugares-title {
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
        
        .lugares-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            height: 70vh;
        }
        
        .mapa-container {
            background: #2D1950;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
        }
        
        #mapa {
            width: 100%;
            height: 100%;
            border-radius: 20px;
        }
        
        .lugares-lista {
            overflow-y: auto;
            padding-right: 10px;
        }
        
        .lugar-card {
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .lugar-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            border-color: #a259f7;
        }
        
        .lugar-card.seleccionado {
            border-color: #a259f7;
            background: rgba(162,89,247,0.1);
        }
        
        .lugar-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .lugar-nombre {
            color: #a259f7;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
        }
        
        .lugar-categoria {
            background: rgba(162,89,247,0.2);
            color: #a259f7;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .lugar-descripcion {
            color: #ccc;
            font-size: 0.9rem;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .lugar-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
        }
        
        .info-icon {
            color: #a259f7;
            width: 16px;
            text-align: center;
        }
        
        .info-label {
            color: #a259f7;
            font-weight: 600;
        }
        
        .info-value {
            color: #fff;
        }
        
        .lugar-precio {
            color: #28a745;
            font-weight: 600;
            font-size: 1.1rem;
            text-align: right;
        }
        
        .lugar-acciones {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-contacto {
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
        }
        
        .btn-contacto:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(162,89,247,0.3);
        }
        
        .btn-ver {
            background: rgba(162,89,247,0.2);
            color: #a259f7;
            border: 1px solid rgba(162,89,247,0.4);
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
        }
        
        .btn-ver:hover {
            background: rgba(162,89,247,0.3);
        }
        
        .sin-lugares {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        
        .sin-lugares i {
            font-size: 4rem;
            color: #a259f7;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        @media (max-width: 1024px) {
            .lugares-content {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .mapa-container {
                height: 400px;
            }
        }
        
        @media (max-width: 768px) {
            .lugares-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .lugar-info {
                grid-template-columns: 1fr;
            }
            
            .lugar-acciones {
                flex-direction: column;
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
    <div class="lugares-container">
        <header class="lugares-header">
            <h1 class="lugares-title">Lugares para Eventos</h1>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="index.php" class="btn-filtro">
                    <i class="fa fa-home"></i> Inicio
                </a>
                <button class="btn-filtro activo" data-categoria="todos">
                    <i class="fa fa-map-marker-alt"></i> Todos
                </button>
                <button class="btn-filtro" data-categoria="Salones de eventos">
                    <i class="fa fa-building"></i> Salones
                </button>
                <button class="btn-filtro" data-categoria="Hoteles">
                    <i class="fa fa-hotel"></i> Hoteles
                </button>
                <button class="btn-filtro" data-categoria="Restaurantes">
                    <i class="fa fa-utensils"></i> Restaurantes
                </button>
            </div>
        </header>

        <div class="lugares-content">
            <div class="mapa-container">
                <div id="mapa"></div>
            </div>
            
            <div class="lugares-lista">
                <?php foreach ($lugares as $lugar): ?>
                    <div class="lugar-card" data-lugar-id="<?php echo $lugar['id']; ?>" data-lat="<?php echo $lugar['lat']; ?>" data-lng="<?php echo $lugar['lng']; ?>" data-categoria="<?php echo htmlspecialchars($lugar['categoria']); ?>">
                        <div class="lugar-header">
                            <h3 class="lugar-nombre"><?php echo htmlspecialchars($lugar['nombre']); ?></h3>
                            <span class="lugar-categoria"><?php echo htmlspecialchars($lugar['categoria']); ?></span>
                        </div>
                        
                        <p class="lugar-descripcion"><?php echo htmlspecialchars($lugar['descripcion']); ?></p>
                        
                        <div class="lugar-info">
                            <div class="info-item">
                                <i class="fa fa-map-marker-alt info-icon"></i>
                                <span class="info-label">Ubicación:</span>
                                <span class="info-value"><?php echo htmlspecialchars($lugar['direccion']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fa fa-users info-icon"></i>
                                <span class="info-label">Capacidad:</span>
                                <span class="info-value"><?php echo $lugar['capacidad']; ?> personas</span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fa fa-phone info-icon"></i>
                                <span class="info-label">Teléfono:</span>
                                <span class="info-value"><?php echo htmlspecialchars($lugar['telefono']); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <i class="fa fa-envelope info-icon"></i>
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?php echo htmlspecialchars($lugar['email']); ?></span>
                            </div>
                        </div>
                        
                        <div class="lugar-precio">
                            Desde <?php echo formatearPrecio($lugar['precio_min']); ?>
                        </div>
                        
                        <div class="lugar-acciones">
                            <a href="mailto:<?php echo htmlspecialchars($lugar['email']); ?>" class="btn-contacto">
                                <i class="fa fa-envelope"></i> Contactar
                            </a>
                            <a href="tel:<?php echo htmlspecialchars($lugar['telefono']); ?>" class="btn-ver">
                                <i class="fa fa-phone"></i> Llamar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
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

    <!-- Google Maps API -->
    <script>
        let map;
        let markers = [];
        let lugares = <?php echo json_encode($lugares); ?>;

        function initMap() {
            // Centro del mapa en Asunción, Paraguay
            const asuncion = { lat: -25.2637, lng: -57.5759 };
            
            map = new google.maps.Map(document.getElementById("mapa"), {
                zoom: 12,
                center: asuncion,
                styles: [
                    {
                        "featureType": "all",
                        "elementType": "geometry",
                        "stylers": [{"color": "#2d1950"}]
                    },
                    {
                        "featureType": "all",
                        "elementType": "labels.text.stroke",
                        "stylers": [{"color": "#2d1950"}]
                    },
                    {
                        "featureType": "all",
                        "elementType": "labels.text.fill",
                        "stylers": [{"color": "#ffffff"}]
                    },
                    {
                        "featureType": "administrative.locality",
                        "elementType": "labels.text.fill",
                        "stylers": [{"color": "#a259f7"}]
                    },
                    {
                        "featureType": "poi",
                        "elementType": "labels.text.fill",
                        "stylers": [{"color": "#a259f7"}]
                    },
                    {
                        "featureType": "road",
                        "elementType": "geometry",
                        "stylers": [{"color": "#38414e"}]
                    },
                    {
                        "featureType": "road",
                        "elementType": "geometry.stroke",
                        "stylers": [{"color": "#212a37"}]
                    },
                    {
                        "featureType": "road",
                        "elementType": "labels.text.fill",
                        "stylers": [{"color": "#9ca5b3"}]
                    },
                    {
                        "featureType": "water",
                        "elementType": "geometry",
                        "stylers": [{"color": "#17263c"}]
                    }
                ]
            });

            // Crear marcadores para cada lugar
            lugares.forEach((lugar, index) => {
                const marker = new google.maps.Marker({
                    position: { lat: lugar.lat, lng: lugar.lng },
                    map: map,
                    title: lugar.nombre,
                    icon: {
                        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                            <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="20" cy="20" r="18" fill="#a259f7" stroke="#ffffff" stroke-width="2"/>
                                <text x="20" y="25" text-anchor="middle" fill="#ffffff" font-size="12" font-weight="bold">${index + 1}</text>
                            </svg>
                        `),
                        scaledSize: new google.maps.Size(40, 40)
                    }
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div style="padding: 10px; max-width: 250px;">
                            <h3 style="color: #a259f7; margin: 0 0 5px 0; font-size: 16px;">${lugar.nombre}</h3>
                            <p style="margin: 0 0 5px 0; color: #666; font-size: 12px;">${lugar.categoria}</p>
                            <p style="margin: 0 0 5px 0; font-size: 13px;">${lugar.direccion}</p>
                            <p style="margin: 0; color: #28a745; font-weight: bold;">Desde ${formatearPrecio(lugar.precio_min)}</p>
                        </div>
                    `
                });

                marker.addListener("click", () => {
                    infoWindow.open(map, marker);
                    // Resaltar la tarjeta correspondiente
                    document.querySelectorAll('.lugar-card').forEach(card => card.classList.remove('seleccionado'));
                    document.querySelector(`[data-lugar-id="${lugar.id}"]`).classList.add('seleccionado');
                });

                markers.push({ marker, lugar });
            });
        }

        function formatearPrecio(precio) {
            return 'Gs. ' + precio.toLocaleString('es-PY');
        }

        // Filtros
        document.querySelectorAll('.btn-filtro[data-categoria]').forEach(btn => {
            btn.addEventListener('click', function() {
                const categoria = this.dataset.categoria;
                
                // Actualizar botones activos
                document.querySelectorAll('.btn-filtro').forEach(b => b.classList.remove('activo'));
                this.classList.add('activo');
                
                // Filtrar lugares
                document.querySelectorAll('.lugar-card').forEach(card => {
                    if (categoria === 'todos' || card.dataset.categoria === categoria) {
                        card.style.display = 'block';
                        // Mostrar marcador correspondiente
                        const lugarId = parseInt(card.dataset.lugarId);
                        const markerData = markers.find(m => m.lugar.id === lugarId);
                        if (markerData) {
                            markerData.marker.setMap(map);
                        }
                    } else {
                        card.style.display = 'none';
                        // Ocultar marcador correspondiente
                        const lugarId = parseInt(card.dataset.lugarId);
                        const markerData = markers.find(m => m.lugar.id === lugarId);
                        if (markerData) {
                            markerData.marker.setMap(null);
                        }
                    }
                });
            });
        });

        // Hacer clic en tarjeta para centrar mapa
        document.querySelectorAll('.lugar-card').forEach(card => {
            card.addEventListener('click', function() {
                const lat = parseFloat(this.dataset.lat);
                const lng = parseFloat(this.dataset.lng);
                
                map.setCenter({ lat, lng });
                map.setZoom(15);
                
                // Resaltar tarjeta
                document.querySelectorAll('.lugar-card').forEach(c => c.classList.remove('seleccionado'));
                this.classList.add('seleccionado');
            });
        });
    </script>
    
    <!-- Reemplaza YOUR_API_KEY con tu clave de Google Maps API -->
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap">
    </script>
</body>
</html> 