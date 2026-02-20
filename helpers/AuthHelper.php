<?php
require_once __DIR__ . '/SessionHelper.php';

class AuthHelper {
    
    /**
     * Verificar si el usuario está autenticado
     * Si no lo está, redirige al login
     */
    public static function requireAuth() {
        if (!SessionHelper::isAuthenticated()) {
            header('Location: /vetalmacen/public/index.php?url=auth/login');
            exit();
        }
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public static function hasRole($roleName) {
        $user = SessionHelper::getUser();
        if (!$user) {
            return false;
        }
        return strtolower($user['rol_nombre']) === strtolower($roleName);
    }

    /**
     * Verificar si el usuario tiene uno de los roles especificados
     */
    public static function hasAnyRole($roles) {
        $user = SessionHelper::getUser();
        if (!$user) {
            return false;
        }
        
        // ⭐ NUEVO: SuperUsuario tiene todos los roles
        if (isset($user['es_super']) && $user['es_super'] == 1) {
            return true;
        }
        
        foreach ($roles as $role) {
            if (strtolower($user['rol_nombre']) === strtolower($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Requerir un rol específico (redirige si no lo tiene)
     */
    public static function requireRole($roleName) {
        self::requireAuth();
        
        if (!self::hasRole($roleName)) {
            SessionHelper::setFlash('danger', 'No tienes permisos para acceder a esta sección');
            header('Location: /vetalmacen/public/index.php?url=dashboard');
            exit();
        }
    }

    /**
     * Requerir uno de varios roles
     */
    public static function requireAnyRole($roles) {
        self::requireAuth();
        
        if (!self::hasAnyRole($roles)) {
            SessionHelper::setFlash('danger', 'No tienes permisos para acceder a esta sección');
            header('Location: /vetalmacen/public/index.php?url=dashboard');
            exit();
        }
    }

    /**
     * Verificar si el usuario es administrador
     */
    public static function isAdmin() {
        $user = SessionHelper::getUser();
        
        // ⭐ NUEVO: SuperUsuario cuenta como admin
        if (isset($user['es_super']) && $user['es_super'] == 1) {
            return true;
        }
        
        return self::hasRole('Administrador');
    }

    /**
     * Hash de contraseña
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verificar contraseña
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Redirigir si ya está autenticado (para login)
     */
    public static function redirectIfAuthenticated() {
        if (SessionHelper::isAuthenticated()) {
            header('Location: /vetalmacen/public/index.php?url=dashboard');
            exit();
        }
    }

    /**
     * ⭐ NUEVO: Verificar si el usuario es SuperUsuario
     */
    public static function esSuperUsuario() {
        $user = SessionHelper::getUser();
        return isset($user['es_super']) && $user['es_super'] == 1;
    }

    /**
     * ⭐ NUEVO: Requerir que el usuario sea SuperUsuario
     */
    public static function requireSuperUsuario() {
        self::requireAuth();
        
        if (!self::esSuperUsuario()) {
            SessionHelper::setFlash('danger', 'Esta acción solo está disponible para el SuperUsuario');
            header('Location: /vetalmacen/public/index.php?url=dashboard');
            exit();
        }
    }
}