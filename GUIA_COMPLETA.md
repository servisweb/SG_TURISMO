# 🚀 SISTEMA DE RESERVAS TUMBES TOURS - GUÍA COMPLETA

## 📋 CREDENCIALES DEL SISTEMA

### 👨‍💼 Usuario Administrador
- **Email:** admin@tumbestours.com
- **Contraseña:** Admin2024!
- **Rol:** Admin
- **Acceso:** Panel administrativo completo

### 👤 Usuario Cliente (Prueba)
- **Email:** cliente@test.com
- **Contraseña:** cliente123
- **Rol:** Cliente
- **Acceso:** Perfil de usuario, reservas

---

## 🔧 CONFIGURACIÓN INICIAL

### 1. Base de Datos
```sql
-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS gestion_turismo_v3 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

-- Importar estructura
USE gestion_turismo_v3;
SOURCE assets/BD_sql/db_tumbes_tours.sql;
```

### 2. Generar Hash de Contraseña Admin
```bash
# Ejecutar desde la raíz del proyecto
php assets/BD_sql/generar_hash_admin.php
```

Esto generará el hash correcto para la contraseña `Admin2024!`

### 3. Insertar Usuario Administrador
```sql
-- Copiar el hash generado y ejecutar:
INSERT INTO usuarios (tipo_documento, numero_documento, nombres, apellidos, email, telefono, password_hash, rol, estado)
VALUES ('DNI', '12345678', 'Administrador', 'Sistema', 'admin@tumbestours.com', '942123456', 
        'HASH_GENERADO_AQUI', 'Admin', 'Activo');
```

---

## 📁 ESTRUCTURA DEL PROYECTO

```
SG_TURISMO/
├── assets/                    # Imágenes y recursos
│   ├── BD_sql/               # Scripts SQL
│   │   ├── db_tumbes_tours.sql
│   │   ├── usuarios_prueba.sql
│   │   ├── admin_usuario.sql
│   │   └── generar_hash_admin.php
│   └── *.jpg                 # Imágenes de tours
├── config/
│   └── conexion.php          # Configuración BD
├── controladores/
│   ├── cards-destinos.php    # Carga paquetes desde BD
│   ├── procesar_login.php    # Autenticación
│   ├── procesar_registro.php # Registro de usuarios
│   └── cerrar_sesion.php     # Logout
├── css/
│   ├── estyle.css            # Estilos principales
│   └── mejoras.css           # Mejoras (footer, header, whatsapp)
├── views/
│   ├── admin/
│   │   ├── dashboard.php     # Panel admin con datos reales
│   │   ├── paquetes.php
│   │   └── reservas.php
│   ├── user/
│   │   └── perfil_user.php   # Perfil con actualización de datos
│   └── login.php
├── index.php                 # Página principal
└── detalles_tour.php
```

---

## ✨ FUNCIONALIDADES IMPLEMENTADAS

### 🔐 Sistema de Autenticación
- ✅ Login con email y contraseña
- ✅ Registro de nuevos usuarios (rol Cliente automático)
- ✅ Hash de contraseñas con bcrypt
- ✅ Sesiones seguras
- ✅ Redirección según rol (Admin → Dashboard, Cliente → Index)

### 👤 Perfil de Usuario
- ✅ Vista de datos personales desde BD
- ✅ Actualización de nombres, apellidos, email, teléfono
- ✅ Cambio de contraseña con validación
- ✅ Validación de email único
- ✅ Mensajes de éxito/error

### 🎨 Interfaz Mejorada
- ✅ Footer con colores del header (#31735a)
- ✅ Emojis en títulos del footer
- ✅ Texto centrado en footer
- ✅ Botón flotante de WhatsApp con animación pulse
- ✅ Menú de usuario en header con dropdown
- ✅ CSS limpio y organizado

### 📊 Panel Administrativo
- ✅ Dashboard con estadísticas reales de BD
- ✅ Total de reservas
- ✅ Reservas pendientes
- ✅ Ingresos del mes actual
- ✅ Tours activos
- ✅ Tabla de reservas recientes
- ✅ Emojis en títulos
- ✅ Enlace "Ver Sitio Web" en sidebar

### 🖼️ Gestión de Imágenes
- ✅ Carga de imágenes desde campo `foto_portada_url` en tabla `paquetes`
- ✅ Fallback a imagen por defecto si no hay imagen en BD
- ✅ Consulta dinámica con JOIN a tabla `destinos`

---

## 🗄️ ESTRUCTURA DE BASE DE DATOS

### Tablas Principales
1. **usuarios** - Clientes y administradores
2. **paquetes** - Tours disponibles
3. **destinos** - Lugares turísticos
4. **reservas** - Reservas de clientes
5. **salidas_operativas** - Fechas de tours
6. **guias** - Guías turísticos
7. **movilidades** - Vehículos
8. **proveedores** - Proveedores de servicios

---

## 🔄 FLUJO DE REGISTRO Y LOGIN

### Registro de Usuario
1. Usuario completa formulario en `/views/login.php`
2. `procesar_registro.php` valida datos
3. Verifica email único
4. Genera hash de contraseña con `password_hash()`
5. Inserta en BD con rol 'Cliente'
6. Inicia sesión automáticamente
7. Redirecciona a `index.php`

### Login
1. Usuario ingresa email y contraseña
2. `procesar_login.php` busca usuario en BD
3. Verifica contraseña con `password_verify()`
4. Crea variables de sesión
5. Redirecciona según rol:
   - Admin → `/views/admin/dashboard.php`
   - Cliente → `/index.php`

---

## 🎯 RUTAS IMPORTANTES

### Públicas
- `/index.php` - Página principal
- `/views/login.php` - Login y registro
- `/detalles_tour.php?id=X` - Detalles de tour

### Usuario Autenticado
- `/views/user/perfil_user.php` - Perfil del usuario

### Administrador
- `/views/admin/dashboard.php` - Panel principal
- `/views/admin/paquetes.php` - Gestión de paquetes
- `/views/admin/reservas.php` - Gestión de reservas

---

## 🛠️ CONFIGURACIÓN DE CONEXIÓN

Archivo: `config/conexion.php`
```php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'gestion_turismo_v3';
```

---

## 📝 NOTAS IMPORTANTES

1. **Imágenes de Paquetes**: 
   - Se cargan desde `paquetes.foto_portada_url`
   - Si está vacío, usa `assets/fondo.jpg`
   - Ruta relativa desde `index.php`

2. **Roles de Usuario**:
   - Solo hay 2 roles: 'Admin' y 'Cliente'
   - El registro siempre crea usuarios con rol 'Cliente'
   - El admin debe crearse manualmente en BD

3. **Seguridad**:
   - Todas las contraseñas usan bcrypt
   - Prepared statements en todas las consultas
   - Validación de sesión en páginas protegidas

4. **Responsive**:
   - Footer se adapta a 1 columna en móviles
   - Botón WhatsApp ajusta tamaño en pantallas pequeñas
   - Menú de usuario optimizado para móviles

---

## 🚀 PRÓXIMOS PASOS SUGERIDOS

1. Implementar sistema de reservas funcional
2. Agregar gestión de paquetes desde panel admin
3. Sistema de pagos
4. Notificaciones por email
5. Galería de imágenes por paquete
6. Sistema de calificaciones y reseñas
7. Exportación de reportes PDF

---

## 📞 CONTACTO

**Tumbes Tours**
- 📧 Email: info@tumbestours.com
- 📱 WhatsApp: +51 942 123 456
- 📍 Dirección: Jr. Bolívar 234, Tumbes - Perú

---

**Última actualización:** 2024
**Versión:** 3.0
