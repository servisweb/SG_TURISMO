-- =============================================
-- USUARIO ADMINISTRADOR PRINCIPAL
-- =============================================
-- Email: admin@tumbestours.com
-- Password: Admin2024!
-- =============================================

INSERT INTO usuarios (tipo_documento, numero_documento, nombres, apellidos, email, telefono, password_hash, rol, estado) 
VALUES ('DNI', '12345678', 'Administrador', 'Sistema', 'admin@tumbestours.com', '942123456', 
        '$2y$10$YourHashedPasswordHere', 'Admin', 'Activo')
ON DUPLICATE KEY UPDATE 
    password_hash = '$2y$10$YourHashedPasswordHere',
    rol = 'Admin',
    estado = 'Activo';

-- Nota: Ejecutar este script PHP para generar el hash correcto:
-- <?php echo password_hash('Admin2024!', PASSWORD_DEFAULT); ?>
