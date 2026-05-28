-- Script para insertar usuarios de prueba en el sistema

-- Usuario Administrador
-- Email: admin@tumbestours.com
-- Password: admin123
INSERT INTO usuarios (tipo_documento, numero_documento, nombres, apellidos, email, telefono, password_hash, rol, estado) 
VALUES ('DNI', '12345678', 'Admin', 'Sistema', 'admin@tumbestours.com', '942123456', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Activo');

-- Usuario Cliente de prueba
-- Email: cliente@test.com
-- Password: cliente123
INSERT INTO usuarios (tipo_documento, numero_documento, nombres, apellidos, email, telefono, password_hash, rol, estado) 
VALUES ('DNI', '87654321', 'Juan Carlos', 'Pérez García', 'cliente@test.com', '999888777', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Cliente', 'Activo');

-- Verificar usuarios insertados
SELECT id_usuario, nombres, apellidos, email, rol, estado FROM usuarios;
