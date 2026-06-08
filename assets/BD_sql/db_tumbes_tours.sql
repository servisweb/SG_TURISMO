
CREATE DATABASE IF NOT EXISTS gestion_turismo_v4
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gestion_turismo_v4;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS notificaciones;
DROP TABLE IF EXISTS favoritos;
DROP TABLE IF EXISTS descuentos;
DROP TABLE IF EXISTS evaluaciones_destinos;
DROP TABLE IF EXISTS evaluaciones_operacion;
DROP TABLE IF EXISTS cancelaciones;
DROP TABLE IF EXISTS pagos;
DROP TABLE IF EXISTS pasajeros;
DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS grupos_compartidos;
DROP TABLE IF EXISTS salidas_operativas;
DROP TABLE IF EXISTS itinerario_detalle;
DROP TABLE IF EXISTS paquetes;
DROP TABLE IF EXISTS guias;
DROP TABLE IF EXISTS movilidades;
DROP TABLE IF EXISTS alojamientos;
DROP TABLE IF EXISTS destinos;
DROP TABLE IF EXISTS proveedores;
DROP TABLE IF EXISTS usuarios;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. USUARIOS
-- ============================================================
CREATE TABLE usuarios (
  id_usuario      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo_documento  ENUM('DNI','CE','PASSPORT') NOT NULL,
  numero_documento VARCHAR(20) NOT NULL,
  nombres         VARCHAR(120) NOT NULL,
  apellidos       VARCHAR(120) NOT NULL,
  email           VARCHAR(120) NOT NULL,
  telefono        VARCHAR(30)  DEFAULT NULL,
  password_hash   VARCHAR(255) NOT NULL,
  avatar_url      VARCHAR(255) DEFAULT NULL,        -- foto de perfil
  rol             ENUM('Admin','Cliente') NOT NULL DEFAULT 'Cliente',
  estado          ENUM('Activo','Inactivo')  NOT NULL DEFAULT 'Activo',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_usuarios_documento (numero_documento),
  UNIQUE KEY uk_usuarios_email     (email),
  KEY idx_usuarios_rol    (rol),
  KEY idx_usuarios_estado (estado)
) ENGINE=InnoDB;

CREATE TABLE app_reservas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario BIGINT UNSIGNED NOT NULL,
  codigo_reserva VARCHAR(80) NOT NULL,
  tour_id INT DEFAULT NULL,
  cantidad SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  estado ENUM('Pendiente','Pagada','Cancelada','Migrada') NOT NULL DEFAULT 'Pendiente',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_salida DATETIME DEFAULT NULL,
  guide_name VARCHAR(200) DEFAULT NULL,
  comentarios TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_app_reservas_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  UNIQUE KEY uk_app_reservas_codigo (codigo_reserva),
  KEY idx_app_reservas_usuario (id_usuario),
  KEY idx_app_reservas_estado (estado),
  KEY idx_app_reservas_fecha (fecha_creacion)
) ENGINE=InnoDB;

CREATE TABLE app_pasajeros (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_reserva BIGINT UNSIGNED NOT NULL,
  tipo_documento ENUM('DNI','CE','PASSPORT') NOT NULL,
  numero_documento VARCHAR(20) NOT NULL,
  nombres_completos VARCHAR(200) NOT NULL,
  fecha_nacimiento DATE DEFAULT NULL,
  contacto_emergencia_nombre VARCHAR(150) DEFAULT NULL,
  contacto_emergencia_telefono VARCHAR(30) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_app_pasajeros_reserva FOREIGN KEY (id_reserva) REFERENCES app_reservas(id) ON DELETE CASCADE,
  KEY idx_app_pasajeros_reserva (id_reserva)
) ENGINE=InnoDB;
CREATE TABLE proveedores (
  id_proveedor          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo_documento        ENUM('RUC','DNI') NOT NULL,
  numero_documento      VARCHAR(20)  NOT NULL,
  razon_social_o_nombre VARCHAR(200) NOT NULL,
  tipo_relacion         ENUM('Interno','Tercero') NOT NULL,
  telefono_contacto     VARCHAR(30)  DEFAULT NULL,
  email_contacto        VARCHAR(120) DEFAULT NULL,
  direccion             VARCHAR(255) DEFAULT NULL,
  estado                ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  created_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_proveedores_documento (numero_documento),
  KEY idx_proveedores_relacion (tipo_relacion)
) ENGINE=InnoDB;

