<?php
require_once 'conexion.php';

echo "<h1>Insertando Fiesta de Baby Shower</h1>";

try {
    $conn = obtenerConexion();
    
    // Insertar la fiesta de Baby Shower
    $stmt = $conn->prepare('
        INSERT IGNORE INTO solicitudes_eventos 
        (usuario_id, titulo, categoria_id, descripcion, ubicacion, fecha_evento, hora_evento, capacidad, presupuesto, privacidad, contacto, estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
    $usuario_id = 1; // María González
    $titulo = '🎊 Baby Shower - Esperando a Sofía';
    $categoria_id = 7; // Baby Shower
    $descripcion = 'Celebración especial para dar la bienvenida a nuestra pequeña Sofía. Juegos temáticos, decoración rosa y azul, buffet dulce y salado, y muchas sorpresas para la futura mamá. ¡Todos están invitados a celebrar esta nueva vida! 👶💕';
    $ubicacion = 'Restaurante El Patio, Calle Palma 321, Asunción, Paraguay';
    $fecha_evento = '2025-02-15';
    $hora_evento = '15:00:00';
    $capacidad = 80;
    $presupuesto = 1200000;
    $privacidad = 'Público';
    $contacto = 'maria@example.com';
    $estado = 'Aprobado';
    
    $stmt->bind_param('isissssdssss', 
        $usuario_id, $titulo, $categoria_id, $descripcion, $ubicacion, 
        $fecha_evento, $hora_evento, $capacidad, $presupuesto, 
        $privacidad, $contacto, $estado
    );
    
    if ($stmt->execute()) {
        echo "<h2>✅ Fiesta de Baby Shower insertada correctamente</h2>";
        echo "<p><strong>Título:</strong> {$titulo}</p>";
        echo "<p><strong>Fecha:</strong> {$fecha_evento}</p>";
        echo "<p><strong>Ubicación:</strong> {$ubicacion}</p>";
    } else {
        echo "<h2>❌ Error al insertar la fiesta</h2>";
        echo "<p>" . $stmt->error . "</p>";
    }
    
    $stmt->close();
    
    // Verificar que se insertó
    $result = $conn->query('
        SELECT COUNT(*) as total 
        FROM solicitudes_eventos 
        WHERE estado = "Aprobado" AND privacidad = "Público"
    ');
    $total = $result->fetch_assoc()['total'];
    echo "<h2>📊 Total de fiestas aprobadas y públicas: {$total}</h2>";
    
    $conn->close();
    
    echo "<p><a href='index.php' style='background: #a259f7; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir a la página principal</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
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
</style> 