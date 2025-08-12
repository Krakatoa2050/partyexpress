<?php
require_once 'conexion.php';

echo "<h1>Verificando Fiesta de Baby Shower</h1>";

try {
    $conn = obtenerConexion();
    
    // Buscar la fiesta de Baby Shower
    $result = $conn->query('
        SELECT 
            se.titulo,
            se.estado,
            se.privacidad,
            u.nombre as organizador
        FROM solicitudes_eventos se
        JOIN usuarios u ON se.usuario_id = u.id
        WHERE se.titulo LIKE "%Baby Shower%"
    ');
    
    echo "<h2>🔍 Buscando fiesta de Baby Shower:</h2>";
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<p>✅ <strong>{$row['titulo']}</strong></p>";
            echo "<p>Estado: {$row['estado']}</p>";
            echo "<p>Privacidad: {$row['privacidad']}</p>";
            echo "<p>Organizador: {$row['organizador']}</p>";
        }
    } else {
        echo "<p>❌ No se encontró la fiesta de Baby Shower</p>";
    }
    
    // Verificar todas las fiestas aprobadas
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