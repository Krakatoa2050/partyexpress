<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.html?redirect=' . urlencode('organizar.php'));
  exit();
}

function h($v){ return htmlspecialchars((string)$v ?? '', ENT_QUOTES, 'UTF-8'); }

$errores = [];
$titulo = trim($_POST['titulo'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$ubicacion = trim($_POST['ubicacion'] ?? '');
$fecha = trim($_POST['fecha'] ?? '');
$hora = trim($_POST['hora'] ?? '');
$capacidad = trim($_POST['capacidad'] ?? '');
$presupuesto = trim($_POST['presupuesto'] ?? '');
$privacidad = trim($_POST['privacidad'] ?? 'Público');
$contacto = trim($_POST['contacto'] ?? '');

if ($titulo === '' || mb_strlen($titulo) < 3) { $errores[] = 'El título es obligatorio (mínimo 3 caracteres).'; }
if ($categoria === '') { $errores[] = 'La categoría es obligatoria.'; }
if ($descripcion === '') { $errores[] = 'La descripción es obligatoria.'; }
if ($ubicacion === '') { $errores[] = 'La ubicación es obligatoria.'; }
if ($fecha === '') { $errores[] = 'La fecha es obligatoria.'; }
if ($hora === '') { $errores[] = 'La hora es obligatoria.'; }

$subidos = [];
$MAX_FILES = 10;
$MAX_SIZE = 8 * 1024 * 1024; // 8MB
$ALLOWED_MIME = [
  'image/jpeg','image/png','image/gif','image/webp','application/pdf'
];

$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($uploadDir)) {
  @mkdir($uploadDir, 0777, true);
}

if (!empty($_FILES['adjuntos']) && is_array($_FILES['adjuntos']['name'])) {
  $count = count($_FILES['adjuntos']['name']);
  for ($i = 0; $i < $count && count($subidos) < $MAX_FILES; $i++) {
    $err = $_FILES['adjuntos']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
    if ($err === UPLOAD_ERR_NO_FILE) { continue; }
    if ($err !== UPLOAD_ERR_OK) { $errores[] = 'Error al subir un archivo (código '.$err.').'; continue; }

    $name = $_FILES['adjuntos']['name'][$i] ?? 'archivo';
    $size = $_FILES['adjuntos']['size'][$i] ?? 0;
    $tmp  = $_FILES['adjuntos']['tmp_name'][$i] ?? '';

    if ($size > $MAX_SIZE) { $errores[] = 'Un archivo supera el tamaño máximo de 8MB: '.h($name); continue; }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($tmp);
    if (!in_array($mime, $ALLOWED_MIME, true)) { $errores[] = 'Tipo de archivo no permitido: '.h($name); continue; }

    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $safeBase = preg_replace('/[^a-zA-Z0-9-_]/','_', pathinfo($name, PATHINFO_FILENAME));
    $final = sprintf('%s_%s.%s', $safeBase !== '' ? $safeBase : 'archivo', uniqid(), $ext ?: 'bin');
    $dest = $uploadDir . DIRECTORY_SEPARATOR . $final;
    if (!@move_uploaded_file($tmp, $dest)) { $errores[] = 'No se pudo guardar: '.h($name); continue; }
    $subidos[] = 'uploads/'.$final;
  }
}

?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Solicitud de fiesta</title>
  <link rel="stylesheet" href="organizar-styles.css">
  <style>
    .resumen{ background:#fff; border-radius:16px; padding:20px; box-shadow:0 18px 50px rgba(0,0,0,.25); }
    .resumen h2{ margin-top:0; color:#3a0ca3; }
    .alert{ padding:12px 14px; border-radius:10px; margin-bottom:12px; }
    .alert.error{ background:#ffe6e6; color:#b00020; border:1px solid #ffb3b3; }
    .alert.ok{ background:#eefaf0; color:#1e7e34; border:1px solid #b7ebc6; }
    .grid{ display:grid; grid-template-columns: repeat(2,1fr); gap:12px; }
    .grid .full{ grid-column: span 2; }
    .files{ display:grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap:12px; margin-top:10px; }
    .file{ background:#fafafa; border:1px solid #eee; border-radius:10px; overflow:hidden; }
    .file img{ width:100%; height:120px; object-fit:cover; display:block; }
    .file a{ display:block; padding:8px 10px; text-decoration:none; color:#231942; font-size:.92rem; }
    .acciones{ margin-top:16px; display:flex; gap:10px; }
  </style>
</head>
<body>
  <div class="org-container">
    <header class="org-header">
      <a class="org-back" href="organizar.php"><i class="fa fa-arrow-left"></i> Volver</a>
      <h1>Resumen de tu solicitud</h1>
      <?php if (isset($_SESSION['usuario'])): ?>
        <div class="org-user">Usuario: <strong><?php echo h($_SESSION['usuario']); ?></strong></div>
      <?php endif; ?>
    </header>

    <div class="resumen">
      <?php if ($errores): ?>
        <div class="alert error">
          <strong>Revisá los siguientes errores:</strong>
          <ul>
            <?php foreach ($errores as $e): ?><li><?php echo h($e); ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php else: ?>
        <div class="alert ok">Tu solicitud se envió correctamente.</div>
      <?php endif; ?>

      <div class="grid">
        <div><strong>Título:</strong> <?php echo h($titulo); ?></div>
        <div><strong>Categoría:</strong> <?php echo h($categoria); ?></div>
        <div><strong>Ubicación:</strong> <?php echo h($ubicacion); ?></div>
        <div><strong>Fecha y hora:</strong> <?php echo h($fecha); ?> <?php echo h($hora); ?></div>
        <div><strong>Capacidad:</strong> <?php echo h($capacidad); ?></div>
        <div><strong>Presupuesto:</strong> <?php echo h($presupuesto); ?></div>
        <div><strong>Privacidad:</strong> <?php echo h($privacidad); ?></div>
        <div><strong>Contacto:</strong> <?php echo h($contacto); ?></div>
        <div class="full"><strong>Descripción:</strong><br><?php echo nl2br(h($descripcion)); ?></div>
      </div>

      <?php if ($subidos): ?>
        <h2>Archivos subidos</h2>
        <div class="files">
          <?php foreach ($subidos as $path): ?>
            <div class="file">
              <?php if (@getimagesize($path)): ?>
                <img src="<?php echo h($path); ?>" alt="">
              <?php endif; ?>
              <a href="<?php echo h($path); ?>" target="_blank" rel="noopener">Ver archivo</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="acciones">
        <a class="button" href="organizar.php">Crear otra solicitud</a>
        <a class="button alt" href="index.php">Volver al inicio</a>
      </div>
    </div>
  </div>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</body>
</html> 