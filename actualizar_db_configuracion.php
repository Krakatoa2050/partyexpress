<?php
require_once 'conexion.php';

function actualizarBaseDatosConfiguracion() {
    try {
        $conn = obtenerConexion();
        
        // Array de columnas a agregar
        $columnas = [
            'idioma' => 'VARCHAR(10) DEFAULT "es"',
            'zona_horaria' => 'VARCHAR(50) DEFAULT "America/Mexico_City"',
            'visibilidad_perfil' => 'VARCHAR(20) DEFAULT "publico"',
            'permitir_busqueda' => 'TINYINT(1) DEFAULT 1',
            'compartir_datos' => 'TINYINT(1) DEFAULT 0',
            'email_notificaciones' => 'TINYINT(1) DEFAULT 1',
            'push_notificaciones' => 'TINYINT(1) DEFAULT 1',
            'recordatorios_eventos' => 'TINYINT(1) DEFAULT 1',
            'nuevos_favoritos' => 'TINYINT(1) DEFAULT 0',
            'two_factor_enabled' => 'TINYINT(1) DEFAULT 0',
            'two_factor_secret' => 'VARCHAR(32) DEFAULT NULL',
            'backup_codes' => 'TEXT DEFAULT NULL'
        ];
        
        $columnas_agregadas = [];
        
        // Obtener todas las columnas existentes de una vez
        $result = $conn->query("DESCRIBE usuarios");
        $columnas_existentes = [];
        while ($row = $result->fetch_assoc()) {
            $columnas_existentes[] = $row['Field'];
        }
        
        foreach ($columnas as $columna => $definicion) {
            // Verificar si la columna ya existe
            if (!in_array($columna, $columnas_existentes)) {
                // La columna no existe, agregarla
                $sql = "ALTER TABLE usuarios ADD COLUMN $columna $definicion";
                if ($conn->query($sql)) {
                    $columnas_agregadas[] = $columna;
                }
            }
        }
        
        $conn->close();
        return $columnas_agregadas;
        
    } catch (Exception $e) {
        return false;
    }
}

// Si se accede directamente a este archivo, mostrar la página de actualización
if (basename($_SERVER['PHP_SELF']) === 'actualizar_db_configuracion.php') {
    $columnas_agregadas = actualizarBaseDatosConfiguracion();
    
    if ($columnas_agregadas !== false) {
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Actualización de Base de Datos - PartyExpress</title>
            <style>
                body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin: 0; padding: 20px; min-height: 100vh; }
                .container { max-width: 800px; margin: 0 auto; background: rgba(255,255,255,0.1); padding: 30px; border-radius: 20px; backdrop-filter: blur(10px); }
                h1 { text-align: center; color: #a259f7; }
                .success { color: #28a745; }
                .info { color: #17a2b8; }
                .btn { display: inline-block; background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 50px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>✅ Base de Datos Actualizada</h1>";
        
        if (!empty($columnas_agregadas)) {
            echo "<p class='success'>Se agregaron las siguientes columnas:</p><ul>";
            foreach ($columnas_agregadas as $columna) {
                echo "<li class='success'>✅ $columna</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='info'>ℹ️ Todas las columnas ya existen en la base de datos.</p>";
        }
        
        echo "<a href='configuracion_unificada.php' class='btn'>Ir a Configuración</a>
            </div>
        </body>
        </html>";
    } else {
        echo "<p style='color: red;'>Error al actualizar la base de datos.</p>";
    }
}
?>
