<?php
/**
 * Helper para validaciones comunes
 * Fecha: 2026-01-23
 */

class ValidationHelper {
    
    /**
     * Validar que un campo no esté vacío
     */
    public static function required($value) {
        return !empty(trim($value));
    }

    /**
     * Validar longitud mínima
     */
    public static function minLength($value, $min) {
        return strlen(trim($value)) >= $min;
    }

    /**
     * Validar longitud máxima
     */
    public static function maxLength($value, $max) {
        return strlen(trim($value)) <= $max;
    }

    /**
     * Validar formato de email
     */
    public static function email($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar que sea numérico
     */
    public static function numeric($value) {
        return is_numeric($value);
    }

    /**
     * Validar que sea un entero positivo
     */
    public static function positiveInteger($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false && $value > 0;
    }

    /**
     * Validar que sea un número decimal positivo
     */
    public static function positiveDecimal($value) {
        return is_numeric($value) && $value >= 0;
    }

    /**
     * Validar formato de teléfono peruano
     */
    public static function phone($value) {
        // Acepta formatos: 987654321, +51987654321, 01-4567890
        $pattern = '/^(\+51)?[0-9]{9,11}$/';
        return preg_match($pattern, str_replace([' ', '-'], '', $value));
    }

    /**
     * Validar RUC peruano (11 dígitos)
     */
    public static function ruc($value) {
        return preg_match('/^[0-9]{11}$/', $value);
    }

    /**
     * Sanitizar string (eliminar HTML y caracteres especiales)
     */
    public static function sanitizeString($value) {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validar extensión de archivo
     */
    public static function fileExtension($filename, $allowedExtensions) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $allowedExtensions);
    }

    /**
     * Validar tamaño de archivo
     */
    public static function fileSize($fileSize, $maxSize) {
        return $fileSize <= $maxSize;
    }

    /**
     * Validar tipo MIME de archivo
     */
    public static function fileMimeType($mimeType, $allowedTypes) {
        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Validar formato de fecha (YYYY-MM-DD)
     */
    public static function date($value) {
        $d = DateTime::createFromFormat('Y-m-d', $value);
        return $d && $d->format('Y-m-d') === $value;
    }

    /**
     * Validar que un valor esté en un array de opciones
     */
    public static function inArray($value, $options) {
        return in_array($value, $options);
    }
}