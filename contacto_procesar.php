<?php
session_start();
require_once 'conexion.php';

function h($v) { 
    return htmlspecialchars((string)$v ?? '', ENT_QUOTES, 'UTF-8'); 
}

$errores = [];
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$asunto = trim($_POST['asunto'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

// Validaciones
if ($nombre === '' || mb_strlen($nombre) < 2) {
    $errores[] = 'El nombre es obligatorio (mínimo 2 caracteres).';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El email es obligatorio y debe ser válido.';
}

if ($asunto === '') {
    $errores[] = 'El asunto es obligatorio.';
}

if ($mensaje === '' || mb_strlen($mensaje) < 10) {
    $errores[] = 'El mensaje es obligatorio (mínimo 10 caracteres).';
}

// Si no hay errores, guardar en base de datos
$mensaje_enviado = false;
$mensaje_id = null;

if (empty($errores)) {
    try {
        $conn = obtenerConexion();
        
        // Obtener usuario_id si está logueado
        $usuario_id = null;
        if (isset($_SESSION['usuario_id'])) {
            $usuario_id = $_SESSION['usuario_id'];
        }
        
        // Obtener IP del cliente
        $ip_remota = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $stmt = $conn->prepare('INSERT INTO mensajes_contacto (nombre, email, asunto, mensaje, usuario_id, ip_remota) VALUES (?, ?, ?, ?, ?, ?)');
        
        if (!$stmt) {
            throw new Exception('Error en prepare: ' . $conn->error);
        }
        
        if (!$stmt->bind_param('ssssis', $nombre, $email, $asunto, $mensaje, $usuario_id, $ip_remota)) {
            throw new Exception('Error en bind_param: ' . $stmt->error);
        }
        
        if ($stmt->execute()) {
            $mensaje_id = $conn->insert_id;
            $mensaje_enviado = true;
        } else {
            throw new Exception('Error en execute: ' . $stmt->error);
        }
        
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        $errores[] = 'Error al guardar el mensaje: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensaje de contacto - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="contacto-styles.css">
    <style>
        .resultado {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 40px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .resultado h2 {
            color: #a259f7;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
        
        .resultado p {
            color: #fff;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .alert.error {
            background: rgba(220,53,69,0.2);
            border: 1px solid rgba(220,53,69,0.4);
            color: #ff6b6b;
        }
        
        .alert.success {
            background: rgba(40,167,69,0.2);
            border: 1px solid rgba(40,167,69,0.4);
            color: #51cf66;
        }
        
        .detalles {
            background: rgba(45,25,80,0.6);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        
        .detalles h3 {
            color: #a259f7;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .detalle-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(162,89,247,0.2);
        }
        
        .detalle-item:last-child {
            border-bottom: none;
        }
        
        .detalle-label {
            color: #a259f7;
            font-weight: 600;
        }
        
        .detalle-valor {
            color: #fff;
        }
        
        .acciones {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #a259f7 60%, #7209b7 100%);
            color: #fff;
            border: none;
            cursor: pointer;
        }
        
        .btn-secondary {
            background: rgba(162,89,247,0.2);
            color: #a259f7;
            border: 1px solid rgba(162,89,247,0.4);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(162,89,247,0.3);
        }
    </style>
</head>
<body>
    <div class="contacto-container">
        <header class="contacto-header">
            <a href="contacto.php" class="contacto-back"><i class="fa fa-arrow-left"></i> Volver</a>
            <h1>Mensaje de contacto</h1>
            <?php if (isset($_SESSION['usuario'])): ?>
                <div class="contacto-user">Usuario: <strong><?php echo h($_SESSION['usuario']); ?></strong></div>
            <?php endif; ?>
        </header>

        <div class="resultado">
            <?php if ($mensaje_enviado): ?>
                <div class="alert success">
                    <i class="fa fa-check-circle"></i>
                    <strong>¡Mensaje enviado correctamente!</strong>
                </div>
                <h2>Gracias por contactarnos</h2>
                <p>Hemos recibido tu mensaje y nos pondremos en contacto contigo lo antes posible.</p>
                
                <div class="detalles">
                    <h3>Detalles del mensaje:</h3>
                    <div class="detalle-item">
                        <span class="detalle-label">Nombre:</span>
                        <span class="detalle-valor"><?php echo h($nombre); ?></span>
                    </div>
                    <div class="detalle-item">
                        <span class="detalle-label">Email:</span>
                        <span class="detalle-valor"><?php echo h($email); ?></span>
                    </div>
                    <div class="detalle-item">
                        <span class="detalle-label">Asunto:</span>
                        <span class="detalle-valor"><?php echo h($asunto); ?></span>
                    </div>
                    <div class="detalle-item">
                        <span class="detalle-label">Mensaje:</span>
                        <span class="detalle-valor"><?php echo nl2br(h($mensaje)); ?></span>
                    </div>
                </div>
                
                <p><small>Fecha de envío: <?php echo date('d/m/Y H:i'); ?></small></p>
            <?php else: ?>
                <div class="alert error">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>Error al enviar el mensaje</strong>
                </div>
                <h2>Revisa los siguientes errores:</h2>
                <ul style="text-align: left; color: #ff6b6b; margin: 20px 0;">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo h($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="acciones">
                <a href="contacto.php" class="btn btn-primary">Enviar otro mensaje</a>
                <a href="index.php" class="btn btn-secondary">Volver al inicio</a>
            </div>
        </div>
    </div>
</body>
</html> 