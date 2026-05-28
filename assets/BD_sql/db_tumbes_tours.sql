CREATE DATABASE IF NOT EXISTS gestion_turismo_v3 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE gestion_turismo_v3;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS evaluaciones_destinos;
DROP TABLE IF EXISTS evaluaciones_operacion;
DROP TABLE IF EXISTS cancelaciones;
DROP TABLE IF EXISTS pagos;
DROP TABLE IF EXISTS pasajeros;
DROP TABLE IF EXISTS reservas;
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

-- 1. USUARIOS
CREATE TABLE usuarios (
  id_usuario BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo_documento ENUM('DNI','CE','PASSPORT') NOT NULL,
  numero_documento VARCHAR(20) NOT NULL,
  nombres VARCHAR(120) NOT NULL,
  apellidos VARCHAR(120) NOT NULL,
  email VARCHAR(120) DEFAULT NULL,
  telefono VARCHAR(30) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('Admin','Cliente') NOT NULL DEFAULT 'Cliente',
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_usuarios_documento (numero_documento),
  UNIQUE KEY uk_usuarios_email (email),
  KEY idx_usuarios_rol (rol),
  KEY idx_usuarios_estado (estado)
) ENGINE=InnoDB;

-- 2. PROVEEDORES
CREATE TABLE proveedores (
  id_proveedor BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo_documento ENUM('RUC','DNI') NOT NULL,
  numero_documento VARCHAR(20) NOT NULL,
  razon_social_o_nombre VARCHAR(200) NOT NULL,
  tipo_relacion ENUM('Interno','Tercero') NOT NULL,
  telefono_contacto VARCHAR(30) DEFAULT NULL,
  email_contacto VARCHAR(120) DEFAULT NULL,
  direccion VARCHAR(255) DEFAULT NULL,
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_proveedores_documento (numero_documento),
  KEY idx_proveedores_relacion (tipo_relacion)
) ENGINE=InnoDB;

-- 3. DESTINOS
CREATE TABLE destinos (
  id_destino BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre_destino VARCHAR(150) NOT NULL,
  tipo_destino ENUM('Playa','Naturaleza','Cultura','Mixto') NOT NULL,
  region VARCHAR(120) NOT NULL DEFAULT 'Tumbes',
  provincia VARCHAR(120) DEFAULT NULL,
  distrito VARCHAR(120) DEFAULT NULL,
  descripcion TEXT DEFAULT NULL,
  foto_url VARCHAR(255) DEFAULT NULL,
  horario_apertura TIME DEFAULT NULL,
  horario_cierre TIME DEFAULT NULL,
  precio_referencial DECIMAL(10,2) DEFAULT NULL,
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_destinos_nombre (nombre_destino),
  KEY idx_destinos_tipo (tipo_destino),
  KEY idx_destinos_region (region),
  KEY idx_destinos_estado (estado)
) ENGINE=InnoDB;

-- 4. ALOJAMIENTOS
CREATE TABLE alojamientos (
  id_alojamiento BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_proveedor BIGINT UNSIGNED NOT NULL,
  id_destino BIGINT UNSIGNED NOT NULL,
  nombre_hotel VARCHAR(200) NOT NULL,
  categoria_estrellas TINYINT UNSIGNED NOT NULL,
  direccion VARCHAR(255) NOT NULL,
  telefono VARCHAR(30) DEFAULT NULL,
  email VARCHAR(120) DEFAULT NULL,
  foto_url VARCHAR(255) DEFAULT NULL,
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_alojamientos_proveedor FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
  CONSTRAINT fk_alojamientos_destino FOREIGN KEY (id_destino) REFERENCES destinos(id_destino),
  KEY idx_alojamientos_destino (id_destino),
  KEY idx_alojamientos_estado (estado)
) ENGINE=InnoDB;

-- 5. MOVILIDADES
CREATE TABLE movilidades (
  id_movilidad BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_proveedor BIGINT UNSIGNED NOT NULL,
  placa VARCHAR(20) NOT NULL,
  tipo_vehiculo ENUM('Van','Bus','Lancha','Auto') NOT NULL,
  capacidad_pasajeros SMALLINT UNSIGNED NOT NULL,
  marca VARCHAR(80) DEFAULT NULL,
  modelo VARCHAR(80) DEFAULT NULL,
  anio_modelo SMALLINT UNSIGNED DEFAULT NULL,
  foto_url VARCHAR(255) DEFAULT NULL,
  estado ENUM('Activo','Inactivo','Mantenimiento') NOT NULL DEFAULT 'Activo',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_movilidades_proveedor FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
  UNIQUE KEY uk_movilidades_placa (placa),
  KEY idx_movilidades_tipo (tipo_vehiculo),
  KEY idx_movilidades_estado (estado)
) ENGINE=InnoDB;

