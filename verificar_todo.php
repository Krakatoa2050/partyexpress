<?php
require_once 'conexion.php';

echo "<h1>Verificación Completa de la Base de Datos</h1>";

try {
    $conn = obtenerConexion();
    
    // Verificar todas las fiestas
    $result = $conn->query('
        SELECT 
            se.id,
            se.titulo,
            se.estado,
            se.privacidad,
            se.fecha_evento,
            se.fecha_creacion,
            u.nombre as organizador,
            u.usuario as usuario_nombre
        FROM solicitudes_eventos se
        JOIN usuarios u ON se.usuario_id = u.id
        ORDER BY se.fecha_creacion DESC
    ');
    
    echo "<h2>📋 Todas las fiestas en la base de datos:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Título</th><th>Organizador</th><th>Usuario</th><th>Estado</th><th>Privacidad</th><th>Fecha Evento</th><th>Fecha Creación</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['organizador']) . "</td>";
        echo "<td>" . htmlspecialchars($row['usuario_nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
        echo "<td>" . htmlspecialchars($row['privacidad']) . "</td>";
        echo "<td>" . htmlspecialchars($row['fecha_evento']) . "</td>";
        echo "<td>" . htmlspecialchars($row['fecha_creacion']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Verificar usuarios
    $result = $conn->query('SELECT id, nombre, usuario, activo FROM usuarios ORDER BY id');
    
    echo "<h2>👥 Usuarios en la base de datos:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Activo</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($row['usuario']) . "</td>";
        echo "<td>" . ($row['activo'] ? 'Sí' : 'No') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    $conn->close();
    
    echo "<h2>🔧 Acciones recomendadas:</h2>";
    echo "<p>1. Si las fiestas no están aprobadas, ejecuta el script de actualización</p>";
    echo "<p>2. Si no hay fiestas, ejecuta el script de inserción de datos</p>";
    echo "<p>3. Verifica que los usuarios estén activos</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
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
    margin-bottom: 20px;
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