-- ============================================================
-- 3. DESTINOS
-- ============================================================
CREATE TABLE destinos (
  id_destino          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre_destino      VARCHAR(150) NOT NULL,
  tipo_destino        ENUM('Playa','Naturaleza','Cultura','Mixto') NOT NULL,
  region              VARCHAR(120) NOT NULL DEFAULT 'Tumbes',
  provincia           VARCHAR(120) DEFAULT NULL,
  distrito            VARCHAR(120) DEFAULT NULL,
  descripcion         TEXT         DEFAULT NULL,
  foto_url            VARCHAR(255) DEFAULT NULL,
  horario_apertura    TIME         DEFAULT NULL,
  horario_cierre      TIME         DEFAULT NULL,
  precio_referencial  DECIMAL(10,2) DEFAULT NULL,
  estado              ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_destinos_nombre (nombre_destino),
  KEY idx_destinos_tipo   (tipo_destino),
  KEY idx_destinos_region (region),
  KEY idx_destinos_estado (estado)
) ENGINE=InnoDB;

-- ============================================================
-- 4. ALOJAMIENTOS
-- ============================================================
CREATE TABLE alojamientos (
  id_alojamiento     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_proveedor       BIGINT UNSIGNED NOT NULL,
  id_destino         BIGINT UNSIGNED NOT NULL,
  nombre_hotel       VARCHAR(200) NOT NULL,
  categoria_estrellas TINYINT UNSIGNED NOT NULL,
  direccion          VARCHAR(255) NOT NULL,
  telefono           VARCHAR(30)  DEFAULT NULL,
  email              VARCHAR(120) DEFAULT NULL,
  foto_url           VARCHAR(255) DEFAULT NULL,
  estado             ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_alojamientos_proveedor FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
  CONSTRAINT fk_alojamientos_destino   FOREIGN KEY (id_destino)   REFERENCES destinos(id_destino),
  KEY idx_alojamientos_destino (id_destino),
  KEY idx_alojamientos_estado  (estado)
) ENGINE=InnoDB;

-- ============================================================
-- 5. MOVILIDADES
-- ============================================================
CREATE TABLE movilidades (
  id_movilidad        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_proveedor        BIGINT UNSIGNED NOT NULL,
  placa               VARCHAR(20) NOT NULL,
  tipo_vehiculo       ENUM('Van','Bus','Lancha','Auto') NOT NULL,
  capacidad_pasajeros SMALLINT UNSIGNED NOT NULL,
  marca               VARCHAR(80) DEFAULT NULL,
  modelo              VARCHAR(80) DEFAULT NULL,
  anio_modelo         SMALLINT UNSIGNED DEFAULT NULL,
  foto_url            VARCHAR(255) DEFAULT NULL,
  estado              ENUM('Activo','Inactivo','Mantenimiento') NOT NULL DEFAULT 'Activo',
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_movilidades_proveedor FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
  UNIQUE KEY uk_movilidades_placa (placa),
  KEY idx_movilidades_tipo   (tipo_vehiculo),
  KEY idx_movilidades_estado (estado)
) ENGINE=InnoDB;

