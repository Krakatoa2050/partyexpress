<?php
require_once 'conexion.php';

echo "<h1>Verificando Fiestas en la Base de Datos</h1>";

try {
    $conn = obtenerConexion();
    
    // Verificar usuarios
    $result = $conn->query('SELECT COUNT(*) as total FROM usuarios WHERE activo = TRUE');
    $usuarios_count = $result->fetch_assoc()['total'];
    echo "<h2>👥 Usuarios activos: {$usuarios_count}</h2>";
    
    // Verificar fiestas que deberían aparecer en la página principal
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
    
    echo "<h2>🎯 Fiestas que deberían aparecer en la página principal (primeras 3):</h2>";
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
    
    if ($count == 0) {
        echo "<h3 style='color: #ff6b6b;'>⚠️ No hay fiestas aprobadas y públicas para mostrar</h3>";
    } else {
        echo "<h3 style='color: #28a745;'>✅ Se encontraron {$count} fiestas para mostrar</h3>";
    }
    
    $conn->close();
    
    echo "<h2>✅ Verificación completada</h2>";
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