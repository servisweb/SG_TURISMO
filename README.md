# Sistema de Gestión de Turismo - Tumbes Tours

## 📋 Descripción
Sistema web para la gestión de paquetes turísticos, reservas y administración de tours en Tumbes, Perú.

## 🗂️ Estructura del Proyecto

```
SG_TURISMO/
├── assets/                    # Recursos estáticos (imágenes)
│   └── BD_sql/               # Scripts de base de datos
├── config/                    # Configuración
│   └── conexion.php          # Conexión a MySQL
├── controladores/            # Lógica de negocio
│   ├── cards-destinos.php    # Datos de tours
│   ├── detalles_tour.php     # Detalles de tours
│   ├── procesar_login.php    # Autenticación
│   ├── procesar_registro.php # Registro de usuarios
│   ├── procesar_reserva.php  # Procesamiento de reservas
│   └── cerrar_sesion.php     # Cierre de sesión
├── css/                      # Estilos
│   └── estyle.css           # Estilos principales
├── js/                       # JavaScript
│   └── main.js              # Scripts principales
├── views/                    # Vistas
│   ├── admin/               # Panel administrativo
│   │   ├── dashboard.php    # Dashboard principal
│   │   ├── paquetes.php     # Gestión de paquetes
│   │   ├── paquetes_crear.php # Crear paquetes
│   │   └── reservas.php     # Gestión de reservas
│   ├── user/                # Vistas de usuario
│   ├── login.php            # Login/Registro
│   ├── reservar.php         # Formulario de reserva
│   └── confirmacion.php     # Confirmación de reserva
├── index.php                 # Página principal
└── detalles_tour.php        # Detalles de tour
```

## 🚀 Instalación

### Requisitos Previos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Extensión mysqli habilitada

### Pasos de Instalación

1. **Clonar o descargar el proyecto**
   ```bash
   cd d:\TECNO\5to_CICLO\CURSOS_\SERVICE-WEB\
   ```

2. **Configurar la base de datos**
   - Crear la base de datos:
     ```sql
     CREATE DATABASE gestion_turismo_v3 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     ```
   - Importar el script SQL:
     ```bash
     mysql -u root -p gestion_turismo_v3 < assets/BD_sql/db_tumbes_tours.sql
     ```

3. **Configurar conexión**
   - Editar `config/conexion.php` si es necesario:
     ```php
     $host = 'localhost';
     $user = 'root';
     $password = '';
     $database = 'gestion_turismo_v3';
     ```

4. **Crear usuario administrador**
   ```sql
   INSERT INTO usuarios (tipo_documento, numero_documento, nombres, apellidos, email, telefono, password_hash, rol, estado) 
   VALUES ('DNI', '12345678', 'Admin', 'Sistema', 'admin@tumbestours.com', '999999999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Activo');
   ```
   - Email: `admin@tumbestours.com`
   - Password: `password` (cambiar después del primer login)

## 🔐 Sistema de Autenticación

### Login
- **Archivo**: `views/login.php`
- **Procesador**: `controladores/procesar_login.php`
- **Funcionalidades**:
  - Validación de email y contraseña
  - Verificación con base de datos
  - Encriptación con `password_verify()`
  - Redirección según rol (Admin/Cliente)
  - Manejo de sesiones

### Registro
- **Archivo**: `views/login.php` (formulario integrado)
- **Procesador**: `controladores/procesar_registro.php`
- **Funcionalidades**:
  - Validación de datos
  - Verificación de email único
  - Encriptación de contraseña con `password_hash()`
  - Inserción en base de datos
  - Login automático después del registro

## 📊 Base de Datos

### Tablas Principales

#### usuarios
```sql
- id_usuario (PK)
- tipo_documento (DNI, CE, PASSPORT)
- numero_documento
- nombres
- apellidos
- email (UNIQUE)
- telefono
- password_hash
- rol (Admin, Cliente)
- estado (Activo, Inactivo)
- created_at, updated_at
```

#### paquetes
```sql
- id_paquete (PK)
- id_destino (FK)
- codigo_paquete (UNIQUE)
- titulo
- descripcion_general
- foto_portada_url
- precio_base
- precio_persona
- precio_grupo
- cupo_minimo, cupo_maximo
- estado (Activo, Inactivo)
```