-- ============================================================
-- 6. GUIAS
--    + precio_adicional  (cobro extra al precio base del tour)
--    + experiencia_anios (para mostrar en el perfil del guía)
-- ============================================================
CREATE TABLE guias (
  id_guia            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_proveedor       BIGINT UNSIGNED DEFAULT NULL,
  id_usuario         BIGINT UNSIGNED DEFAULT NULL,
  tipo_contrato      ENUM('Interno','Tercero') NOT NULL,
  nombres_completos  VARCHAR(200) NOT NULL,
  especialidad       VARCHAR(120) NOT NULL,
  idiomas            VARCHAR(200) DEFAULT NULL,
  experiencia_anios  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  licencia_operativa VARCHAR(60)  NOT NULL,
  telefono           VARCHAR(30)  DEFAULT NULL,
  foto_url           VARCHAR(255) DEFAULT NULL,
  precio_adicional   DECIMAL(10,2) NOT NULL DEFAULT 0.00,  -- cargo extra por guía
  estado             ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_guias_proveedor FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
  CONSTRAINT fk_guias_usuario   FOREIGN KEY (id_usuario)   REFERENCES usuarios(id_usuario),
  UNIQUE KEY uk_guias_licencia (licencia_operativa),
  KEY idx_guias_estado (estado)
) ENGINE=InnoDB;

-- ============================================================
-- 7. PAQUETES
--    + duracion_horas  (duración real del tour en horas)
--    + categoria       (para los filtros del frontend)
--    + incluye_guia    (si el precio base ya incluye guía)
--    + precio_grupo    (precio cuando se reserva para 4+ personas)
-- ============================================================
CREATE TABLE paquetes (
  id_paquete          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_destino          BIGINT UNSIGNED NOT NULL,
  codigo_paquete      VARCHAR(40)  NOT NULL,
  titulo              VARCHAR(200) NOT NULL,
  descripcion_general TEXT         DEFAULT NULL,
  foto_portada_url    VARCHAR(255) DEFAULT NULL,
  categoria           ENUM('Playa','Naturaleza','Cultura','Mixto') NOT NULL DEFAULT 'Mixto',
  duracion_horas      SMALLINT UNSIGNED NOT NULL DEFAULT 8,   -- duración en horas
  precio_base         DECIMAL(10,2) NOT NULL DEFAULT 0.00,    -- precio por persona
  precio_grupo        DECIMAL(10,2) NOT NULL DEFAULT 0.00,    -- precio para grupos de 4+
  incluye_guia        TINYINT(1)   NOT NULL DEFAULT 0,        -- 1 = guía incluido en precio
  cupo_minimo         SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  cupo_maximo         SMALLINT UNSIGNED NOT NULL DEFAULT 20,
  estado              ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_paquetes_destino FOREIGN KEY (id_destino) REFERENCES destinos(id_destino),
  UNIQUE KEY uk_paquetes_codigo (codigo_paquete),
  KEY idx_paquetes_destino   (id_destino),
  KEY idx_paquetes_categoria (categoria),
  KEY idx_paquetes_estado    (estado)
) ENGINE=InnoDB;

-- ============================================================
-- 8. ITINERARIO DETALLE
-- ============================================================
CREATE TABLE itinerario_detalle (
  id_detalle       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_paquete       BIGINT UNSIGNED NOT NULL,
  id_detalle_padre BIGINT UNSIGNED DEFAULT NULL,
  dia_numero       SMALLINT UNSIGNED NOT NULL,
  hora_inicio      TIME    DEFAULT NULL,
  hora_fin         TIME    DEFAULT NULL,
  categoria_evento ENUM('Alimentacion','Actividad','Traslado','Descanso') NOT NULL,
  descripcion_evento VARCHAR(500) NOT NULL,
  id_alojamiento   BIGINT UNSIGNED DEFAULT NULL,
  orden            SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_itinerario_paquete     FOREIGN KEY (id_paquete)       REFERENCES paquetes(id_paquete) ON DELETE CASCADE,
  CONSTRAINT fk_itinerario_padre       FOREIGN KEY (id_detalle_padre) REFERENCES itinerario_detalle(id_detalle) ON DELETE CASCADE,
  CONSTRAINT fk_itinerario_alojamiento FOREIGN KEY (id_alojamiento)   REFERENCES alojamientos(id_alojamiento),
  KEY idx_itinerario_paquete_dia (id_paquete, dia_numero),
  KEY idx_itinerario_categoria   (categoria_evento)
) ENGINE=InnoDB;

