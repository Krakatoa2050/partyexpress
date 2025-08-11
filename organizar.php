<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header('Location: login.html?redirect=' . urlencode('organizar.php'));
  exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Organizar tu fiesta</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="organizar-styles.css" />
</head>
<body>
  <div class="org-container">
    <header class="org-header">
      <a href="index.php" class="org-back"><i class="fa fa-arrow-left"></i> Volver</a>
      <h1>Organizá tu fiesta</h1>
      <?php if (isset($_SESSION['usuario'])): ?>
        <div class="org-user">Hola, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></div>
      <?php endif; ?>
    </header>

    <main class="org-card">
      <form class="org-form" action="organizar_procesar.php" method="POST" enctype="multipart/form-data" novalidate>
        <div class="org-grid">
          <div class="org-field">
            <label for="titulo">Título del evento</label>
            <input type="text" id="titulo" name="titulo" placeholder="Ej: Fiesta de cumpleaños 25" required minlength="3">
          </div>
          <div class="org-field">
            <label for="categoria">Categoría</label>
            <select id="categoria" name="categoria" required>
              <option value="" disabled selected>Elegí una categoría</option>
              <option>Electrónica</option>
              <option>Bares</option>
              <option>Fiestas privadas</option>
              <option>Conciertos</option>
              <option>Salones de eventos</option>
              <option>Otros</option>
            </select>
          </div>
          <div class="org-field org-col-2">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="5" placeholder="Contanos cómo querés que sea tu fiesta (música, decoración, comida, invitados, etc.)" required></textarea>
          </div>
          <div class="org-field">
            <label for="ubicacion">Ubicación</label>
            <input type="text" id="ubicacion" name="ubicacion" placeholder="Ciudad, dirección o lugar" required>
          </div>
          <div class="org-field">
            <label for="fecha">¿Cuándo querés hacer la fiesta?</label>
            <input type="date" id="fecha" name="fecha" required>
          </div>
          <div class="org-field">
            <label for="hora">Hora</label>
            <input type="time" id="hora" name="hora" required>
          </div>
          <div class="org-field">
            <label for="capacidad">Capacidad estimada</label>
            <input type="number" id="capacidad" name="capacidad" min="1" step="1" placeholder="Ej: 50">
          </div>
          <div class="org-field">
            <label for="presupuesto">Presupuesto aproximado (Gs.)</label>
            <input type="number" id="presupuesto" name="presupuesto" min="0" step="10000" placeholder="Ej: 1500000">
          </div>
          <div class="org-field">
            <label for="privacidad">Privacidad</label>
            <select id="privacidad" name="privacidad">
              <option>Público</option>
              <option>Privado</option>
            </select>
          </div>
          <div class="org-field">
            <label for="contacto">Contacto</label>
            <input type="text" id="contacto" name="contacto" placeholder="Email o teléfono" autocomplete="email">
          </div>
          
          <div class="org-field org-col-2">
            <label>Adjuntar archivos (imágenes o PDF)</label>
            <div class="dropzone" id="dropzone">
              <input type="file" id="adjuntos" name="adjuntos[]" accept="image/*,.pdf" multiple hidden>
              <i class="fa fa-cloud-arrow-up"></i>
              <p>Arrastrá y soltá aquí, o <button type="button" class="link" id="btn-file">explorá tus archivos</button></p>
              <small>Hasta 10 archivos. Máximo 8MB por archivo.</small>
            </div>
            <div class="preview-grid" id="preview"></div>
          </div>
        </div>

        <div class="org-actions">
          <button class="button" type="submit">Enviar solicitud</button>
          <a href="index.php" class="button alt">Cancelar</a>
        </div>
      </form>
    </main>
  </div>

  <script>
  const dropzone = document.getElementById('dropzone');
  const fileInput = document.getElementById('adjuntos');
  const btnFile = document.getElementById('btn-file');
  const preview = document.getElementById('preview');

  function bytesToSize(bytes){
    const sizes=['Bytes','KB','MB','GB'];
    if(bytes===0) return '0 Byte';
    const i=parseInt(Math.floor(Math.log(bytes)/Math.log(1024)),10);
    return Math.round(bytes/Math.pow(1024,i),2)+' '+sizes[i];
  }

  function renderPreview(files){
    preview.innerHTML='';
    Array.from(files).forEach((file)=>{
      const item=document.createElement('div');
      item.className='preview-item';
      const isImage=file.type.startsWith('image/');
      if(isImage){
        const img=document.createElement('img');
        img.alt=file.name;
        img.src=URL.createObjectURL(file);
        item.appendChild(img);
      } else {
        const icon=document.createElement('div');
        icon.className='file-icon';
        icon.innerHTML='<i class="fa fa-file-pdf"></i>';
        item.appendChild(icon);
      }
      const meta=document.createElement('div');
      meta.className='meta';
      meta.innerHTML=`<span class="name">${file.name}</span><span class="size">${bytesToSize(file.size)}</span>`;
      item.appendChild(meta);
      preview.appendChild(item);
    });
  }

  function setFilesAndPreview(fileList){
    const max=10;
    const files=Array.from(fileList).slice(0,max);
    const dt=new DataTransfer();
    files.forEach(f=>dt.items.add(f));
    fileInput.files=dt.files;
    renderPreview(files);
  }

  btnFile?.addEventListener('click',()=>fileInput.click());

  ;['dragenter','dragover'].forEach(evt=>dropzone.addEventListener(evt,(e)=>{
    e.preventDefault();
    e.stopPropagation();
    dropzone.classList.add('dragover');
  }));
  ;['dragleave','drop'].forEach(evt=>dropzone.addEventListener(evt, (e)=>{
    e.preventDefault();
    e.stopPropagation();
    dropzone.classList.remove('dragover');
  }));
  dropzone.addEventListener('drop',(e)=>{
    const files=e.dataTransfer?.files;
    if(files && files.length){ setFilesAndPreview(files); }
  });
  fileInput.addEventListener('change',()=>{ if(fileInput.files?.length){ renderPreview(fileInput.files); } });

  // Submit UX
  document.querySelector('.org-form')?.addEventListener('submit',(e)=>{
    const form=e.currentTarget;
    if(!form.checkValidity()){
      e.preventDefault();
      form.classList.remove('form-invalid');
      void form.offsetWidth;
      form.classList.add('form-invalid');
    } else {
      const submitBtn=form.querySelector('button[type="submit"]');
      if(submitBtn){ submitBtn.disabled=true; submitBtn.textContent='Enviando...'; }
    }
  });
  </script>
</body>
</html> 