-- 6. GUIAS
CREATE TABLE guias (
  id_guia BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_proveedor BIGINT UNSIGNED DEFAULT NULL,
  id_usuario BIGINT UNSIGNED DEFAULT NULL,
  tipo_contrato ENUM('Interno','Tercero') NOT NULL,
  especialidad VARCHAR(120) NOT NULL,
  idiomas VARCHAR(200) DEFAULT NULL,
  licencia_operativa VARCHAR(60) NOT NULL,
  telefono VARCHAR(30) DEFAULT NULL,
  foto_url VARCHAR(255) DEFAULT NULL,
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_guias_proveedor FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
  CONSTRAINT fk_guias_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
  UNIQUE KEY uk_guias_licencia (licencia_operativa),
  KEY idx_guias_estado (estado)
) ENGINE=InnoDB;

-- 7. PAQUETES
CREATE TABLE paquetes (
  id_paquete BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_destino BIGINT UNSIGNED NOT NULL,
  codigo_paquete VARCHAR(40) NOT NULL,
  titulo VARCHAR(200) NOT NULL,
  descripcion_general TEXT DEFAULT NULL,
  foto_portada_url VARCHAR(255) DEFAULT NULL,
  precio_base DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  cupo_minimo SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  cupo_maximo SMALLINT UNSIGNED NOT NULL DEFAULT 999,
  estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_paquetes_destino FOREIGN KEY (id_destino) REFERENCES destinos(id_destino),
  UNIQUE KEY uk_paquetes_codigo (codigo_paquete),
  KEY idx_paquetes_destino (id_destino),
  KEY idx_paquetes_estado (estado)
) ENGINE=InnoDB;

-- 8. ITINERARIO DETALLE
CREATE TABLE itinerario_detalle (
  id_detalle BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_paquete BIGINT UNSIGNED NOT NULL,
  id_detalle_padre BIGINT UNSIGNED DEFAULT NULL,
  dia_numero SMALLINT UNSIGNED NOT NULL,
  hora_inicio TIME DEFAULT NULL,
  hora_fin TIME DEFAULT NULL,
  categoria_evento ENUM('Alimentacion','Actividad','Traslado','Descanso') NOT NULL,
  descripcion_evento VARCHAR(500) NOT NULL,
  id_alojamiento BIGINT UNSIGNED DEFAULT NULL,
  orden SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_itinerario_paquete FOREIGN KEY (id_paquete) REFERENCES paquetes(id_paquete) ON DELETE CASCADE,
  CONSTRAINT fk_itinerario_padre FOREIGN KEY (id_detalle_padre) REFERENCES itinerario_detalle(id_detalle) ON DELETE CASCADE,
  CONSTRAINT fk_itinerario_alojamiento FOREIGN KEY (id_alojamiento) REFERENCES alojamientos(id_alojamiento),
  KEY idx_itinerario_paquete_dia (id_paquete, dia_numero),
  KEY idx_itinerario_categoria (categoria_evento)
) ENGINE=InnoDB;

-- 9. SALIDAS OPERATIVAS
CREATE TABLE salidas_operativas (
  id_salida BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_paquete BIGINT UNSIGNED NOT NULL,
  fecha_hora_salida DATETIME NOT NULL,
  fecha_hora_retorno DATETIME DEFAULT NULL,
  id_guia BIGINT UNSIGNED NOT NULL,
  id_movilidad BIGINT UNSIGNED NOT NULL,
  cupos_totales SMALLINT UNSIGNED NOT NULL,
  cupos_reservados SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  estado ENUM('Programada','Confirmada','En Curso','Finalizada','Cancelada') NOT NULL DEFAULT 'Programada',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_salidas_paquete FOREIGN KEY (id_paquete) REFERENCES paquetes(id_paquete),
  CONSTRAINT fk_salidas_guia FOREIGN KEY (id_guia) REFERENCES guias(id_guia),
  CONSTRAINT fk_salidas_movilidad FOREIGN KEY (id_movilidad) REFERENCES movilidades(id_movilidad),
  KEY idx_salidas_fecha (fecha_hora_salida),
  KEY idx_salidas_estado (estado),
  KEY idx_salidas_paquete (id_paquete)
) ENGINE=InnoDB;