-- ============================================================
-- 9. SALIDAS OPERATIVAS
--    Cada fila = una fecha concreta en la que sale un paquete.
--    cupos_reservados se actualiza mediante TRIGGER (ver abajo).
-- ============================================================
CREATE TABLE salidas_operativas (
  id_salida         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_paquete        BIGINT UNSIGNED NOT NULL,
  fecha_hora_salida DATETIME        NOT NULL,
  fecha_hora_retorno DATETIME       DEFAULT NULL,
  id_guia           BIGINT UNSIGNED NOT NULL,
  id_movilidad      BIGINT UNSIGNED NOT NULL,
  cupos_totales     SMALLINT UNSIGNED NOT NULL,
  cupos_reservados  SMALLINT UNSIGNED NOT NULL DEFAULT 0,  -- se actualiza con trigger
  estado            ENUM('Programada','Confirmada','En Curso','Finalizada','Cancelada') NOT NULL DEFAULT 'Programada',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_salidas_paquete   FOREIGN KEY (id_paquete)   REFERENCES paquetes(id_paquete),
  CONSTRAINT fk_salidas_guia      FOREIGN KEY (id_guia)      REFERENCES guias(id_guia),
  CONSTRAINT fk_salidas_movilidad FOREIGN KEY (id_movilidad) REFERENCES movilidades(id_movilidad),
  KEY idx_salidas_fecha   (fecha_hora_salida),
  KEY idx_salidas_estado  (estado),
  KEY idx_salidas_paquete (id_paquete)
) ENGINE=InnoDB;

-- ============================================================
-- 10. DESCUENTOS  [NUEVA]
--     Permite cupones por anticipación, grupos o temporada baja.
--     Se vincula a un paquete concreto o a todos (id_paquete NULL).
-- ============================================================
CREATE TABLE descuentos (
  id_descuento        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_paquete          BIGINT UNSIGNED DEFAULT NULL,     -- NULL = aplica a todos
  tipo                ENUM('Anticipacion','Grupo','Temporada_Baja','Cupon') NOT NULL,
  descripcion         VARCHAR(200) NOT NULL,
  porcentaje          DECIMAL(5,2) NOT NULL DEFAULT 0.00,  -- ej: 15.00 = 15%
  condicion_minima    SMALLINT UNSIGNED NOT NULL DEFAULT 1, -- mín. días anticipación o personas
  fecha_inicio        DATE NOT NULL,
  fecha_fin           DATE NOT NULL,
  activo              TINYINT(1) NOT NULL DEFAULT 1,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_descuentos_paquete FOREIGN KEY (id_paquete) REFERENCES paquetes(id_paquete) ON DELETE CASCADE,
  KEY idx_descuentos_tipo   (tipo),
  KEY idx_descuentos_fechas (fecha_inicio, fecha_fin),
  KEY idx_descuentos_activo (activo)
) ENGINE=InnoDB;

-- ============================================================
-- 11. RESERVAS
-- ============================================================
CREATE TABLE reservas (
  id_reserva          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo_reserva      VARCHAR(40)  NOT NULL,
  id_usuario_titular  BIGINT UNSIGNED NOT NULL,
  id_salida           BIGINT UNSIGNED NOT NULL,
  id_guia_elegido     BIGINT UNSIGNED DEFAULT NULL,     -- guía extra elegido por el cliente
  id_descuento        BIGINT UNSIGNED DEFAULT NULL,     -- descuento aplicado
  cantidad_pasajeros  SMALLINT UNSIGNED NOT NULL,
  precio_total        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  estado_reserva      ENUM('Pendiente','Parcial','Pagada','Cancelada') NOT NULL DEFAULT 'Pendiente',
  comentarios         TEXT DEFAULT NULL,                -- observaciones del cliente
  fecha_creacion      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_reservas_usuario   FOREIGN KEY (id_usuario_titular) REFERENCES usuarios(id_usuario),
  CONSTRAINT fk_reservas_salida    FOREIGN KEY (id_salida)          REFERENCES salidas_operativas(id_salida),
  CONSTRAINT fk_reservas_guia      FOREIGN KEY (id_guia_elegido)    REFERENCES guias(id_guia),
  CONSTRAINT fk_reservas_descuento FOREIGN KEY (id_descuento)       REFERENCES descuentos(id_descuento),
  UNIQUE KEY uk_reservas_codigo (codigo_reserva),
  KEY idx_reservas_salida  (id_salida),
  KEY idx_reservas_estado  (estado_reserva),
  KEY idx_reservas_usuario (id_usuario_titular)
) ENGINE=InnoDB;

