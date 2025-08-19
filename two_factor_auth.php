<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit();
}

// Función para generar secretos para 2FA
function generateSecret() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for ($i = 0; $i < 16; $i++) {
        $secret .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $secret;
}

// Función para generar códigos de respaldo
function generateBackupCodes() {
    $codes = [];
    for ($i = 0; $i < 8; $i++) {
        $codes[] = sprintf('%08d', rand(0, 99999999));
    }
    return $codes;
}

// Función para generar código QR
function generateQRCode($secret, $email) {
    $issuer = 'PartyExpress';
    $url = "otpauth://totp/{$issuer}:{$email}?secret={$secret}&issuer={$issuer}";
    return "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=" . urlencode($url);
}

// Función para verificar código TOTP
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

$mensaje_exito = '';
$mensaje_error = '';
$qr_code = '';
$backup_codes = [];

// Obtener información del usuario
$usuario = null;
try {
    $conn = obtenerConexion();
    $stmt = $conn->prepare('SELECT id, email, two_factor_enabled, two_factor_secret, backup_codes FROM usuarios WHERE id = ? AND activo = TRUE');
    $stmt->bind_param('i', $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
    }
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $mensaje_error = 'Error al cargar datos del usuario: ' . $e->getMessage();
}

// Procesar activación de 2FA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'activar_2fa':
            if (!$usuario['two_factor_enabled']) {
                $secret = generateSecret();
                $backup_codes = generateBackupCodes();
                $backup_codes_json = json_encode($backup_codes);
                
                try {
                    $conn = obtenerConexion();
                    $stmt = $conn->prepare('UPDATE usuarios SET two_factor_secret = ?, backup_codes = ? WHERE id = ?');
                    $stmt->bind_param('ssi', $secret, $backup_codes_json, $_SESSION['usuario_id']);
                    
                    if ($stmt->execute()) {
                        $qr_code = generateQRCode($secret, $usuario['email']);
                        $usuario['two_factor_secret'] = $secret;
                        $usuario['backup_codes'] = $backup_codes_json;
                        $mensaje_exito = 'Código QR generado. Escanea el código con tu aplicación de autenticación.';
                    } else {
                        $mensaje_error = 'Error al generar el código QR';
                    }
                    $stmt->close();
                    $conn->close();
                } catch (Exception $e) {
                    $mensaje_error = 'Error: ' . $e->getMessage();
                }
            }
            break;
            
        case 'verificar_2fa':
            $codigo = $_POST['codigo'] ?? '';
            if (!empty($codigo) && !empty($usuario['two_factor_secret'])) {
                if (verifyTOTP($usuario['two_factor_secret'], $codigo)) {
                    try {
                        $conn = obtenerConexion();
                        $stmt = $conn->prepare('UPDATE usuarios SET two_factor_enabled = 1 WHERE id = ?');
                        $stmt->bind_param('i', $_SESSION['usuario_id']);
                        
                        if ($stmt->execute()) {
                            $usuario['two_factor_enabled'] = 1;
                            $mensaje_exito = '¡Autenticación de dos factores activada correctamente!';
                        } else {
                            $mensaje_error = 'Error al activar la autenticación de dos factores';
                        }
                        $stmt->close();
                        $conn->close();
                    } catch (Exception $e) {
                        $mensaje_error = 'Error: ' . $e->getMessage();
                    }
                } else {
                    $mensaje_error = 'Código incorrecto. Intenta nuevamente.';
                }
            } else {
                $mensaje_error = 'Por favor ingresa el código de 6 dígitos';
            }
            break;
            
        case 'desactivar_2fa':
            $password = $_POST['password'] ?? '';
            if (!empty($password)) {
                try {
                    $conn = obtenerConexion();
                    $stmt = $conn->prepare('SELECT contrasena FROM usuarios WHERE id = ?');
                    $stmt->bind_param('i', $_SESSION['usuario_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $user_data = $result->fetch_assoc();
                        if (password_verify($password, $user_data['contrasena'])) {
                            $stmt = $conn->prepare('UPDATE usuarios SET two_factor_enabled = 0, two_factor_secret = NULL, backup_codes = NULL WHERE id = ?');
                            $stmt->bind_param('i', $_SESSION['usuario_id']);
                            
                            if ($stmt->execute()) {
                                $usuario['two_factor_enabled'] = 0;
                                $usuario['two_factor_secret'] = null;
                                $usuario['backup_codes'] = null;
                                $mensaje_exito = 'Autenticación de dos factores desactivada correctamente';
                            } else {
                                $mensaje_error = 'Error al desactivar la autenticación de dos factores';
                            }
                        } else {
                            $mensaje_error = 'Contraseña incorrecta';
                        }
                    }
                    $stmt->close();
                    $conn->close();
                } catch (Exception $e) {
                    $mensaje_error = 'Error: ' . $e->getMessage();
                }
            } else {
                $mensaje_error = 'Por favor ingresa tu contraseña';
            }
            break;
    }
}

