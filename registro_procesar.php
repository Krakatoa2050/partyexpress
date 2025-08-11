<?php
require_once 'conexion.php';

// Recibir datos del formulario
$nombre = $_POST['nombre'] ?? '';
$usuario = $_POST['usuario'] ?? '';
$email = $_POST['email'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$redirect = $_POST['redirect'] ?? '';

// Validar que no estén vacíos
if ($nombre && $usuario && $email && $contrasena) {
    $conn = obtenerConexion();
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    try {
        $stmt = $conn->prepare('INSERT INTO usuarios (nombre, usuario, email, contrasena) VALUES (?, ?, ?, ?)');
        if (!$stmt) {
            throw new Exception('Error en prepare: ' . $conn->error);
        }
        if (!$stmt->bind_param('ssss', $nombre, $usuario, $email, $hash)) {
            throw new Exception('Error en bind_param: ' . $stmt->error);
        }
        if ($stmt->execute()) {
            $redir = 'login.html' . ($redirect ? ('?redirect=' . urlencode($redirect)) : '');
            echo '<script>alert("Registro exitoso. Ahora puedes iniciar sesión."); window.location="' . $redir . '";</script>';
        } else {
            throw new Exception('Error en execute: ' . $stmt->error);
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            echo '<script>alert("El usuario o correo ya existe."); window.location="login.html";</script>';
        } else {
            echo 'Error: ' . $e->getMessage();
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
    $conn->close();
} else {
    echo '<script>alert("Por favor, completa todos los campos."); window.location="login.html";</script>';
}
?> 