-- ============================================================
-- 12. PASAJEROS
-- ============================================================
CREATE TABLE pasajeros (
  id_pasajero                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_reserva                   BIGINT UNSIGNED NOT NULL,
  tipo_documento               ENUM('DNI','CE','PASSPORT') NOT NULL,
  numero_documento             VARCHAR(20)  NOT NULL,
  nombres_completos            VARCHAR(200) NOT NULL,
  fecha_nacimiento             DATE         DEFAULT NULL,
  restricciones_alimenticias   VARCHAR(300) DEFAULT NULL,
  contacto_emergencia_nombre   VARCHAR(150) DEFAULT NULL,
  contacto_emergencia_telefono VARCHAR(30)  DEFAULT NULL,
  seguro_viajero               TINYINT(1)   NOT NULL DEFAULT 0,
  numero_poliza                VARCHAR(80)  DEFAULT NULL,
  consentimiento_privacidad    TINYINT(1)   NOT NULL DEFAULT 0,
  created_at                   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at                   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pasajeros_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON DELETE CASCADE,
  KEY idx_pasajeros_reserva    (id_reserva),
  KEY idx_pasajeros_documento  (numero_documento)
) ENGINE=InnoDB;

-- ============================================================
-- 13. PAGOS
-- ============================================================
CREATE TABLE pagos (
  id_pago                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_reserva             BIGINT UNSIGNED NOT NULL,
  monto                  DECIMAL(10,2) NOT NULL,
  metodo_pago            ENUM('Tarjeta','Transferencia','Efectivo','Pasarela_Web') NOT NULL,
  estado_pago            ENUM('Completado','Fallido','Reembolsado') NOT NULL,
  referencia_transaccion VARCHAR(100) DEFAULT NULL,
  fecha_pago             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pagos_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON DELETE CASCADE,
  KEY idx_pagos_reserva (id_reserva),
  KEY idx_pagos_estado  (estado_pago)
) ENGINE=InnoDB;

-- ============================================================
-- 14. CANCELACIONES
-- ============================================================
CREATE TABLE cancelaciones (
  id_cancelacion   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_reserva       BIGINT UNSIGNED NOT NULL,
  motivo           VARCHAR(500) NOT NULL,
  fecha_solicitud  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  monto_reembolso  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  estado           ENUM('Pendiente','Aprobado','Rechazado') NOT NULL DEFAULT 'Pendiente',
  politica_aplicada VARCHAR(200) DEFAULT NULL,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cancelaciones_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON DELETE CASCADE,
  KEY idx_cancelaciones_reserva (id_reserva),
  KEY idx_cancelaciones_estado  (estado)
) ENGINE=InnoDB;

-- ============================================================
-- 15. GRUPOS COMPARTIDOS  [NUEVA]
--     Cuando un viajero individual se une a una salida que aún
--     no ha completado el cupo mínimo, queda registrado aquí
--     con estado 'En Espera'. Cuando se completa el mínimo,
--     el admin confirma la salida y los estados pasan a 'Confirmado'.
-- ============================================================
CREATE TABLE grupos_compartidos (
  id_grupo_compartido BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_salida           BIGINT UNSIGNED NOT NULL,
  id_usuario          BIGINT UNSIGNED NOT NULL,
  cantidad_personas   SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  estado              ENUM('En Espera','Confirmado','Cancelado') NOT NULL DEFAULT 'En Espera',
  fecha_solicitud     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_grupos_salida   FOREIGN KEY (id_salida)  REFERENCES salidas_operativas(id_salida),
  CONSTRAINT fk_grupos_usuario  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
  UNIQUE KEY uk_grupo_salida_usuario (id_salida, id_usuario),  -- un usuario no se apunta dos veces
  KEY idx_grupos_estado (estado)
) ENGINE=InnoDB;