// Cargar códigos de respaldo si existen
if (!empty($usuario['backup_codes'])) {
    $backup_codes = json_decode($usuario['backup_codes'], true);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticación de Dos Factores - PartyExpress</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .twofa-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .twofa-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .twofa-title {
            color: #a259f7;
            font-size: 2rem;
            margin: 0;
        }
        
        .btn-volver {
            background: rgba(162,89,247,0.2);
            color: #a259f7;
            border: 1px solid rgba(162,89,247,0.4);
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .twofa-card {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(162,89,247,0.2);
            margin-bottom: 25px;
        }
        
        .twofa-card h3 {
            color: #a259f7;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .mensaje {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .mensaje-exito {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .mensaje-error {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .qr-container {
            text-align: center;
            margin: 20px 0;
        }
        
        .qr-code {
            border: 2px solid rgba(162,89,247,0.3);
            border-radius: 10px;
            padding: 10px;
            background: white;
            display: inline-block;
        }
        
        .form-grupo {
            margin-bottom: 20px;
        }
        
        .form-label {
            color: #a259f7;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid rgba(162,89,247,0.3);
            border-radius: 15px;
            background: rgba(255,255,255,0.1);
            color: #fff;
            font-size: 1rem;
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
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(162,89,247,0.3);
        }
        
        .backup-codes {
            background: rgba(162,89,247,0.1);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .backup-codes h4 {
            color: #a259f7;
            margin-bottom: 15px;
        }
        
        .backup-codes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
        }
        
        .backup-code {
            background: rgba(255,255,255,0.1);
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            font-family: monospace;
            font-weight: bold;
            color: #a259f7;
        }
        
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-active {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .status-inactive {
            background: rgba(108, 117, 125, 0.2);
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }
        
        .info-card {
            background: rgba(162,89,247,0.1);
            border: 1px solid rgba(162,89,247,0.3);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-card h4 {
            color: #a259f7;
            margin-bottom: 10px;
        }
        
        .info-card p {
            color: #ccc;
            line-height: 1.6;
            margin: 0;
        }
    </style>
</head>
<body>
    <nav class="catalogo">
        <div class="logo-nombre">
            <img src="img/logo.jpg" alt="Logo PartyExpress" class="logo-img">
            <span class="logo-text">PartyExpress</span>
        </div>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="fiestas.php">Fiestas</a></li>
            <li><a href="lugares.php">Lugares</a></li>
            <li><a href="organizar.php">Organizar fiesta</a></li>
            <li><a href="contacto.php">Contacto</a></li>
        </ul>
        <span class="usuario-menu-container">
            <?php if (isset($_SESSION['usuario'])): ?>
                <div class="usuario-bienvenida">
                    <div class="usuario-avatar">
                        <?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?>
                    </div>
                    <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                </div>
                <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="mis_solicitudes.php" class="menu-item">
                        <i class="fa fa-calendar-check"></i> Mis Solicitudes
                    </a>
                    <a href="perfil.php" class="menu-item">
                        <i class="fa fa-user"></i> Mi Perfil
                    </a>
                    <a href="favoritos.php" class="menu-item">
                        <i class="fa fa-heart"></i> Favoritos
                    </a>
                    <a href="configuracion.php" class="menu-item">
                        <i class="fa fa-cog"></i> Configuración
                    </a>
                    
                    <form method="POST" action="logout.php" style="margin:0;">
                        <button type="submit" class="logout-btn">
                            <i class="fa fa-sign-out-alt"></i> Cerrar sesión
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <a href="login.html" class="login-btn">Iniciar sesión</a>
            <?php endif; ?>
        </span>
    </nav>

    <div class="twofa-container">
        <header class="twofa-header">
            <h1 class="twofa-title">
                <i class="fa fa-shield-alt"></i> Autenticación de Dos Factores
            </h1>
            <a href="seguridad.php" class="btn-volver">
                <i class="fa fa-arrow-left"></i> Volver a seguridad
            </a>
        </header>

        <?php if ($mensaje_exito): ?>
            <div class="mensaje mensaje-exito">
                <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($mensaje_exito); ?>
            </div>
        <?php endif; ?>

        <?php if ($mensaje_error): ?>
            <div class="mensaje mensaje-error">
                <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($mensaje_error); ?>
            </div>
        <?php endif; ?>

        <!-- Estado actual -->
        <div class="twofa-card">
            <h3><i class="fa fa-info-circle"></i> Estado Actual</h3>
            <div class="status-indicator <?php echo $usuario['two_factor_enabled'] ? 'status-active' : 'status-inactive'; ?>">
                <i class="fa fa-<?php echo $usuario['two_factor_enabled'] ? 'check-circle' : 'times-circle'; ?>"></i>
                <?php echo $usuario['two_factor_enabled'] ? 'Activado' : 'Desactivado'; ?>
            </div>
        </div>

        <?php if (!$usuario['two_factor_enabled']): ?>
            <!-- Activar 2FA -->
            <div class="twofa-card">
                <h3><i class="fa fa-plus-circle"></i> Activar Autenticación de Dos Factores</h3>
                
                <div class="info-card">
                    <h4>¿Qué es la autenticación de dos factores?</h4>
                    <p>La autenticación de dos factores añade una capa extra de seguridad a tu cuenta. Además de tu contraseña, necesitarás un código de 6 dígitos que se genera en tu aplicación de autenticación.</p>
                </div>

                <?php if (empty($usuario['two_factor_secret'])): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="activar_2fa">
                        <button type="submit" class="btn-submit">
                            <i class="fa fa-shield"></i> Generar Código QR
                        </button>
                    </form>
                <?php else: ?>
                    <div class="qr-container">
                        <h4>Escanea este código QR con tu aplicación de autenticación</h4>
                        <div class="qr-code">
                            <img src="<?php echo $qr_code; ?>" alt="Código QR para 2FA">
                        </div>
                        <p style="color: #ccc; margin-top: 15px;">
                            <strong>Aplicaciones recomendadas:</strong><br>
                            • Google Authenticator<br>
                            • Microsoft Authenticator<br>
                            • Authy
                        </p>
                    </div>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="verificar_2fa">
                        <div class="form-grupo">
                            <label for="codigo" class="form-label">Código de 6 dígitos</label>
                            <input type="text" id="codigo" name="codigo" class="form-input" 
                                   placeholder="000000" maxlength="6" pattern="[0-9]{6}" required>
                        </div>
                        <button type="submit" class="btn-submit">
                            <i class="fa fa-check"></i> Verificar y Activar
                        </button>
                    </form>

                    <?php if (!empty($backup_codes)): ?>
                        <div class="backup-codes">
                            <h4><i class="fa fa-key"></i> Códigos de Respaldo</h4>
                            <p style="color: #ccc; margin-bottom: 15px;">Guarda estos códigos en un lugar seguro. Los necesitarás si pierdes tu dispositivo de autenticación.</p>
                            <div class="backup-codes-grid">
                                <?php foreach ($backup_codes as $code): ?>
                                    <div class="backup-code"><?php echo $code; ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Desactivar 2FA -->
            <div class="twofa-card">
                <h3><i class="fa fa-minus-circle"></i> Desactivar Autenticación de Dos Factores</h3>
                
                <div class="info-card">
                    <h4>⚠️ Advertencia</h4>
                    <p>Desactivar la autenticación de dos factores reducirá la seguridad de tu cuenta. Solo hazlo si es absolutamente necesario.</p>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="desactivar_2fa">
                    <div class="form-grupo">
                        <label for="password" class="form-label">Contraseña actual</label>
                        <input type="password" id="password" name="password" class="form-input" required>
                    </div>
                    <button type="submit" class="btn-submit" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                        <i class="fa fa-times"></i> Desactivar 2FA
                    </button>
                </form>
            </div>

            <!-- Códigos de respaldo -->
            <?php if (!empty($backup_codes)): ?>
                <div class="twofa-card">
                    <h3><i class="fa fa-key"></i> Códigos de Respaldo</h3>
                    <p style="color: #ccc; margin-bottom: 15px;">Estos códigos te permiten acceder a tu cuenta si pierdes tu dispositivo de autenticación. Guárdalos en un lugar seguro.</p>
                    <div class="backup-codes-grid">
                        <?php foreach ($backup_codes as $code): ?>
                            <div class="backup-code"><?php echo $code; ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Información adicional -->
        <div class="twofa-card">
            <h3><i class="fa fa-question-circle"></i> Información Importante</h3>
            
            <div class="info-card">
                <h4>¿Cómo funciona?</h4>
                <p>1. Escanea el código QR con tu aplicación de autenticación<br>
                2. La aplicación generará códigos de 6 dígitos cada 30 segundos<br>
                3. Ingresa el código actual cuando inicies sesión<br>
                4. Guarda los códigos de respaldo en caso de emergencia</p>
            </div>
            
            <div class="info-card">
                <h4>Recomendaciones de seguridad</h4>
                <p>• Usa una aplicación de autenticación confiable<br>
                • No compartas tu código QR con nadie<br>
                • Guarda los códigos de respaldo en un lugar seguro<br>
                • Considera usar múltiples dispositivos para la autenticación</p>
            </div>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById("menuToggle");
        const dropdownMenu = document.getElementById("dropdownMenu");
        
        if (menuToggle && dropdownMenu) {
            menuToggle.onclick = function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
                menuToggle.classList.toggle('active');
            };
            
            document.addEventListener('click', function(e) {
                if (!dropdownMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                    menuToggle.classList.remove('active');
                }
            });
        }

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
        }
    </script>
</body>
</html>
