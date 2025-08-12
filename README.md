# PartyExpress - Sistema de Organización de Eventos

## Descripción
PartyExpress es una plataforma web para organizar y gestionar eventos y fiestas. Permite a los usuarios registrarse, crear solicitudes de eventos, subir archivos adjuntos y contactar con el equipo de soporte.

## Estructura de la Base de Datos

### Tablas Principales

#### 1. `usuarios`
Almacena información de usuarios registrados
- `id`: Identificador único
- `nombre`: Nombre completo del usuario
- `usuario`: Nombre de usuario único
- `email`: Correo electrónico único
- `contrasena`: Contraseña hasheada
- `fecha_registro`: Fecha de registro
- `ultimo_acceso`: Último acceso del usuario
- `activo`: Estado activo/inactivo

#### 2. `categorias_eventos`
Categorías predefinidas para los eventos
- `id`: Identificador único
- `nombre`: Nombre de la categoría
- `descripcion`: Descripción de la categoría
- `icono`: Icono de Font Awesome
- `color`: Color hexadecimal
- `activa`: Estado activo/inactivo
- `fecha_creacion`: Fecha de creación

#### 3. `solicitudes_eventos`
Solicitudes de organización de eventos
- `id`: Identificador único
- `usuario_id`: ID del usuario que creó la solicitud
- `titulo`: Título del evento
- `categoria_id`: ID de la categoría
- `descripcion`: Descripción del evento
- `ubicacion`: Ubicación del evento
- `fecha_evento`: Fecha del evento
- `hora_evento`: Hora del evento
- `capacidad`: Capacidad estimada
- `presupuesto`: Presupuesto aproximado
- `privacidad`: Nivel de privacidad
- `contacto`: Información de contacto
- `estado`: Estado de la solicitud
- `fecha_creacion`: Fecha de creación
- `fecha_actualizacion`: Fecha de última actualización

#### 4. `archivos_adjuntos`
Archivos subidos con las solicitudes
- `id`: Identificador único
- `solicitud_id`: ID de la solicitud
- `nombre_original`: Nombre original del archivo
- `nombre_archivo`: Nombre del archivo en el servidor
- `ruta_archivo`: Ruta del archivo
- `tipo_mime`: Tipo MIME del archivo
- `tamano_bytes`: Tamaño en bytes
- `fecha_subida`: Fecha de subida

#### 5. `mensajes_contacto`
Mensajes del formulario de contacto
- `id`: Identificador único
- `nombre`: Nombre del remitente
- `email`: Correo del remitente
- `asunto`: Asunto del mensaje
- `mensaje`: Contenido del mensaje
- `usuario_id`: ID del usuario (si está logueado)
- `ip_remota`: IP del remitente
- `estado`: Estado del mensaje
- `fecha_envio`: Fecha de envío
- `fecha_respuesta`: Fecha de respuesta

#### 6. `sesiones_usuarios`
Control de sesiones activas
- `id`: Identificador único
- `usuario_id`: ID del usuario
- `token_sesion`: Token único de sesión
- `ip_address`: IP del cliente
- `user_agent`: User agent del navegador
- `fecha_inicio`: Fecha de inicio de sesión
- `fecha_ultimo_acceso`: Último acceso
- `activa`: Estado activo/inactivo

#### 7. `logs_actividad`
Registro de actividades del sistema
- `id`: Identificador único
- `usuario_id`: ID del usuario
- `accion`: Acción realizada
- `tabla_afectada`: Tabla afectada
- `registro_id`: ID del registro afectado
- `detalles`: Detalles en formato JSON
- `ip_address`: IP del cliente
- `user_agent`: User agent
- `fecha`: Fecha de la actividad

### Vistas Útiles

#### `vista_solicitudes_completas`
Vista que combina información de solicitudes, usuarios y categorías con conteo de archivos adjuntos.

#### `vista_mensajes_contacto`
Vista que combina mensajes de contacto con información de usuarios registrados.

### Procedimientos Almacenados

#### `sp_estadisticas_sistema()`
Obtiene estadísticas generales del sistema:
- Total de usuarios activos
- Total de solicitudes
- Solicitudes pendientes
- Mensajes nuevos
- Total de archivos

#### `sp_limpiar_sesiones_expiradas()`
Limpia sesiones que han expirado (más de 24 horas).

### Triggers

#### `tr_actualizar_ultimo_acceso`
Actualiza el último acceso del usuario cuando se crea una nueva sesión.

#### `tr_log_actividad_usuarios`
Registra la creación de nuevos usuarios en el log de actividad.

## Instalación

1. **Configurar la base de datos:**
   ```sql
   -- Ejecutar el archivo db.sql en MySQL/MariaDB
   source db.sql;
   ```

2. **Configurar la conexión:**
   - Editar `conexion.php` con los datos de tu servidor de base de datos

3. **Configurar el servidor web:**
   - Asegurar que PHP tenga permisos de escritura en la carpeta `uploads/`

## Características del Sistema

### Gestión de Usuarios
- Registro de usuarios
- Inicio de sesión seguro
- Control de sesiones
- Logout con limpieza de sesiones

### Gestión de Eventos
- Creación de solicitudes de eventos
- Categorización automática
- Subida de archivos adjuntos
- Estados de solicitud (Pendiente, En revisión, Aprobado, etc.)

### Sistema de Contacto
- Formulario de contacto
- Asociación con usuarios registrados
- Estados de mensajes
- Registro de IP y user agent

### Seguridad
- Contraseñas hasheadas con `password_hash()`
- Protección contra SQL injection con prepared statements
- Validación de archivos subidos
- Control de sesiones en base de datos

### Optimización
- Índices en campos frecuentemente consultados
- Vistas para consultas complejas
- Procedimientos almacenados para operaciones comunes

## Archivos Principales

- `db.sql`: Script completo de la base de datos
- `conexion.php`: Configuración de conexión a la BD
- `login.html` / `login_procesar.php`: Sistema de autenticación
- `registro_procesar.php`: Registro de usuarios
- `organizar.php` / `organizar_procesar.php`: Creación de solicitudes
- `contacto.php` / `contacto_procesar.php`: Sistema de contacto
- `mis_solicitudes.php`: Vista de solicitudes del usuario
- `logout.php`: Cierre de sesión

## Tecnologías Utilizadas

- **Backend:** PHP 7.4+
- **Base de Datos:** MySQL 5.7+ / MariaDB 10.3+
- **Frontend:** HTML5, CSS3, JavaScript
- **Iconos:** Font Awesome 6.4.2
- **Servidor:** Apache/Nginx con PHP