-- ============================================================
-- 16. EVALUACIONES OPERACION
-- ============================================================
CREATE TABLE evaluaciones_operacion (
  id_evaluacion_operacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_reserva              BIGINT UNSIGNED NOT NULL,
  calificacion_guia       TINYINT UNSIGNED NOT NULL,
  calificacion_movilidad  TINYINT UNSIGNED NOT NULL,
  calificacion_comida     TINYINT UNSIGNED NOT NULL,
  calificacion_alojamiento TINYINT UNSIGNED NOT NULL,
  comentarios_adicionales TEXT DEFAULT NULL,
  fecha_evaluacion        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_eval_op_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON DELETE CASCADE,
  CHECK (calificacion_guia        BETWEEN 1 AND 5),
  CHECK (calificacion_movilidad   BETWEEN 1 AND 5),
  CHECK (calificacion_comida      BETWEEN 1 AND 5),
  CHECK (calificacion_alojamiento BETWEEN 1 AND 5),
  UNIQUE KEY uk_eval_op_reserva (id_reserva)
) ENGINE=InnoDB;

-- ============================================================
-- 17. EVALUACIONES DESTINOS
-- ============================================================
CREATE TABLE evaluaciones_destinos (
  id_evaluacion_destino BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_reserva            BIGINT UNSIGNED NOT NULL,
  id_destino            BIGINT UNSIGNED NOT NULL,
  calificacion          TINYINT UNSIGNED NOT NULL,
  comentario            TEXT DEFAULT NULL,
  fecha_evaluacion      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_eval_destino_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON DELETE CASCADE,
  CONSTRAINT fk_eval_destino_destino FOREIGN KEY (id_destino) REFERENCES destinos(id_destino),
  CHECK (calificacion BETWEEN 1 AND 5),
  UNIQUE KEY uk_eval_destino (id_reserva, id_destino),
  KEY idx_eval_destino_destino (id_destino)
) ENGINE=InnoDB;

-- ============================================================
-- 18. FAVORITOS  [NUEVA]
--     Guarda qué destinos marcó como favoritos cada usuario.
-- ============================================================
CREATE TABLE favoritos (
  id_favorito  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario   BIGINT UNSIGNED NOT NULL,
  id_destino   BIGINT UNSIGNED NOT NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_favoritos_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_favoritos_destino FOREIGN KEY (id_destino) REFERENCES destinos(id_destino) ON DELETE CASCADE,
  UNIQUE KEY uk_favorito (id_usuario, id_destino)   -- evita duplicados
) ENGINE=InnoDB;

-- ============================================================
-- 19. NOTIFICACIONES  [NUEVA]
--     Registro de alertas enviadas a los usuarios
--     (ofertas, confirmaciones, recordatorios).
-- ============================================================
CREATE TABLE notificaciones (
  id_notificacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario      BIGINT UNSIGNED NOT NULL,
  tipo            ENUM('Oferta','Confirmacion','Recordatorio','Cancelacion') NOT NULL,
  titulo          VARCHAR(150) NOT NULL,
  mensaje         TEXT         NOT NULL,
  leida           TINYINT(1)   NOT NULL DEFAULT 0,   -- 0 = no leída, 1 = leída
  url_destino     VARCHAR(255) DEFAULT NULL,          -- enlace opcional en la notificación
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_notificaciones_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  KEY idx_notificaciones_usuario (id_usuario),
  KEY idx_notificaciones_leida   (leida)
) ENGINE=InnoDB;


-- ============================================================
-- TRIGGERS
-- Actualizan cupos_reservados automáticamente al insertar,
-- actualizar o cancelar una reserva, sin necesidad de hacerlo
-- desde PHP.
-- ============================================================

DELIMITER $$

