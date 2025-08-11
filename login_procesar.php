<?php
session_start();
require_once 'conexion.php';

$usuario = $_POST['usuario'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$redirect = $_POST['redirect'] ?? '';

if ($usuario && $contrasena) {
    $conn = obtenerConexion();
    $stmt = $conn->prepare('SELECT id, contrasena FROM usuarios WHERE usuario = ?');
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hash);
        $stmt->fetch();
        if (password_verify($contrasena, $hash)) {
            $_SESSION['usuario_id'] = $id;
            $_SESSION['usuario'] = $usuario;
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
        echo '<script>alert("Usuario no encontrado."); window.location="login.html";</script>';
    }
    $stmt->close();
    $conn->close();
} else {
    echo '<script>alert("Por favor, completa todos los campos."); window.location="login.html";</script>';
}
?> 