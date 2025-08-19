<?php
session_start();
require_once 'conexion.php';

echo "<h1>Test de Perfil - Debug</h1>";

// Verificar sesión
echo "<h2>Información de Sesión:</h2>";
if (isset($_SESSION['usuario_id'])) {
    echo "✅ Usuario ID: " . $_SESSION['usuario_id'] . "<br>";
    echo "✅ Usuario: " . ($_SESSION['usuario'] ?? 'No definido') . "<br>";
    echo "✅ Nombre: " . ($_SESSION['nombre'] ?? 'No definido') . "<br>";
} else {
    echo "❌ No hay sesión activa<br>";
    exit();
}

// Verificar conexión a la base de datos
echo "<h2>Test de Conexión:</h2>";
try {
    $conn = obtenerConexion();
    echo "✅ Conexión exitosa<br>";
    
    // Verificar estructura de la tabla usuarios
    echo "<h2>Estructura de la tabla usuarios:</h2>";
    $result = $conn->query("DESCRIBE usuarios");
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Llave</th><th>Por defecto</th><th>Extra</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar si el usuario existe
    echo "<h2>Test de Consulta de Usuario:</h2>";
    $stmt = $conn->prepare('SELECT id, nombre, usuario, email, fecha_registro, ultimo_acceso FROM usuarios WHERE id = ? AND activo = TRUE');
    $stmt->bind_param('i', $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        echo "✅ Usuario encontrado:<br>";
        echo "- ID: " . $usuario['id'] . "<br>";
        echo "- Nombre: " . $usuario['nombre'] . "<br>";
        echo "- Usuario: " . $usuario['usuario'] . "<br>";
        echo "- Email: " . $usuario['email'] . "<br>";
        echo "- Fecha registro: " . $usuario['fecha_registro'] . "<br>";
        echo "- Último acceso: " . $usuario['ultimo_acceso'] . "<br>";
    } else {
        echo "❌ Usuario no encontrado en la base de datos<br>";
    }
    
    $stmt->close();
    
    // Contar solicitudes
    echo "<h2>Test de Solicitudes:</h2>";
    $stmt = $conn->prepare('SELECT COUNT(*) as total FROM solicitudes_eventos WHERE usuario_id = ?');
    $stmt->bind_param('i', $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $solicitudes_count = $result->fetch_assoc()['total'];
    echo "✅ Solicitudes del usuario: " . $solicitudes_count . "<br>";
    $stmt->close();
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
}

echo "<h2>Enlaces:</h2>";
echo "<a href='perfil.php' style='background: #a259f7; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Ir al Perfil</a>";
echo "<a href='actualizar_db_perfil.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Actualizar BD</a>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Inicio</a>";
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

table {
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
    overflow: hidden;
    width: 100%;
}

th {
    background: rgba(162,89,247,0.3);
    color: #fff;
    padding: 10px;
    text-align: left;
}

td {
    padding: 8px 10px;
    border-bottom: 1px solid rgba(162,89,247,0.2);
}

a {
    color: #a259f7;
}
</style>
