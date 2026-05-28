# ✅ GUÍA DE VERIFICACIÓN DEL SISTEMA

## 🔍 VERIFICAR REGISTRO EN BASE DE DATOS

### 1. Verificar que la base de datos existe
```sql
SHOW DATABASES LIKE 'gestion_turismo_v3';
```

### 2. Verificar estructura de tabla usuarios
```sql
USE gestion_turismo_v3;
DESCRIBE usuarios;
```

### 3. Ver usuarios registrados
```sql
SELECT id_usuario, nombres, apellidos, email, rol, estado, created_at 
FROM usuarios 
ORDER BY created_at DESC;
```

### 4. Verificar último usuario registrado
```sql
SELECT * FROM usuarios ORDER BY id_usuario DESC LIMIT 1;
```

## 🧪 PRUEBAS DEL SISTEMA

### Prueba 1: Registro de Usuario
1. Ir a: `http://localhost/SG_TURISMO/views/login.php`
2. Hacer clic en "Regístrate aquí"
3. Llenar el formulario:
   - Nombres: Test
   - Apellidos: Usuario
   - Email: test@ejemplo.com
   - Teléfono: 999999999
   - Contraseña: test123
   - Confirmar Contraseña: test123
4. Hacer clic en "Registrarse"
5. **Resultado esperado**: Redirige a index.php con sesión iniciada

### Prueba 2: Verificar en Base de Datos
```sql
SELECT * FROM usuarios WHERE email = 'test@ejemplo.com';
```
**Resultado esperado**: Debe aparecer el usuario con password_hash encriptado

### Prueba 3: Login con Usuario Registrado
1. Cerrar sesión (si está logueado)
2. Ir a: `http://localhost/SG_TURISMO/views/login.php`
3. Ingresar:
   - Email: test@ejemplo.com
   - Contraseña: test123
4. Hacer clic en "Iniciar Sesión"
5. **Resultado esperado**: Redirige a index.php mostrando el nombre del usuario

### Prueba 4: Verificar Menú de Usuario
1. Con sesión iniciada, ir a: `http://localhost/SG_TURISMO/index.php`
2. **Resultado esperado**: 
   - En el header debe aparecer: "👤 Test Usuario ▼"
   - Al hacer clic debe mostrar dropdown con:
     - Mi Perfil
     - Cerrar Sesión

### Prueba 5: Login como Admin
1. Primero insertar usuario admin (ver usuarios_prueba.sql)
2. Ir a login
3. Ingresar:
   - Email: admin@tumbestours.com
   - Contraseña: admin123
4. **Resultado esperado**: Redirige a `/views/admin/dashboard.php`

## 🐛 SOLUCIÓN DE PROBLEMAS

### Problema: No se registra en la base de datos
**Verificar:**
```php
// En config/conexion.php
$host = 'localhost';
$user = 'root';
$password = ''; // Tu contraseña de MySQL
$database = 'gestion_turismo_v3';
```

### Problema: Error de conexión
**Solución:**
1. Verificar que MySQL esté corriendo
2. Verificar credenciales en `config/conexion.php`
3. Verificar que la base de datos exista

### Problema: No muestra el nombre del usuario
**Verificar:**
1. Que la sesión esté iniciada: `var_dump($_SESSION);`
2. Que exista `$_SESSION['user_name']`
3. Limpiar caché del navegador

### Problema: Password no coincide
**Causa:** El hash de prueba es para "password"
**Solución:** Registrar un nuevo usuario o cambiar el password:
```php
// Generar nuevo hash
echo password_hash('tu_password', PASSWORD_DEFAULT);
```

## 📱 VERIFICAR ELEMENTOS VISUALES

### ✅ Header con Usuario Logueado
- [ ] Muestra nombre del usuario
- [ ] Icono de usuario visible
- [ ] Dropdown funciona al hacer clic
- [ ] Opciones del menú visibles

### ✅ Footer
- [ ] Tres columnas de información
- [ ] Redes sociales con iconos
- [ ] Enlaces funcionan
- [ ] Copyright visible

### ✅ Botón Flotante WhatsApp
- [ ] Visible en esquina inferior derecha
- [ ] Color verde (#25D366)
- [ ] Animación de pulso
- [ ] Abre WhatsApp al hacer clic
- [ ] Responsive en móvil

## 🔐 USUARIOS DE PRUEBA

### Admin
- **Email:** admin@tumbestours.com
- **Password:** admin123
- **Rol:** Admin

### Cliente
- **Email:** cliente@test.com
- **Password:** cliente123
- **Rol:** Cliente

## 📊 CONSULTAS ÚTILES

### Ver todos los usuarios
```sql
SELECT id_usuario, CONCAT(nombres, ' ', apellidos) as nombre_completo, 
       email, rol, estado, created_at 
FROM usuarios;
```

### Contar usuarios por rol
```sql
SELECT rol, COUNT(*) as total 
FROM usuarios 
GROUP BY rol;
```

### Ver usuarios activos
```sql
SELECT * FROM usuarios WHERE estado = 'Activo';
```

### Eliminar usuario de prueba
```sql
DELETE FROM usuarios WHERE email = 'test@ejemplo.com';
```

## ✨ CARACTERÍSTICAS IMPLEMENTADAS

✅ Sistema de login con base de datos
✅ Registro de usuarios con validación
✅ Encriptación de contraseñas (bcrypt)
✅ Sesiones de usuario
✅ Menú de usuario en header
✅ Redirección según rol (Admin/Cliente)
✅ Footer completo con información
✅ Botón flotante de WhatsApp
✅ Diseño responsive
✅ Validación de formularios
✅ Mensajes de error personalizados

## 🎯 PRÓXIMOS PASOS

1. Conectar CRUD de paquetes con BD
2. Implementar sistema de reservas con BD
3. Agregar gestión de destinos
4. Implementar panel de usuario
5. Agregar sistema de pagos
6. Implementar reportes

---

**Nota:** Asegúrate de tener la base de datos creada e importada antes de realizar las pruebas.
