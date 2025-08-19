<?php
session_start();
require_once 'conexion.php';

// Función para verificar código TOTP (copiada de two_factor_auth.php)
function verifyTOTP($secret, $code) {
    $timeSlice = floor(time() / 30);
    
    // Verificar código actual y códigos adyacentes (para sincronización)
    for ($i = -1; $i <= 1; $i++) {
        $checkTime = $timeSlice + $i;
        $checkCode = generateTOTP($secret, $checkTime);
        if ($checkCode === $code) {
            return true;
        }
    }
    return false;
}

// Función para generar código TOTP
function generateTOTP($secret, $timeSlice) {
    $secretKey = base32_decode($secret);
    $time = pack('N*', 0) . pack('N*', $timeSlice);
    $hash = hash_hmac('SHA1', $time, $secretKey, true);
    $offset = ord($hash[19]) & 0xf;
    $code = (
        ((ord($hash[$offset]) & 0x7f) << 24) |
        ((ord($hash[$offset + 1]) & 0xff) << 16) |
        ((ord($hash[$offset + 2]) & 0xff) << 8) |
        (ord($hash[$offset + 3]) & 0xff)
    ) % 1000000;
    return str_pad($code, 6, '0', STR_PAD_LEFT);
}

// Función para decodificar base32
function base32_decode($secret) {
    $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $base32charsFlipped = array_flip(str_split($base32chars));
    
    $paddingCharCount = substr_count($secret, $base32chars[32]);
    $allowedValues = array(6, 4, 3, 1, 0);
    if (!in_array($paddingCharCount, $allowedValues)) return false;
    for ($i = 0; $i < 4; ++$i) {
        if ($paddingCharCount == $allowedValues[$i] &&
            substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])) return false;
    }
    $secret = str_replace('=', '', $secret);
    $secret = str_split($secret);
    $binaryString = "";
    for ($i = 0; $i < count($secret); $i = $i + 8) {
        $x = "";
        if (!in_array($secret[$i], $base32chars)) return false;
        for ($j = 0; $j < 8; ++$j) {
            $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
        }
        $eightBits = str_split($x, 8);
        for ($z = 0; $z < count($eightBits); ++$z) {
            $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : "";
        }
    }
    return $binaryString;
}

// Función para verificar códigos de respaldo
function verifyBackupCode($backup_codes_json, $code) {
    if (empty($backup_codes_json)) return false;
    
    $backup_codes = json_decode($backup_codes_json, true);
    if (!is_array($backup_codes)) return false;
    
    $index = array_search($code, $backup_codes);
    if ($index !== false) {
        // Remover el código usado
        unset($backup_codes[$index]);
        $backup_codes = array_values($backup_codes); // Reindexar array
        
        // Actualizar en la base de datos
        try {
            $conn = obtenerConexion();
            $stmt = $conn->prepare('UPDATE usuarios SET backup_codes = ? WHERE id = ?');
            $new_backup_codes = json_encode($backup_codes);
            $stmt->bind_param('si', $new_backup_codes, $_SESSION['temp_user_id']);
            $stmt->execute();
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            // Si falla la actualización, no es crítico para el login
        }
        
        return true;
    }
    
    return false;
}

$mensaje_error = '';

// Verificar que el usuario esté en proceso de verificación 2FA
if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['temp_user_email'])) {
    header('Location: login.html');
    exit();
}