-- Al crear una reserva, suma los pasajeros a cupos_reservados
CREATE TRIGGER trg_reserva_insert
AFTER INSERT ON reservas
FOR EACH ROW
BEGIN
  IF NEW.estado_reserva != 'Cancelada' THEN
    UPDATE salidas_operativas
      SET cupos_reservados = cupos_reservados + NEW.cantidad_pasajeros,
          updated_at       = NOW()
    WHERE id_salida = NEW.id_salida;
  END IF;
END$$

-- Al cancelar una reserva, devuelve los cupos
CREATE TRIGGER trg_reserva_update
AFTER UPDATE ON reservas
FOR EACH ROW
BEGIN
  -- Pasó a Cancelada: devolver cupos
  IF NEW.estado_reserva = 'Cancelada' AND OLD.estado_reserva != 'Cancelada' THEN
    UPDATE salidas_operativas
      SET cupos_reservados = GREATEST(0, cupos_reservados - OLD.cantidad_pasajeros),
          updated_at       = NOW()
    WHERE id_salida = OLD.id_salida;
  END IF;

  -- Cambió cantidad sin cancelar: ajustar diferencia
  IF NEW.estado_reserva != 'Cancelada' AND OLD.estado_reserva != 'Cancelada'
     AND NEW.cantidad_pasajeros != OLD.cantidad_pasajeros THEN
    UPDATE salidas_operativas
      SET cupos_reservados = GREATEST(0, cupos_reservados
                             + NEW.cantidad_pasajeros
                             - OLD.cantidad_pasajeros),
          updated_at       = NOW()
    WHERE id_salida = NEW.id_salida;
  END IF;
END$$

DELIMITER ;


-- ============================================================
-- DATOS DE EJEMPLO
-- Suficientes para arrancar y probar el sistema desde PHP.
-- ============================================================

-- Admin del sistema (password: Admin2025!)
INSERT INTO usuarios (tipo_documento, numero_documento, nombres, apellidos, email, telefono, password_hash, rol) VALUES
('DNI', '12345678', 'Carlos', 'Mendoza', 'admin@tumbestours.com', '+51942123456',
 '$2y$12$eImiTXuWVxfM37uY4JANjOe5XVt.TYCwgPPZTKqZCnEMQ.hmXhU5O', 'Admin');

-- Cliente de prueba (password: Cliente2025!)
INSERT INTO usuarios (tipo_documento, numero_documento, nombres, apellidos, email, telefono, password_hash, rol) VALUES
('DNI', '87654321', 'María', 'García', 'maria@email.com', '+51999888777',
 '$2y$12$eImiTXuWVxfM37uY4JANjOe5XVt.TYCwgPPZTKqZCnEMQ.hmXhU5O', 'Cliente');

-- Proveedor interno
INSERT INTO proveedores (tipo_documento, numero_documento, razon_social_o_nombre, tipo_relacion, telefono_contacto, email_contacto) VALUES
('RUC', '20123456789', 'Tumbes Tours SAC', 'Interno', '+51942123456', 'ops@tumbestours.com');

-- Destinos principales
INSERT INTO destinos (nombre_destino, tipo_destino, provincia, distrito, descripcion, precio_referencial) VALUES
('Puerto Pizarro - Manglares',   'Naturaleza', 'Tumbes',   'Tumbes',    'Explora los manglares, la Isla de los Pájaros y el zoocriadero de cocodrilos.', 65.00),
('Balneario de Zorritos',        'Playa',      'Contralmirante Villar', 'Zorritos', 'Las playas más cálidas del norte peruano con excelente gastronomía.', 120.00),
('Huaca del Sol - Cabeza de Vaca','Cultura',   'Tumbes',   'Corrales',  'Descubre la historia preínca de Tumbes en un fascinante recorrido arqueológico.', 120.00),
('Avistamiento de Ballenas',     'Naturaleza', 'Contralmirante Villar', 'Zorritos', 'Encuentro inolvidable con ballenas jorobadas en temporada de migración.', 150.00),
('Parque Nacional Cerros de Amotape','Naturaleza','Tumbes','Pampas de Hospital','Senderismo en el bosque seco más importante del norte.', 130.00);

