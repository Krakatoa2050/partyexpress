<?php
require_once 'conexion.php';

echo "<h1>Insertando Datos de Ejemplo - PartyExpress</h1>";

try {
    $conn = obtenerConexion();
    
    // Insertar usuarios de ejemplo
    echo "<h2>Insertando usuarios de ejemplo...</h2>";
    
    $usuarios = [
        ['María González', 'mariagonzalez', 'maria@example.com', password_hash('123456', PASSWORD_DEFAULT)],
        ['Carlos Rodríguez', 'carlosrodriguez', 'carlos@example.com', password_hash('123456', PASSWORD_DEFAULT)],
        ['Ana Martínez', 'anamartinez', 'ana@example.com', password_hash('123456', PASSWORD_DEFAULT)]
    ];
    
    $stmt = $conn->prepare('INSERT IGNORE INTO usuarios (nombre, usuario, email, contrasena, activo) VALUES (?, ?, ?, ?, TRUE)');
    
    foreach ($usuarios as $usuario) {
        $stmt->bind_param('ssss', $usuario[0], $usuario[1], $usuario[2], $usuario[3]);
        if ($stmt->execute()) {
            echo "✅ Usuario insertado: {$usuario[1]}<br>";
        } else {
            echo "⚠️ Usuario ya existe: {$usuario[1]}<br>";
        }
    }
    
    $stmt->close();
    
    // Insertar fiestas de ejemplo
    echo "<h2>Insertando fiestas de ejemplo...</h2>";
    
    $fiestas = [
        [
            'usuario_id' => 1,
            'titulo' => 'Fiesta de Cumpleaños 25 - María',
            'categoria_id' => 1,
            'descripcion' => 'Celebración especial de mi cumpleaños número 25 con música en vivo, buffet completo, decoración temática y sorpresas especiales. ¡Todos están invitados a celebrar conmigo!',
            'ubicacion' => 'Salón La Casona, Av. España 1234, Asunción, Paraguay',
            'fecha_evento' => '2024-12-15',
            'hora_evento' => '20:00:00',
            'capacidad' => 150,
            'presupuesto' => 2000000,
            'privacidad' => 'Público',
            'contacto' => 'maria@example.com',
            'estado' => 'Aprobado'
        ],
        [
            'usuario_id' => 2,
            'titulo' => 'Graduación Universidad - Ingeniería',
            'categoria_id' => 3,
            'descripcion' => 'Ceremonia de graduación de la promoción 2024 de Ingeniería con cena de gala, entrega de diplomas y celebración especial. Evento formal con dress code elegante.',
            'ubicacion' => 'Centro de Convenciones del Paraguay, Av. Costanera 789, Asunción',
            'fecha_evento' => '2024-12-20',
            'hora_evento' => '19:00:00',
            'capacidad' => 300,
            'presupuesto' => 3500000,
            'privacidad' => 'Público',
            'contacto' => 'carlos@example.com',
            'estado' => 'Aprobado'
        ],
        [
            'usuario_id' => 3,
            'titulo' => 'Boda de Ana y Juan - Celebración de Amor',
            'categoria_id' => 2,
            'descripcion' => 'Celebración de nuestro amor con ceremonia religiosa en la iglesia y recepción en hotel de lujo. Incluye cena, baile, fotografía profesional y momentos inolvidables.',
            'ubicacion' => 'Hotel Gran Asunción, Av. Brasilia 654, Asunción',
            'fecha_evento' => '2024-12-25',
            'hora_evento' => '18:00:00',
            'capacidad' => 200,
            'presupuesto' => 5000000,
            'privacidad' => 'Público',
            'contacto' => 'ana@example.com',
            'estado' => 'Aprobado'
        ],
        [
            'usuario_id' => 1,
            'titulo' => 'Fiesta Temática - Años 80',
            'categoria_id' => 6,
            'descripcion' => 'Fiesta retro con música de los años 80, decoración vintage, disfraces de la época y mucho baile. ¡Vamos a revivir la mejor década!',
            'ubicacion' => 'Club Social Paraguayo, Av. Mariscal López 456, Asunción',
            'fecha_evento' => '2024-12-30',
            'hora_evento' => '21:00:00',
            'capacidad' => 120,
            'presupuesto' => 1800000,
            'privacidad' => 'Público',
            'contacto' => 'maria@example.com',
            'estado' => 'Aprobado'
        ],
        [
            'usuario_id' => 2,
            'titulo' => 'Evento Corporativo - Lanzamiento Producto',
            'categoria_id' => 5,
            'descripcion' => 'Lanzamiento oficial de nuestro nuevo producto con presentación ejecutiva, networking, cóctel y entretenimiento. Evento exclusivo para profesionales del sector.',
            'ubicacion' => 'Centro de Convenciones del Paraguay, Av. Costanera 789, Asunción',
            'fecha_evento' => '2025-01-10',
            'hora_evento' => '18:30:00',
            'capacidad' => 250,
            'presupuesto' => 4000000,
            'privacidad' => 'Público',
            'contacto' => 'carlos@example.com',
            'estado' => 'Aprobado'
        ],
        [
            'usuario_id' => 3,
            'titulo' => '🎉 Mega Fiesta de Verano - Pool Party Extravaganza',
            'categoria_id' => 6,
            'descripcion' => '¡La fiesta más épica del verano! Pool party con DJ en vivo, barras de cócteles temáticos, juegos acuáticos, food trucks gourmet, zona VIP con cabañas privadas, show de luces láser y fuegos artificiales. Dress code: traje de baño y actitud festiva. ¡No te pierdas la experiencia del año! 🌊🎵✨',
            'ubicacion' => 'Parque Acuático Aqualandia, Ruta 2 Km 25, San Bernardino, Paraguay',
            'fecha_evento' => '2025-01-25',
            'hora_evento' => '16:00:00',
            'capacidad' => 500,
            'presupuesto' => 8000000,
            'privacidad' => 'Público',
            'contacto' => 'ana@example.com',
            'estado' => 'Aprobado'
        ],
        [
            'usuario_id' => 1,
            'titulo' => '🎊 Baby Shower - Esperando a Sofía',
            'categoria_id' => 7,
            'descripcion' => 'Celebración especial para dar la bienvenida a nuestra pequeña Sofía. Juegos temáticos, decoración rosa y azul, buffet dulce y salado, y muchas sorpresas para la futura mamá. ¡Todos están invitados a celebrar esta nueva vida! 👶💕',
            'ubicacion' => 'Restaurante El Patio, Calle Palma 321, Asunción, Paraguay',
            'fecha_evento' => '2025-02-15',
            'hora_evento' => '15:00:00',
            'capacidad' => 80,
            'presupuesto' => 1200000,
            'privacidad' => 'Público',
            'contacto' => 'maria@example.com',
            'estado' => 'Aprobado'
        ]
    ];
    
    $stmt = $conn->prepare('
        INSERT IGNORE INTO solicitudes_eventos 
        (usuario_id, titulo, categoria_id, descripcion, ubicacion, fecha_evento, hora_evento, capacidad, presupuesto, privacidad, contacto, estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
    foreach ($fiestas as $fiesta) {
        $stmt->bind_param(
            'isissssdssss',
            $fiesta['usuario_id'],
            $fiesta['titulo'],
            $fiesta['categoria_id'],
            $fiesta['descripcion'],
            $fiesta['ubicacion'],
            $fiesta['fecha_evento'],
            $fiesta['hora_evento'],
            $fiesta['capacidad'],
            $fiesta['presupuesto'],
            $fiesta['privacidad'],
            $fiesta['contacto'],
            $fiesta['estado']
        );
        
        if ($stmt->execute()) {
            echo "✅ Fiesta insertada: {$fiesta['titulo']}<br>";
        } else {
            echo "⚠️ Fiesta ya existe: {$fiesta['titulo']}<br>";
        }
    }
    
    $stmt->close();
    
    // Insertar lugar de la fiesta personalizada
    echo "<h2>Insertando lugar de la fiesta personalizada...</h2>";
    
    $stmt = $conn->prepare('
        INSERT IGNORE INTO lugares_eventos 
        (nombre, descripcion, categoria, direccion, latitud, longitud, telefono, email, capacidad, precio_minimo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
    $lugar_fiesta = [
        'nombre' => 'Parque Acuático Aqualandia',
        'descripcion' => 'Parque acuático de lujo con piscinas infinitas, toboganes extremos, zona VIP y servicios premium para eventos especiales',
        'categoria' => 'Parques acuáticos',
        'direccion' => 'Ruta 2 Km 25, San Bernardino, Paraguay',
        'latitud' => -25.3500,
        'longitud' => -57.3000,
        'telefono' => '+595 21 987 654',
        'email' => 'eventos@aqualandia.com.py',
        'capacidad' => 500,
        'precio_minimo' => 5000000
    ];
    
    $stmt->bind_param('ssssddssid', 
        $lugar_fiesta['nombre'],
        $lugar_fiesta['descripcion'],
        $lugar_fiesta['categoria'],
        $lugar_fiesta['direccion'],
        $lugar_fiesta['latitud'],
        $lugar_fiesta['longitud'],
        $lugar_fiesta['telefono'],
        $lugar_fiesta['email'],
        $lugar_fiesta['capacidad'],
        $lugar_fiesta['precio_minimo']
    );
    
    if ($stmt->execute()) {
        echo "✅ Lugar insertado: {$lugar_fiesta['nombre']}<br>";
    } else {
        echo "⚠️ Lugar ya existe: {$lugar_fiesta['nombre']}<br>";
    }
    
    $stmt->close();
    
    // Verificar datos insertados
    echo "<h2>Verificando datos insertados...</h2>";
    
    $result = $conn->query('SELECT COUNT(*) as total FROM usuarios WHERE activo = TRUE');
    $usuarios_count = $result->fetch_assoc()['total'];
    echo "👥 Total de usuarios activos: {$usuarios_count}<br>";
    
    $result = $conn->query('SELECT COUNT(*) as total FROM solicitudes_eventos WHERE estado = "Aprobado" AND privacidad = "Público"');
    $fiestas_count = $result->fetch_assoc()['total'];
    echo "🎉 Total de fiestas públicas aprobadas: {$fiestas_count}<br>";
    
    $conn->close();
    
    echo "<h2>✅ ¡Datos de ejemplo insertados correctamente!</h2>";
    echo "<p>Ahora puedes ver las fiestas de ejemplo en la página principal.</p>";
    echo "<p><a href='index.php' style='background: #a259f7; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir a la página principal</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error al insertar datos:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: #2D1950;
    color: #fff;
}

h1, h2 {
    color: #a259f7;
}   

a {
    color: #a259f7;
}
</style>            