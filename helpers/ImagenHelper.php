<?php
// helpers/ImagenHelper.php
// Clase que maneja subida, redimensión y eliminación de imágenes

require_once __DIR__ . '/../config/config_uploads.php';

class ImagenHelper
{
    /**
     * Sube una imagen, la redimensiona y devuelve la ruta relativa para guardar en DB.
     *
     * @param array  $archivo   El elemento de $_FILES['campo']
     * @param string $carpeta   Constante de carpeta: UPLOAD_DESTINOS, etc.
     * @param string $prefijo   Prefijo del nombre: 'destino', 'guia', etc.
     * @return array ['ok' => bool, 'ruta' => string, 'error' => string]
     */
    public static function subir(array $archivo, string $carpeta, string $prefijo): array
    {
        // 1. Verificar que se subió algo
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'No se recibió ningún archivo.'];
        }

        // 2. Verificar tamaño (máx 5 MB)
        $maxBytes = UPLOAD_MAX_MB * 1024 * 1024;
        if ($archivo['size'] > $maxBytes) {
            return ['ok' => false, 'error' => "La imagen supera los " . UPLOAD_MAX_MB . " MB."];
        }

        // 3. Verificar tipo MIME real (no confiar solo en la extensión)
        $tipoReal = mime_content_type($archivo['tmp_name']);
        if (!in_array($tipoReal, UPLOAD_TYPES)) {
            return ['ok' => false, 'error' => 'Solo se permiten imágenes JPG, PNG o WebP.'];
        }

        // 4. Generar nombre único para evitar colisiones
        $extension = self::extensionDesdeMime($tipoReal);
        $nombreArchivo = $prefijo . '_' . uniqid() . '.' . $extension;
        $rutaFisica = $carpeta . $nombreArchivo;

        // 5. Redimensionar y guardar (evita imágenes enormes)
        $resultado = self::redimensionarYGuardar(
            $archivo['tmp_name'],
            $rutaFisica,
            $tipoReal,
            IMG_WIDTH_MAX,
            IMG_HEIGHT_MAX
        );

        if (!$resultado) {
            return ['ok' => false, 'error' => 'Error al procesar la imagen.'];
        }

        // 6. Devolver la ruta relativa para guardar en la DB
        $rutaRelativa = str_replace(UPLOAD_BASE_PATH, '', $rutaFisica);
        // Quedará algo como: "destinos/destino_6675abc123.jpg"
        return ['ok' => true, 'ruta' => $rutaRelativa];
    }

    /**
     * Elimina el archivo físico dado una ruta relativa guardada en la DB.
     */
    public static function eliminar(?string $rutaRelativa): void
    {
        if (empty($rutaRelativa)) return;

        $rutaFisica = UPLOAD_BASE_PATH . $rutaRelativa;
        if (file_exists($rutaFisica)) {
            unlink($rutaFisica);
        }
    }

    /**
     * Devuelve la URL pública de la imagen, o una imagen por defecto si no existe.
     */
    public static function url(?string $rutaRelativa, string $porDefecto = '/assets/img/no-imagen.jpg'): string
    {
        if (empty($rutaRelativa)) return $porDefecto;
        $rutaFisica = UPLOAD_BASE_PATH . $rutaRelativa;
        return file_exists($rutaFisica) ? UPLOAD_BASE_URL . $rutaRelativa : $porDefecto;
    }

    // ── Métodos privados ────────────────────────────────────────────

    private static function redimensionarYGuardar(
        string $origen,
        string $destino,
        string $mime,
        int $maxAncho,
        int $maxAlto
    ): bool {
        // Verificar si GD está disponible y las funciones necesarias existen
        if (!extension_loaded('gd')) {
            // Si GD no está disponible, copiar archivo sin redimensionar
            return @copy($origen, $destino);
        }

        // Cargar imagen según tipo
        $imgOrigen = match($mime) {
            'image/jpeg' => @imagecreatefromjpeg($origen),
            'image/png'  => @imagecreatefrompng($origen),
            'image/webp' => @imagecreatefromwebp($origen),
            default      => false
        };

        if (!$imgOrigen) {
            // Si falla al cargar la imagen, copiar archivo sin redimensionar
            return @copy($origen, $destino);
        }

        $anchoOrig = imagesx($imgOrigen);
        $altoOrig  = imagesy($imgOrigen);

        // Calcular nuevas dimensiones manteniendo proporción
        [$nuevoAncho, $nuevoAlto] = self::calcularDimensiones(
            $anchoOrig, $altoOrig, $maxAncho, $maxAlto
        );

        // Crear imagen redimensionada
        $imgNueva = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

        // Preservar transparencia para PNG y WebP
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($imgNueva, false);
            imagesavealpha($imgNueva, true);
            $transparente = imagecolorallocatealpha($imgNueva, 0, 0, 0, 127);
            imagefilledrectangle($imgNueva, 0, 0, $nuevoAncho, $nuevoAlto, $transparente);
        }

        imagecopyresampled($imgNueva, $imgOrigen, 0, 0, 0, 0,
            $nuevoAncho, $nuevoAlto, $anchoOrig, $altoOrig);

        // Guardar según tipo
        try {
            $ok = match($mime) {
                'image/jpeg' => @imagejpeg($imgNueva, $destino, 85),
                'image/png'  => @imagepng($imgNueva,  $destino, 7),
                'image/webp' => @imagewebp($imgNueva, $destino, 85),
                default      => false
            };
        } catch (Throwable $e) {
            $ok = false;
        }

        @imagedestroy($imgOrigen);
        @imagedestroy($imgNueva);

        return $ok;
    }

    private static function calcularDimensiones(
        int $anchoOrig, int $altoOrig, int $maxAncho, int $maxAlto
    ): array {
        // Si ya cabe, no redimensionar
        if ($anchoOrig <= $maxAncho && $altoOrig <= $maxAlto) {
            return [$anchoOrig, $altoOrig];
        }
        $ratioAncho = $maxAncho / $anchoOrig;
        $ratioAlto  = $maxAlto  / $altoOrig;
        $ratio      = min($ratioAncho, $ratioAlto);
        return [(int)($anchoOrig * $ratio), (int)($altoOrig * $ratio)];
    }

    private static function extensionDesdeMime(string $mime): string
    {
        return match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg'
        };
    }
}