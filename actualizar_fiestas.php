<?php
require_once 'conexion.php';

echo "<h1>Actualizando Estado de Fiestas - PartyExpress</h1>";

try {
    $conn = obtenerConexion();
    
    // Actualizar todas las fiestas de ejemplo para que estén aprobadas y públicas
    $stmt = $conn->prepare('
        UPDATE solicitudes_eventos 
        SET estado = "Aprobado", privacidad = "Público" 
        WHERE titulo NOT LIKE "addadad%"
    ');
    
    if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        echo "<h2>✅ Se actualizaron {$affected_rows} fiestas</h2>";
    } else {
        echo "<h2>❌ Error al actualizar fiestas</h2>";
    }
    
    $stmt->close();
    
    // Verificar el resultado
    $result = $conn->query('
        SELECT COUNT(*) as total 
        FROM solicitudes_eventos 
        WHERE estado = "Aprobado" AND privacidad = "Público"
    ');
    $fiestas_count = $result->fetch_assoc()['total'];
    echo "<h2>🎉 Total de fiestas aprobadas y públicas: {$fiestas_count}</h2>";
    
    // Mostrar las fiestas actualizadas
    $result = $conn->query('
        SELECT 
            se.titulo,
            se.estado,
            se.privacidad,
            u.nombre as organizador
        FROM solicitudes_eventos se
        JOIN usuarios u ON se.usuario_id = u.id
        WHERE se.estado = "Aprobado" AND se.privacidad = "Público"
        ORDER BY se.fecha_evento ASC
        LIMIT 3
    ');
    
    echo "<h2>📋 Fiestas que aparecerán en la página principal:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Título</th><th>Organizador</th><th>Estado</th><th>Privacidad</th></tr>";
    
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $count++;
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['organizador']) . "</td>";
        echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
        echo "<td>" . htmlspecialchars($row['privacidad']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    if ($count > 0) {
        echo "<h3 style='color: #28a745;'>✅ ¡Perfecto! Ahora las fiestas aparecerán en la página principal</h3>";
    } else {
        echo "<h3 style='color: #ff6b6b;'>⚠️ Aún no hay fiestas para mostrar</h3>";
    }
    
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
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    background: #2D1950;
    color: #fff;
}

h1, h2 {
    color: #a259f7;
}

table {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    overflow: hidden;
}

th, td {
    padding: 10px;
    text-align: left;
    border: 1px solid rgba(162,89,247,0.3);
}

th {
    background: rgba(162,89,247,0.3);
    color: #a259f7;
    font-weight: bold;
}
</style> 