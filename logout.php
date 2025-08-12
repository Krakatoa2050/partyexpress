<?php
session_start();
require_once 'conexion.php';

// Marcar sesión como inactiva en la base de datos
if (isset($_SESSION['token_sesion'])) {
    try {
        $conn = obtenerConexion();
        $stmt = $conn->prepare('UPDATE sesiones_usuarios SET activa = FALSE WHERE token_sesion = ?');
        $stmt->bind_param('s', $_SESSION['token_sesion']);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        // Silenciar errores de BD en logout
    }
}

// Destruir sesión PHP
session_destroy();
header('Location: index.php');
exit();
?> 