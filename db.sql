-- =====================================================
-- SCRIPT DE BASE DE DATOS PARA PARTYEXPRESS
-- =====================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS partyexpress CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE partyexpress;

-- =====================================================
-- TABLA DE USUARIOS
-- =====================================================
    CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        usuario VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        contrasena VARCHAR(255) NOT NULL,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ultimo_acceso TIMESTAMP NULL,
        activo BOOLEAN DEFAULT TRUE,
        INDEX idx_usuario (usuario),
        INDEX idx_email (email)
    );

    -- =====================================================
    -- TABLA DE CATEGORÍAS DE EVENTOS
    -- =====================================================
    CREATE TABLE IF NOT EXISTS categorias_eventos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL UNIQUE,
        descripcion TEXT,
        icono VARCHAR(50),
        color VARCHAR(7) DEFAULT '#a259f7',
        activa BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Insertar categorías por defecto
    INSERT IGNORE INTO categorias_eventos (nombre, descripcion, icono, color) VALUES
    ('Cumpleaños', 'Celebraciones de cumpleaños para todas las edades', 'fa-birthday-cake', '#ff6b6b'),
    ('Boda', 'Celebraciones de matrimonio y compromisos', 'fa-heart', '#ff9ff3'),
    ('Graduación', 'Celebraciones de graduación y logros académicos', 'fa-graduation-cap', '#54a0ff'),
    ('Aniversario', 'Celebraciones de aniversarios de pareja o empresa', 'fa-calendar-heart', '#5f27cd'),
    ('Evento Corporativo', 'Eventos empresariales y corporativos', 'fa-briefcase', '#00d2d3'),
    ('Fiesta Temática', 'Fiestas con temáticas específicas', 'fa-mask', '#ff9f43'),
    ('Baby Shower', 'Celebraciones para futuros padres', 'fa-baby', '#a55eea'),
    ('Despedida', 'Despedidas de soltero/a y despedidas de trabajo', 'fa-glass-cheers', '#26de81'),
    ('Otro', 'Otros tipos de eventos y celebraciones', 'fa-star', '#a259f7');

    -- =====================================================
    -- TABLA DE SOLICITUDES DE EVENTOS
    -- =====================================================
    CREATE TABLE IF NOT EXISTS solicitudes_eventos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        titulo VARCHAR(200) NOT NULL,
        categoria_id INT NOT NULL,
        descripcion TEXT NOT NULL,
        ubicacion VARCHAR(255) NOT NULL,
        fecha_evento DATE NOT NULL,
        hora_evento TIME NOT NULL,
        capacidad INT,
        presupuesto DECIMAL(10,2),
        privacidad ENUM('Público', 'Privado', 'Solo invitados') DEFAULT 'Público',
        contacto VARCHAR(255),
        estado ENUM('Pendiente', 'En revisión', 'Aprobado', 'Rechazado', 'Cancelado') DEFAULT 'Pendiente',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (categoria_id) REFERENCES categorias_eventos(id) ON DELETE RESTRICT,
        
        INDEX idx_usuario (usuario_id),
        INDEX idx_categoria (categoria_id),
        INDEX idx_fecha_evento (fecha_evento),
        INDEX idx_estado (estado),
        INDEX idx_fecha_creacion (fecha_creacion)
    );

    -- =====================================================
    -- TABLA DE ARCHIVOS ADJUNTOS
    -- =====================================================
    CREATE TABLE IF NOT EXISTS archivos_adjuntos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        solicitud_id INT NOT NULL,
        nombre_original VARCHAR(255) NOT NULL,
        nombre_archivo VARCHAR(255) NOT NULL,
        ruta_archivo VARCHAR(500) NOT NULL,
        tipo_mime VARCHAR(100),
        tamano_bytes BIGINT,
        fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        FOREIGN KEY (solicitud_id) REFERENCES solicitudes_eventos(id) ON DELETE CASCADE,
        
        INDEX idx_solicitud (solicitud_id),
        INDEX idx_tipo_mime (tipo_mime)
    );

    -- =====================================================
    -- TABLA DE MENSAJES DE CONTACTO
    -- =====================================================
    CREATE TABLE IF NOT EXISTS mensajes_contacto (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        asunto VARCHAR(200) NOT NULL,
        mensaje TEXT NOT NULL,
        usuario_id INT NULL,
        ip_remota VARCHAR(45),
        estado ENUM('Nuevo', 'Leído', 'Respondido', 'Archivado') DEFAULT 'Nuevo',
        fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_respuesta TIMESTAMP NULL,
        
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
        
        INDEX idx_email (email),
        INDEX idx_usuario (usuario_id),
        INDEX idx_estado (estado),
        INDEX idx_fecha_envio (fecha_envio)
    );

    -- =====================================================
    -- TABLA DE SESIONES DE USUARIO
    -- =====================================================
    CREATE TABLE IF NOT EXISTS sesiones_usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        token_sesion VARCHAR(255) NOT NULL UNIQUE,
        ip_address VARCHAR(45),
        user_agent TEXT,
        fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_ultimo_acceso TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        activa BOOLEAN DEFAULT TRUE,
        
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        
        INDEX idx_usuario (usuario_id),
        INDEX idx_token (token_sesion),
        INDEX idx_activa (activa)
    );

    -- =====================================================
    -- TABLA DE LOGS DE ACTIVIDAD
    -- =====================================================
    CREATE TABLE IF NOT EXISTS logs_actividad (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NULL,
        accion VARCHAR(100) NOT NULL,
        tabla_afectada VARCHAR(50),
        registro_id INT,
        detalles JSON,
        ip_address VARCHAR(45),
        user_agent TEXT,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
        
        INDEX idx_usuario (usuario_id),
        INDEX idx_accion (accion),
        INDEX idx_fecha (fecha)
    );

    -- =====================================================
    -- VISTAS ÚTILES
    -- =====================================================

    -- Vista para solicitudes con información completa
    CREATE OR REPLACE VIEW vista_solicitudes_completas AS
    SELECT 
        se.id,
        se.titulo,
        se.descripcion,
        se.ubicacion,
        se.fecha_evento,
        se.hora_evento,
        se.capacidad,
        se.presupuesto,
        se.privacidad,
        se.contacto,
        se.estado,
        se.fecha_creacion,
        u.nombre as nombre_usuario,
        u.usuario as usuario,
        u.email as email_usuario,
        ce.nombre as categoria_nombre,
        ce.icono as categoria_icono,
        ce.color as categoria_color,
        COUNT(aa.id) as total_archivos
    FROM solicitudes_eventos se
    JOIN usuarios u ON se.usuario_id = u.id
    JOIN categorias_eventos ce ON se.categoria_id = ce.id
    LEFT JOIN archivos_adjuntos aa ON se.id = aa.solicitud_id
    GROUP BY se.id;

    -- Vista para mensajes de contacto con información de usuario
    CREATE OR REPLACE VIEW vista_mensajes_contacto AS
    SELECT 
        mc.id,
        mc.nombre,
        mc.email,
        mc.asunto,
        mc.mensaje,
        mc.estado,
        mc.fecha_envio,
        mc.fecha_respuesta,
        u.usuario as usuario_registrado,
        u.nombre as nombre_usuario_registrado
    FROM mensajes_contacto mc
    LEFT JOIN usuarios u ON mc.usuario_id = u.id;

    -- =====================================================
    -- PROCEDIMIENTOS ALMACENADOS ÚTILES
    -- =====================================================

    DELIMITER //

    -- Procedimiento para obtener estadísticas del sistema
    CREATE PROCEDURE sp_estadisticas_sistema()
    BEGIN
        SELECT 
            (SELECT COUNT(*) FROM usuarios WHERE activo = TRUE) as total_usuarios_activos,
            (SELECT COUNT(*) FROM solicitudes_eventos) as total_solicitudes,
            (SELECT COUNT(*) FROM solicitudes_eventos WHERE estado = 'Pendiente') as solicitudes_pendientes,
            (SELECT COUNT(*) FROM mensajes_contacto WHERE estado = 'Nuevo') as mensajes_nuevos,
            (SELECT COUNT(*) FROM archivos_adjuntos) as total_archivos;
    END //

    -- Procedimiento para limpiar sesiones expiradas (más de 24 horas)
    CREATE PROCEDURE sp_limpiar_sesiones_expiradas()
    BEGIN
        UPDATE sesiones_usuarios 
        SET activa = FALSE 
        WHERE fecha_ultimo_acceso < DATE_SUB(NOW(), INTERVAL 24 HOUR) 
        AND activa = TRUE;
    END //

    DELIMITER ;

    -- =====================================================
    -- TRIGGERS PARA MANTENER INTEGRIDAD
    -- =====================================================

    DELIMITER //

    -- Trigger para actualizar último acceso del usuario
    CREATE TRIGGER tr_actualizar_ultimo_acceso
    AFTER INSERT ON sesiones_usuarios
    FOR EACH ROW
    BEGIN
        UPDATE usuarios 
        SET ultimo_acceso = NOW() 
        WHERE id = NEW.usuario_id;
    END //

    -- Trigger para registrar actividad de usuarios
    CREATE TRIGGER tr_log_actividad_usuarios
    AFTER INSERT ON usuarios
    FOR EACH ROW
    BEGIN
        INSERT INTO logs_actividad (usuario_id, accion, tabla_afectada, registro_id, detalles)
        VALUES (NEW.id, 'CREAR', 'usuarios', NEW.id, JSON_OBJECT('usuario', NEW.usuario, 'email', NEW.email));
    END //

    DELIMITER ;

    -- =====================================================
    -- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
    -- =====================================================

    -- Índices compuestos para consultas frecuentes
    CREATE INDEX idx_solicitudes_fecha_estado ON solicitudes_eventos(fecha_evento, estado);
    CREATE INDEX idx_mensajes_fecha_estado ON mensajes_contacto(fecha_envio, estado);
    CREATE INDEX idx_archivos_solicitud_tipo ON archivos_adjuntos(solicitud_id, tipo_mime);

    -- =====================================================
    -- COMENTARIOS FINALES
    -- =====================================================

    /*
    ESTRUCTURA DE LA BASE DE DATOS PARTYEXPRESS:

    1. usuarios: Almacena información de usuarios registrados
    2. categorias_eventos: Categorías predefinidas para los eventos
    3. solicitudes_eventos: Solicitudes de organización de eventos
    4. archivos_adjuntos: Archivos subidos con las solicitudes
    5. mensajes_contacto: Mensajes del formulario de contacto
    6. sesiones_usuarios: Control de sesiones activas
    7. logs_actividad: Registro de actividades del sistema

    VISTAS:
    - vista_solicitudes_completas: Información completa de solicitudes
    - vista_mensajes_contacto: Mensajes con información de usuario

    PROCEDIMIENTOS:
    - sp_estadisticas_sistema(): Obtiene estadísticas generales
    - sp_limpiar_sesiones_expiradas(): Limpia sesiones antiguas

    TRIGGERS:
    - tr_actualizar_ultimo_acceso: Actualiza último acceso del usuario
    - tr_log_actividad_usuarios: Registra creación de usuarios
    */ 