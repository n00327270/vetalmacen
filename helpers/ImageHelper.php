<?php
/**
 * Helper para procesamiento de imágenes
 * Validaciones y optimización antes de subir a Azure
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/../config/azure_config.php';

class ImageHelper {
    
    /**
     * Validar imagen subida
     * @param array $file - Archivo de $_FILES
     * @return array - ['valid' => bool, 'error' => string]
     */
    public static function validateImage($file) {
        // Verificar si hay errores en la subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'error' => self::getUploadErrorMessage($file['error'])
            ];
        }

        // Validar tamaño
        if ($file['size'] > MAX_IMAGE_SIZE) {
            return [
                'valid' => false,
                'error' => 'La imagen excede el tamaño máximo de ' . (MAX_IMAGE_SIZE / 1024 / 1024) . 'MB'
            ];
        }

        // Validar tipo MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            return [
                'valid' => false,
                'error' => 'Tipo de archivo no permitido. Solo se aceptan: JPG, JPEG, PNG'
            ];
        }

        // Validar extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
            return [
                'valid' => false,
                'error' => 'Extensión de archivo no permitida'
            ];
        }

        // Verificar que sea una imagen real
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return [
                'valid' => false,
                'error' => 'El archivo no es una imagen válida'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Optimizar imagen antes de subir (opcional)
     * Redimensiona si es muy grande
     * @param string $filePath - Ruta temporal del archivo
     * @param int $maxWidth - Ancho máximo
     * @param int $maxHeight - Alto máximo
     * @return bool
     */
    public static function optimizeImage($filePath, $maxWidth = 1200, $maxHeight = 1200) {
        $imageInfo = getimagesize($filePath);
        
        if ($imageInfo === false) {
            return false;
        }

        list($width, $height, $type) = $imageInfo;

        // Si la imagen ya es pequeña, no hacer nada
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return true;
        }

        // Calcular nuevas dimensiones manteniendo proporción
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);

        // Crear imagen desde archivo según tipo
        switch ($type) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($filePath);
                break;
            default:
                return false;
        }

        if ($sourceImage === false) {
            return false;
        }

        // Crear nueva imagen redimensionada
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preservar transparencia para PNG
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        // Redimensionar
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Guardar imagen optimizada
        switch ($type) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($newImage, $filePath, 85); // Calidad 85%
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($newImage, $filePath, 8); // Compresión 8
                break;
            default:
                $result = false;
        }

        imagedestroy($sourceImage);
        imagedestroy($newImage);

        return $result;
    }

    /**
     * Obtener mensaje de error de subida
     */
    private static function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'El archivo excede el tamaño máximo permitido';
            case UPLOAD_ERR_PARTIAL:
                return 'El archivo se subió parcialmente';
            case UPLOAD_ERR_NO_FILE:
                return 'No se subió ningún archivo';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Falta la carpeta temporal';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Error al escribir el archivo en disco';
            case UPLOAD_ERR_EXTENSION:
                return 'Una extensión de PHP detuvo la subida del archivo';
            default:
                return 'Error desconocido al subir el archivo';
        }
    }

    /**
     * Generar imagen placeholder si no hay imagen
     */
    public static function getPlaceholderUrl() {
        return '/vetalmacen/public/images/placeholder.png';
    }
}