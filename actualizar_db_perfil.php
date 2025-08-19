<?php
require_once 'conexion.php';

echo "<h1>Actualizando Base de Datos - Agregando Campo Foto de Perfil</h1>";

try {
    $conn = obtenerConexion();
    
    // Verificar si el campo foto_perfil ya existe
    $result = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'foto_perfil'");
    
    if ($result->num_rows === 0) {
        // Agregar el campo foto_perfil
        $sql = "ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) NULL AFTER email";
        
        if ($conn->query($sql)) {
            echo "✅ Campo 'foto_perfil' agregado correctamente a la tabla usuarios<br>";
        } else {
            echo "❌ Error al agregar el campo: " . $conn->error . "<br>";
        }
    } else {
        echo "ℹ️ El campo 'foto_perfil' ya existe en la tabla usuarios<br>";
    }
    
    // Crear directorio para las fotos de perfil si no existe
    $directorio = 'uploads/perfiles';
    if (!is_dir($directorio)) {
        if (mkdir($directorio, 0777, true)) {
            echo "✅ Directorio 'uploads/perfiles' creado correctamente<br>";
        } else {
            echo "❌ Error al crear el directorio 'uploads/perfiles'<br>";
        }
    } else {
        echo "ℹ️ El directorio 'uploads/perfiles' ya existe<br>";
    }
    
    // Verificar la estructura actual de la tabla
    echo "<h2>Estructura actual de la tabla usuarios:</h2>";
    $result = $conn->query("DESCRIBE usuarios");
    
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
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
    
    $conn->close();
    
    echo "<h2>✅ ¡Actualización completada!</h2>";
    echo "<p>La base de datos ha sido actualizada correctamente para soportar fotos de perfil.</p>";
    echo "<p><a href='perfil.php' style='background: #a259f7; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al perfil</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error al actualizar la base de datos:</h2>";
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

table {
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
    overflow: hidden;
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
