<?php
session_start();
require_once 'conexion.php';

echo "<h1>Test de Foto de Perfil - Debug</h1>";

// Verificar sesión
echo "<h2>Información de Sesión:</h2>";
if (isset($_SESSION['usuario_id'])) {
    echo "✅ Usuario ID: " . $_SESSION['usuario_id'] . "<br>";
    echo "✅ Usuario: " . ($_SESSION['usuario'] ?? 'No definido') . "<br>";
    echo "✅ Nombre: " . ($_SESSION['nombre'] ?? 'No definido') . "<br>";
} else {
    echo "❌ No hay sesión activa<br>";
    echo "<p><a href='login.html' style='background: #a259f7; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Iniciar Sesión</a></p>";
    exit();
}

// Verificar conexión y datos del usuario
echo "<h2>Test de Datos del Usuario:</h2>";
try {
    $conn = obtenerConexion();
    echo "✅ Conexión exitosa<br>";
    
    // Verificar si el campo foto_perfil existe
    $result = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'foto_perfil'");
    if ($result->num_rows > 0) {
        echo "✅ Campo 'foto_perfil' existe en la tabla usuarios<br>";
    } else {
        echo "❌ Campo 'foto_perfil' NO existe en la tabla usuarios<br>";
    }
    
    // Obtener datos del usuario
    $stmt = $conn->prepare('SELECT id, nombre, usuario, email, foto_perfil, fecha_registro, ultimo_acceso FROM usuarios WHERE id = ? AND activo = TRUE');
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
        echo "- Foto de perfil: " . ($usuario['foto_perfil'] ? $usuario['foto_perfil'] : 'No tiene foto') . "<br>";
        echo "- Fecha registro: " . $usuario['fecha_registro'] . "<br>";
        echo "- Último acceso: " . $usuario['ultimo_acceso'] . "<br>";
        
        // Mostrar la foto si existe
        if ($usuario['foto_perfil']) {
            echo "<h3>Foto de Perfil Actual:</h3>";
            echo "<img src='uploads/perfiles/" . htmlspecialchars($usuario['foto_perfil']) . "' alt='Foto de perfil' style='width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #a259f7;'>";
        } else {
            echo "<h3>No tiene foto de perfil</h3>";
            echo "<div style='width: 150px; height: 150px; border-radius: 50%; background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: bold; border: 3px solid #a259f7;'>" . strtoupper(substr($usuario['nombre'], 0, 1)) . "</div>";
        }
    } else {
        echo "❌ Usuario no encontrado en la base de datos<br>";
    }
    
    $stmt->close();
    
    // Verificar directorio de uploads
    echo "<h2>Test de Directorio de Uploads:</h2>";
    $directorio = 'uploads/perfiles';
    if (is_dir($directorio)) {
        echo "✅ Directorio 'uploads/perfiles' existe<br>";
        if (is_writable($directorio)) {
            echo "✅ Directorio 'uploads/perfiles' es escribible<br>";
        } else {
            echo "❌ Directorio 'uploads/perfiles' NO es escribible<br>";
        }
        
        // Listar archivos en el directorio
        $archivos = scandir($directorio);
        $fotos = array_filter($archivos, function($archivo) {
            return $archivo !== '.' && $archivo !== '..' && in_array(strtolower(pathinfo($archivo, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
        });
        
        if (count($fotos) > 0) {
            echo "✅ Se encontraron " . count($fotos) . " fotos en el directorio:<br>";
            foreach ($fotos as $foto) {
                echo "- " . $foto . "<br>";
            }
        } else {
            echo "ℹ️ No hay fotos en el directorio<br>";
        }
    } else {
        echo "❌ Directorio 'uploads/perfiles' NO existe<br>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Enlaces de Prueba:</h2>";
echo "<a href='perfil.php' style='background: #a259f7; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Ir al Perfil</a>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Página Principal</a>";
echo "<a href='buscar.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Página de Búsqueda</a>";

echo "<h2>Instrucciones para Probar:</h2>";
echo "<ol>";
echo "<li>Ve a <strong>perfil.php</strong> y haz clic en 'Cambiar foto'</li>";
echo "<li>Selecciona una imagen (JPG, PNG o GIF)</li>";
echo "<li>Haz clic en 'Subir foto'</li>";
echo "<li>La foto debería aparecer inmediatamente en el perfil</li>";
echo "<li>Navega a otras páginas y verifica que la foto aparece en el dropdown</li>";
echo "</ol>";
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

h1, h2, h3 {
    color: #a259f7;
}

ol {
    color: #ccc;
    line-height: 1.6;
}

ol li {
    margin-bottom: 10px;
}

a {
    color: #a259f7;
}
</style>