#### reservas
```sql
- id_reserva (PK)
- codigo_reserva (UNIQUE)
- id_usuario_titular (FK)
- id_salida (FK)
- cantidad_pasajeros
- precio_total
- estado_reserva (Pendiente, Parcial, Pagada, Cancelada)
```

## 🎯 Funcionalidades Principales

### Para Clientes
1. **Explorar Tours**
   - Ver catálogo de paquetes
   - Filtrar por categoría (Playa, Naturaleza, Cultura)
   - Ver detalles completos del tour

2. **Reservar Tours**
   - Seleccionar cantidad de personas
   - Elegir guía (opcional)
   - Calcular precio automático
   - Completar formulario de reserva

3. **Gestión de Cuenta**
   - Registro de usuario
   - Login/Logout
   - Ver historial de reservas

### Para Administradores
1. **Dashboard**
   - Estadísticas generales
   - Reservas recientes
   - Acciones rápidas

2. **Gestión de Paquetes**
   - Listar paquetes
   - Crear nuevo paquete
   - Editar/Eliminar paquetes
   - Cambiar estado

3. **Gestión de Reservas**
   - Ver todas las reservas
   - Filtrar por estado
   - Actualizar estado de reserva
   - Exportar reportes

## 🔒 Seguridad Implementada

1. **Autenticación**
   - Contraseñas encriptadas con `password_hash()`
   - Validación de sesiones
   - Protección de rutas administrativas

2. **Validación de Datos**
   - Sanitización de inputs con `htmlspecialchars()`
   - Prepared statements para prevenir SQL Injection
   - Validación de email y formatos

3. **Control de Acceso**
   - Verificación de rol (Admin/Cliente)
   - Redirección automática según permisos
   - Protección de vistas administrativas

## 📝 Rutas Principales

### Públicas
- `/index.php` - Página principal
- `/detalles_tour.php?id=X` - Detalles de tour
- `/views/login.php` - Login/Registro

### Autenticadas (Cliente)
- `/views/reservar.php` - Formulario de reserva
- `/views/confirmacion.php` - Confirmación de reserva
- `/views/user/perfil_user.php` - Perfil de usuario

### Administrativas (Admin)
- `/views/admin/dashboard.php` - Dashboard
- `/views/admin/paquetes.php` - Gestión de paquetes
- `/views/admin/paquetes_crear.php` - Crear paquete
- `/views/admin/reservas.php` - Gestión de reservas

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Estilos**: CSS personalizado
- **Iconos**: Font Awesome 6.0
- **Arquitectura**: MVC simplificado

## 📌 Notas Importantes

1. **Carpeta html/**
   - Contiene archivos antiguos (conocer_mas.html)
   - Los archivos activos están en la raíz del proyecto
   - Se recomienda eliminar esta carpeta después de verificar

2. **Sesiones**
   - Las sesiones se manejan con `$_SESSION`
   - Variables importantes:
     - `$_SESSION['user_id']`
     - `$_SESSION['user_email']`
     - `$_SESSION['user_name']`
     - `$_SESSION['user_rol']`

3. **Escalabilidad**
   - Estructura modular para fácil mantenimiento
   - Separación de lógica y presentación
   - Base de datos normalizada
   - Código comentado y documentado

## 🐛 Solución de Problemas

### Error de conexión a BD
```php
// Verificar en config/conexion.php
$host = 'localhost';
$user = 'root';
$password = ''; // Tu contraseña de MySQL
$database = 'gestion_turismo_v3';
```

### Sesión no persiste
```php
// Verificar que session_start() esté al inicio de cada archivo
session_start();
```

### Rutas rotas
- Verificar que los archivos estén en las ubicaciones correctas
- index.php y detalles_tour.php deben estar en la raíz
- Las vistas deben estar en /views/

## 📧 Contacto y Soporte

Para dudas o problemas con el sistema, contactar al equipo de desarrollo.

---

**Versión**: 3.0  
**Última actualización**: 2024  
**Desarrollado para**: Tumbes Tours
