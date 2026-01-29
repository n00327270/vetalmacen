<?php
/**
 * Helper para manejo de sesiones
 * Fecha: 2026-01-23
 */

class SessionHelper {
    
    /**
     * Iniciar sesión si no está iniciada
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Establecer valor en sesión
     */
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Obtener valor de sesión
     */
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verificar si existe una clave en sesión
     */
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Eliminar valor de sesión
     */
    public static function delete($key) {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destruir toda la sesión
     */
    public static function destroy() {
        self::start();
        session_unset();
        session_destroy();
    }

    /**
     * Verificar si el usuario está autenticado
     */
    public static function isAuthenticated() {
        return self::has('usuario_id') && self::has('usuario_username');
    }

    /**
     * Obtener datos del usuario actual
     */
    public static function getUser() {
        if (!self::isAuthenticated()) {
            return null;
        }

        return [
            'id' => self::get('usuario_id'),
            'username' => self::get('usuario_username'),
            'rol_id' => self::get('usuario_rol_id'),
            'rol_nombre' => self::get('usuario_rol_nombre'),
            'sucursal_id' => self::get('usuario_sucursal_id')
        ];
    }

    /**
     * Establecer usuario en sesión (después del login)
     */
    public static function setUser($usuario) {
        self::set('usuario_id', $usuario['Id']);
        self::set('usuario_username', $usuario['Username']);
        self::set('usuario_rol_id', $usuario['RolId']);
        self::set('usuario_rol_nombre', $usuario['RolNombre'] ?? '');
        self::set('usuario_sucursal_id', $usuario['SucursalId'] ?? null);
    }

    /**
     * Cerrar sesión del usuario
     */
    public static function logout() {
        self::destroy();
    }

    /**
     * Establecer mensaje flash
     */
    public static function setFlash($type, $message) {
        self::set('flash_type', $type);
        self::set('flash_message', $message);
    }

    /**
     * Obtener y eliminar mensaje flash
     */
    public static function getFlash() {
        $type = self::get('flash_type');
        $message = self::get('flash_message');
        
        self::delete('flash_type');
        self::delete('flash_message');

        if ($type && $message) {
            return ['type' => $type, 'message' => $message];
        }
        
        return null;
    }

    /**
     * Regenerar ID de sesión (seguridad)
     */
    public static function regenerate() {
        self::start();
        session_regenerate_id(true);
    }
}