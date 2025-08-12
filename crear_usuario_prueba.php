<?php
require_once 'conexion.php';

echo "<h2>Crear Usuario de Prueba</h2>";

try {
    $conn = obtenerConexion();
    
    // Verificar si ya existe un usuario
    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    $row = $result->fetch_assoc();
    
    if ($row['total'] > 0) {
        echo "<p>Ya existen usuarios en la base de datos.</p>";
    } else {
        // Crear usuario de prueba
        $nombre = "Usuario Prueba";
        $usuario = "testuser";
        $email = "test@example.com";
        $contrasena = password_hash("123456", PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, usuario, email, contrasena) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $nombre, $usuario, $email, $contrasena);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ Usuario de prueba creado exitosamente:</p>";
            echo "<ul>";
            echo "<li><strong>Usuario:</strong> testuser</li>";
            echo "<li><strong>Contraseña:</strong> 123456</li>";
            echo "<li><strong>Email:</strong> test@example.com</li>";
            echo "</ul>";
            echo "<p>Puedes usar estas credenciales para probar el sistema.</p>";
        } else {
            echo "<p style='color: red;'>✗ Error al crear usuario: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
    
    // Verificar categorías
    $result = $conn->query("SELECT COUNT(*) as total FROM categorias_eventos");
    $row = $result->fetch_assoc();
    
    if ($row['total'] == 0) {
        echo "<p style='color: orange;'>⚠ No hay categorías en la base de datos. Ejecuta el script db.sql completo.</p>";
    } else {
        echo "<p style='color: green;'>✓ Categorías encontradas en la base de datos.</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style> 