-- Guías
INSERT INTO guias (id_proveedor, tipo_contrato, nombres_completos, especialidad, idiomas, experiencia_anios, licencia_operativa, telefono, precio_adicional) VALUES
(1, 'Interno', 'Jorge Castillo Ruiz',  'Manglares y fauna',         'Español, Inglés', 8, 'LIC-001-TUM', '+51942111001', 50.00),
(1, 'Interno', 'Ana Flores Vega',      'Arqueología y cultura',     'Español',         5, 'LIC-002-TUM', '+51942111002', 40.00),
(1, 'Interno', 'Luis Távara Pacheco',  'Avistamiento de ballenas',  'Español, Inglés', 10,'LIC-003-TUM', '+51942111003', 60.00),
(1, 'Interno', 'Rosa Nima Farfán',     'Naturaleza y senderismo',   'Español',         6, 'LIC-004-TUM', '+51942111004', 45.00);

-- Movilidades
INSERT INTO movilidades (id_proveedor, placa, tipo_vehiculo, capacidad_pasajeros, marca, modelo, anio_modelo) VALUES
(1, 'TUM-001', 'Van',    8,  'Toyota',  'Hiace',     2020),
(1, 'TUM-002', 'Lancha', 12, 'Yamaha',  'Enduro 40', 2021),
(1, 'TUM-003', 'Bus',    20, 'Mercedes','Sprinter',  2019);

-- Paquetes
INSERT INTO paquetes (id_destino, codigo_paquete, titulo, descripcion_general, categoria, duracion_horas, precio_base, precio_grupo, incluye_guia, cupo_minimo, cupo_maximo) VALUES
(1, 'PKG-001', 'Tour Manglares Completo',         'Paseo en bote, visita al zoocriadero y la Isla de los Pájaros.',          'Naturaleza', 8,  65.00,  260.00, 0, 2, 8),
(2, 'PKG-002', 'Full Day Zorritos',               'Día completo de relajo en las playas de Zorritos con almuerzo incluido.',  'Playa',      10, 120.00, 480.00, 1, 2, 12),
(3, 'PKG-003', 'Ruta Arqueológica Cabeza de Vaca', 'Recorrido por la huaca preínca con guía especializado.',                 'Cultura',    8,  120.00, 480.00, 1, 2, 10),
(4, 'PKG-004', 'Avistamiento de Ballenas',         'Tour marítimo para ver ballenas jorobadas en su temporada de migración.','Naturaleza', 5,  150.00, 720.00, 1, 4, 12),
(5, 'PKG-005', 'Trekking Cerros de Amotape',       'Senderismo guiado por el bosque seco del Parque Nacional.',              'Naturaleza', 10, 130.00, 520.00, 1, 3, 10);

-- Descuentos
INSERT INTO descuentos (id_paquete, tipo, descripcion, porcentaje, condicion_minima, fecha_inicio, fecha_fin) VALUES
(NULL,  'Anticipacion',   'Reserva con 15+ días de anticipación: 10% de descuento', 10.00, 15, '2025-01-01', '2026-12-31'),
(NULL,  'Grupo',          'Grupos de 6 o más personas: 15% de descuento',           15.00,  6, '2025-01-01', '2026-12-31'),
(NULL,  'Temporada_Baja', 'Temporada baja marzo-mayo: 20% de descuento',            20.00,  1, '2026-03-01', '2026-05-31'),
(4,     'Anticipacion',   'Ballenas: reserva anticipada 20%',                       20.00, 10, '2025-01-01', '2026-12-31');

-- Salida de ejemplo (lista para hacer reservas de prueba)
INSERT INTO salidas_operativas (id_paquete, fecha_hora_salida, fecha_hora_retorno, id_guia, id_movilidad, cupos_totales) VALUES
(1, '2026-07-15 07:00:00', '2026-07-15 17:00:00', 1, 1, 8),
(2, '2026-07-20 06:30:00', '2026-07-20 19:00:00', 2, 3, 12),
(4, '2026-08-10 06:00:00', '2026-08-10 14:00:00', 3, 2, 12);