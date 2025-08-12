<?php
require_once 'conexion.php';

echo "<h1>Corrigiendo Asociación de Fiestas con Usuarios</h1>";

try {
    $conn = obtenerConexion();
    
    // Primero, vamos a ver qué usuarios tenemos y sus IDs
    $result = $conn->query('SELECT id, nombre, usuario FROM usuarios WHERE activo = TRUE ORDER BY id');
    
    echo "<h2>👥 Usuarios disponibles:</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th></tr>";
    
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($row['usuario']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Ahora vamos a actualizar las fiestas para que usen los usuarios correctos
    // Buscar usuario María González
    $maria_id = null;
    $carlos_id = null;
    $ana_id = null;
    
    foreach ($usuarios as $usuario) {
        if (strpos($usuario['nombre'], 'María') !== false) {
            $maria_id = $usuario['id'];
        } elseif (strpos($usuario['nombre'], 'Carlos') !== false) {
            $carlos_id = $usuario['id'];
        } elseif (strpos($usuario['nombre'], 'Ana') !== false) {
            $ana_id = $usuario['id'];
        }
    }
    
    echo "<h2>🔧 Actualizando fiestas...</h2>";
    
    // Actualizar fiestas de María
    if ($maria_id) {
        $stmt = $conn->prepare('UPDATE solicitudes_eventos SET usuario_id = ? WHERE titulo LIKE "%Fiesta de Cumpleaños%" OR titulo LIKE "%Fiesta Temática%"');
        $stmt->bind_param('i', $maria_id);
        $stmt->execute();
        echo "<p>✅ Fiestas de María actualizadas (usuario_id: {$maria_id})</p>";
        $stmt->close();
    }
    
    // Actualizar fiestas de Carlos
    if ($carlos_id) {
        $stmt = $conn->prepare('UPDATE solicitudes_eventos SET usuario_id = ? WHERE titulo LIKE "%Graduación Universidad%" OR titulo LIKE "%Evento Corporativo%"');
        $stmt->bind_param('i', $carlos_id);
        $stmt->execute();
        echo "<p>✅ Fiestas de Carlos actualizadas (usuario_id: {$carlos_id})</p>";
        $stmt->close();
    }
    
    // Actualizar fiestas de Ana
    if ($ana_id) {
        $stmt = $conn->prepare('UPDATE solicitudes_eventos SET usuario_id = ? WHERE titulo LIKE "%Boda de Ana%" OR titulo LIKE "%Mega Fiesta de Verano%"');
        $stmt->bind_param('i', $ana_id);
        $stmt->execute();
        echo "<p>✅ Fiestas de Ana actualizadas (usuario_id: {$ana_id})</p>";
        $stmt->close();
    }
    
    // Verificar el resultado
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