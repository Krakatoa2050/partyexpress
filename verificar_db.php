<?php
require_once 'conexion.php';

echo "<h2>Verificación de Base de Datos</h2>";

try {
    $conn = obtenerConexion();
    
    // Verificar usuarios
    echo "<h3>Usuarios en la base de datos:</h3>";
    $result = $conn->query("SELECT id, nombre, usuario, email, activo FROM usuarios");
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Email</th><th>Activo</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($row['usuario']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . ($row['activo'] ? 'Sí' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay usuarios en la base de datos.</p>";
    }
    
    // Verificar categorías
    echo "<h3>Categorías de eventos:</h3>";
    $result = $conn->query("SELECT id, nombre, activa FROM categorias_eventos");
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Activa</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
            echo "<td>" . ($row['activa'] ? 'Sí' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay categorías en la base de datos.</p>";
    }
    
    // Verificar sesión actual
    echo "<h3>Información de sesión:</h3>";
    session_start();
    if (isset($_SESSION['usuario_id'])) {
        echo "<p>Usuario ID en sesión: " . $_SESSION['usuario_id'] . "</p>";
        echo "<p>Usuario en sesión: " . htmlspecialchars($_SESSION['usuario'] ?? 'No definido') . "</p>";
        
        // Verificar si el usuario de la sesión existe en la BD
        $stmt = $conn->prepare("SELECT id, nombre, usuario, activo FROM usuarios WHERE id = ?");
        $stmt->bind_param('i', $_SESSION['usuario_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<p style='color: green;'>✓ Usuario encontrado en BD: " . htmlspecialchars($user['nombre']) . " (Activo: " . ($user['activo'] ? 'Sí' : 'No') . ")</p>";
        } else {
            echo "<p style='color: red;'>✗ Usuario de sesión NO encontrado en BD</p>";
        }
        $stmt->close();
    } else {
        echo "<p>No hay sesión activa.</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style> 