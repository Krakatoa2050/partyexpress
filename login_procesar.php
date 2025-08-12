<?php
session_start();
require_once 'conexion.php';

$usuario = $_POST['usuario'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$redirect = $_POST['redirect'] ?? '';

if ($usuario && $contrasena) {
    $conn = obtenerConexion();
    $stmt = $conn->prepare('SELECT id, contrasena, nombre FROM usuarios WHERE usuario = ? AND activo = TRUE');
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hash, $nombre);
        $stmt->fetch();
        if (password_verify($contrasena, $hash)) {
            // Actualizar último acceso
            $stmt_update = $conn->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?');
            $stmt_update->bind_param('i', $id);
            $stmt_update->execute();
            $stmt_update->close();
            
            // Crear sesión en base de datos
            $token_sesion = bin2hex(random_bytes(32));
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt_sesion = $conn->prepare('INSERT INTO sesiones_usuarios (usuario_id, token_sesion, ip_address, user_agent) VALUES (?, ?, ?, ?)');
            $stmt_sesion->bind_param('isss', $id, $token_sesion, $ip_address, $user_agent);
            $stmt_sesion->execute();
            $stmt_sesion->close();
            
            // Guardar en sesión PHP
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario'] = $usuario;
            $_SESSION['nombre'] = $nombre;
            $_SESSION['token_sesion'] = $token_sesion;
            
            if ($redirect) {
                header('Location: ' . $redirect);
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            echo '<script>alert("Contraseña incorrecta."); window.location="login.html";</script>';
        }
    } else {
        echo '<script>alert("Usuario no encontrado o inactivo."); window.location="login.html";</script>';
    }
    $stmt->close();
    $conn->close();
} else {
    echo '<script>alert("Por favor, completa todos los campos."); window.location="login.html";</script>';
}
?> 