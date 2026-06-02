<?php
// config/config_uploads.php
// Configuración central para subida de imágenes

define('UPLOAD_BASE_PATH', __DIR__ . '/../assets/uploads/');

// Si la app está en una carpeta dentro de htdocs, construir la URL correcta.
$baseAppUrl = '';
if (isset($_SERVER['DOCUMENT_ROOT'])) {
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
    $appPath = realpath(__DIR__ . '/../');
    if ($docRoot && $appPath && strpos($appPath, $docRoot) === 0) {
        $baseAppUrl = str_replace('\\', '/', substr($appPath, strlen($docRoot)));
        if ($baseAppUrl === '') {
            $baseAppUrl = '';
        }
    }
}
$baseAppUrl = rtrim($baseAppUrl, '/') . '/';
define('UPLOAD_BASE_URL',  $baseAppUrl . 'assets/uploads/');
define('UPLOAD_MAX_MB',    5);
define('UPLOAD_TYPES',     ['image/jpeg', 'image/png', 'image/webp']);
define('UPLOAD_EXTENSIONS',['jpg', 'jpeg', 'png', 'webp']);
define('IMG_WIDTH_MAX',    1200);
define('IMG_HEIGHT_MAX',   900);

// Subcarpetas por entidad
define('UPLOAD_DESTINOS',  UPLOAD_BASE_PATH . 'destinos/');
define('UPLOAD_PAQUETES',  UPLOAD_BASE_PATH . 'paquetes/');
define('UPLOAD_GUIAS',     UPLOAD_BASE_PATH . 'guias/');
define('UPLOAD_AVATARS',   UPLOAD_BASE_PATH . 'avatars/');

// Crear carpetas si no existen
$carpetas = [UPLOAD_BASE_PATH, UPLOAD_DESTINOS, UPLOAD_PAQUETES, UPLOAD_GUIAS, UPLOAD_AVATARS];
foreach ($carpetas as $carpeta) {
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0755, true);
    }
}