// Procesar verificación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'] ?? '';
    $tipo = $_POST['tipo'] ?? 'totp'; // 'totp' o 'backup'
    
    if (!empty($codigo)) {
        try {
            $conn = obtenerConexion();
            $stmt = $conn->prepare('SELECT id, usuario, nombre, email, two_factor_enabled, two_factor_secret, backup_codes FROM usuarios WHERE id = ? AND activo = TRUE');
            $stmt->bind_param('i', $_SESSION['temp_user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $usuario = $result->fetch_assoc();
                
                $verificado = false;
                
                if ($tipo === 'totp' && !empty($usuario['two_factor_secret'])) {
                    $verificado = verifyTOTP($usuario['two_factor_secret'], $codigo);
                } elseif ($tipo === 'backup') {
                    $verificado = verifyBackupCode($usuario['backup_codes'], $codigo);
                }
                
                if ($verificado) {
                    // Verificación exitosa, completar login
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario'] = $usuario['usuario'];
                    $_SESSION['nombre'] = $usuario['nombre'];
                    $_SESSION['email'] = $usuario['email'];
                    
                    // Limpiar variables temporales
                    unset($_SESSION['temp_user_id']);
                    unset($_SESSION['temp_user_email']);
                    
                    // Redirigir al usuario
                    $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
                    unset($_SESSION['redirect_after_login']);
                    header('Location: ' . $redirect);
                    exit();
                } else {
                    $mensaje_error = 'Código incorrecto. Intenta nuevamente.';
                }
            } else {
                $mensaje_error = 'Usuario no encontrado.';
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $mensaje_error = 'Error al verificar el código: ' . $e->getMessage();
        }
    } else {
        $mensaje_error = 'Por favor ingresa el código de 6 dígitos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Dos Factores - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .verificacion-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .verificacion-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 40px;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(162,89,247,0.2);
            text-align: center;
        }
        
        .verificacion-title {
            color: #a259f7;
            font-size: 2rem;
            margin-bottom: 20px;
        }
        
        .verificacion-subtitle {
            color: #ccc;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .mensaje-error {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .form-grupo {
            margin-bottom: 25px;
        }
        
        .form-label {
            color: #a259f7;
            font-weight: 600;
            margin-bottom: 10px;
            display: block;
            font-size: 1.1rem;
        }
        
        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid rgba(162,89,247,0.3);
            border-radius: 15px;
            background: rgba(255,255,255,0.1);
            color: #fff;
            font-size: 1.2rem;
            text-align: center;
            letter-spacing: 3px;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #a259f7;
            box-shadow: 0 0 20px rgba(162,89,247,0.3);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #a259f7 0%, #7209b7 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            width: 100%;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(162,89,247,0.3);
        }
        
        .tipo-verificacion {
            margin-bottom: 20px;
        }
        
        .tipo-verificacion label {
            display: inline-block;
            margin: 0 10px;
            color: #ccc;
            cursor: pointer;
        }
        
        .tipo-verificacion input[type="radio"] {
            margin-right: 5px;
        }
        
        .info-text {
            color: #999;
            font-size: 0.9rem;
            margin-top: 15px;
        }
        
        .backup-link {
            color: #a259f7;
            text-decoration: none;
            font-weight: 600;
            margin-top: 15px;
            display: inline-block;
        }
        
        .backup-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="verificacion-container">
        <div class="verificacion-card">
            <h1 class="verificacion-title">
                <i class="fa fa-shield-alt"></i> Verificación de Dos Factores
            </h1>
            
            <p class="verificacion-subtitle">
                Ingresa el código de 6 dígitos de tu aplicación de autenticación
            </p>

            <?php if ($mensaje_error): ?>
                <div class="mensaje-error">
                    <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($mensaje_error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="tipo-verificacion">
                    <label>
                        <input type="radio" name="tipo" value="totp" checked> Código de aplicación
                    </label>
                    <label>
                        <input type="radio" name="tipo" value="backup"> Código de respaldo
                    </label>
                </div>

                <div class="form-grupo">
                    <label for="codigo" class="form-label">Código de 6 dígitos</label>
                    <input type="text" id="codigo" name="codigo" class="form-input" 
                           placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autocomplete="off">
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa fa-check"></i> Verificar y Continuar
                </button>
            </form>

            <div class="info-text">
                <p><strong>Aplicaciones compatibles:</strong></p>
                <p>• Google Authenticator</p>
                <p>• Microsoft Authenticator</p>
                <p>• Authy</p>
                <p>• Cualquier aplicación TOTP</p>
            </div>

            <a href="login.html" class="backup-link">
                <i class="fa fa-arrow-left"></i> Volver al login
            </a>
        </div>
    </div>

    <script>
        // Auto-formatear código de 6 dígitos
        const codigoInput = document.getElementById('codigo');
        if (codigoInput) {
            codigoInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 6) {
                    value = value.substring(0, 6);
                }
                e.target.value = value;
            });
            
            // Enfocar el input al cargar la página
            codigoInput.focus();
        }

        // Cambiar placeholder según el tipo de verificación
        const tipoInputs = document.querySelectorAll('input[name="tipo"]');
        const codigoLabel = document.querySelector('.form-label');
        
        tipoInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.value === 'totp') {
                    codigoLabel.textContent = 'Código de 6 dígitos';
                    codigoInput.placeholder = '000000';
                } else {
                    codigoLabel.textContent = 'Código de respaldo';
                    codigoInput.placeholder = '12345678';
                }
            });
        });
    </script>
</body>
</html>