-- 10. RESERVAS
CREATE TABLE reservas (
  id_reserva BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo_reserva VARCHAR(40) NOT NULL,
  id_usuario_titular BIGINT UNSIGNED NOT NULL,
  id_salida BIGINT UNSIGNED NOT NULL,
  cantidad_pasajeros SMALLINT UNSIGNED NOT NULL,
  precio_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  estado_reserva ENUM('Pendiente','Parcial','Pagada','Cancelada') NOT NULL DEFAULT 'Pendiente',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_reservas_usuario FOREIGN KEY (id_usuario_titular) REFERENCES usuarios(id_usuario),
  CONSTRAINT fk_reservas_salida FOREIGN KEY (id_salida) REFERENCES salidas_operativas(id_salida),
  UNIQUE KEY uk_reservas_codigo (codigo_reserva),
  KEY idx_reservas_salida (id_salida),
  KEY idx_reservas_estado (estado_reserva)
) ENGINE=InnoDB;

-- 11. PASAJEROS
CREATE TABLE pasajeros (
  id_pasajero BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_reserva BIGINT UNSIGNED NOT NULL,
  tipo_documento ENUM('DNI','CE','PASSPORT') NOT NULL,
  numero_documento VARCHAR(20) NOT NULL,
  nombres_completos VARCHAR(200) NOT NULL,
  fecha_nacimiento DATE DEFAULT NULL,
  restricciones_alimenticias VARCHAR(300) DEFAULT NULL,
  contacto_emergencia_nombre VARCHAR(150) DEFAULT NULL,
  contacto_emergencia_telefono VARCHAR(30) DEFAULT NULL,
  seguro_viajero TINYINT(1) NOT NULL DEFAULT 0,
  numero_poliza VARCHAR(80) DEFAULT NULL,
  consentimiento_privacidad TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pasajeros_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON DELETE CASCADE,
  KEY idx_pasajeros_reserva (id_reserva),
  KEY idx_pasajeros_documento (numero_documento)
) ENGINE=InnoDB;

-- 12. PAGOS
CREATE TABLE pagos (
  id_pago BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_reserva BIGINT UNSIGNED NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  metodo_pago ENUM('Tarjeta','Transferencia','Efectivo','Pasarela_Web') NOT NULL,
  estado_pago ENUM('Completado','Fallido','Reembolsado') NOT NULL,
  referencia_transaccion VARCHAR(100) DEFAULT NULL,
  fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pagos_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON DELETE CASCADE,
  KEY idx_pagos_reserva (id_reserva),
  KEY idx_pagos_estado (estado_pago)
) ENGINE=InnoDB;

-- 13. CANCELACIONES
CREATE TABLE cancelaciones (
  id_cancelacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_reserva BIGINT UNSIGNED NOT NULL,
  motivo VARCHAR(500) NOT NULL,
  fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  monto_reembolso DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  estado ENUM('Pendiente','Aprobado','Rechazado') NOT NULL DEFAULT 'Pendiente',
  politica_aplicada VARCHAR(200) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cancelaciones_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON DELETE CASCADE,
  KEY idx_cancelaciones_reserva (id_reserva),
  KEY idx_cancelaciones_estado (estado)
) ENGINE=InnoDB;

-- 14. EVALUACIONES OPERACION
CREATE TABLE evaluaciones_operacion (
  id_evaluacion_operacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_reserva BIGINT UNSIGNED NOT NULL,
  calificacion_guia TINYINT UNSIGNED NOT NULL,
  calificacion_movilidad TINYINT UNSIGNED NOT NULL,
  calificacion_comida TINYINT UNSIGNED NOT NULL,
  calificacion_alojamiento TINYINT UNSIGNED NOT NULL,
  comentarios_adicionales TEXT DEFAULT NULL,
  fecha_evaluacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_eval_op_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON DELETE CASCADE,
  CHECK (calificacion_guia BETWEEN 1 AND 5),
  CHECK (calificacion_movilidad BETWEEN 1 AND 5),
  CHECK (calificacion_comida BETWEEN 1 AND 5),
  CHECK (calificacion_alojamiento BETWEEN 1 AND 5),
  UNIQUE KEY uk_eval_op_reserva (id_reserva)
) ENGINE=InnoDB;

-- 15. EVALUACIONES DESTINOS
CREATE TABLE evaluaciones_destinos (
  id_evaluacion_destino BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_reserva BIGINT UNSIGNED NOT NULL,
  id_destino BIGINT UNSIGNED NOT NULL,
  calificacion TINYINT UNSIGNED NOT NULL,
  comentario TEXT DEFAULT NULL,
  fecha_evaluacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_eval_destino_reserva FOREIGN KEY (id_reserva) REFERENCES reservas(id_reserva) ON DELETE CASCADE,
  CONSTRAINT fk_eval_destino_destino FOREIGN KEY (id_destino) REFERENCES destinos(id_destino),
  CHECK (calificacion BETWEEN 1 AND 5),
  UNIQUE KEY uk_eval_destino (id_reserva, id_destino),
  KEY idx_eval_destino_destino (id_destino)
) ENGINE=InnoDB;