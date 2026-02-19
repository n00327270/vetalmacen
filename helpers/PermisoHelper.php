<?php
/**
 * PermisoHelper
 * Sistema de validación de permisos basado en roles
 * 
 * JERARQUÍA:
 * - SuperUsuario (es_super = 1): Acceso TOTAL (hardcodeado)
 * - Administrador: Rol normal, se le PUEDEN bloquear módulos
 * - Almacenero: Permisos operativos según BD
 * - Logística: Solo lectura según BD
 */

require_once __DIR__ . '/../app/models/Modulo.php';

class PermisoHelper {
    
    /**
     * Verificar si el usuario actual tiene permiso para una ruta específica
     * 
     * @param string $ruta - Ruta del módulo (ej: 'productos/crear')
     * @return bool
     */
    public static function tienePermiso($ruta) {
        $user = SessionHelper::getUser();
        if (!$user) {
            return false;
        }
        
        // ⭐ SOLO SuperUsuario tiene acceso total (hardcodeado)
        if (isset($user['es_super']) && $user['es_super'] == 1) {
            return true;
        }
        
        // ⭐ TODOS LOS DEMÁS (incluyendo Administrador) consultan BD
        $rolId = $user['rol_id'];
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "SELECT COUNT(*) as tiene_permiso
                  FROM permiso p
                  INNER JOIN modulo m ON p.ModuloId = m.Id
                  WHERE p.RolId = :rol_id 
                    AND m.Ruta = :ruta 
                    AND m.Activo = 1";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':rol_id', $rolId);
        $stmt->bindParam(':ruta', $ruta);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['tiene_permiso'] > 0;
    }

    /**
     * Verificar permiso o redirigir a página de acceso denegado
     * Usar en controladores para proteger acciones
     * 
     * @param string $ruta - Ruta del módulo
     */
    public static function requirePermiso($ruta) {
        if (!self::tienePermiso($ruta)) {
            SessionHelper::setFlash('danger', 'No tienes permisos para acceder a esta sección');
            header('Location: /vetalmacen/public/index.php?url=acceso-denegado');
            exit();
        }
    }

    /**
     * Obtener menú completo del usuario según sus permisos
     * Retorna estructura jerárquica para navbar
     * 
     * @return array - Árbol de secciones con módulos permitidos
     */
    public static function getMenuDelUsuario() {
        $user = SessionHelper::getUser();
        if (!$user) {
            return [];
        }
        
        // ⭐ SOLO SuperUsuario ve todo (hardcodeado)
        if (isset($user['es_super']) && $user['es_super'] == 1) {
            $moduloModel = new Modulo();
            return $moduloModel->getArbolCompleto();
        }
        
        // ⭐ TODOS LOS DEMÁS (incluyendo Administrador) filtrados por BD
        $rolId = $user['rol_id'];
        $database = new Database();
        $conn = $database->getConnection();
        
        // Obtener secciones permitidas (Nivel 1)
        $query = "SELECT DISTINCT m.*
                  FROM modulo m
                  INNER JOIN permiso p ON m.Id = p.ModuloId
                  WHERE p.RolId = :rol_id 
                    AND m.Nivel = 1 
                    AND m.Activo = 1
                  ORDER BY m.Orden ASC";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':rol_id', $rolId);
        $stmt->execute();
        $secciones = $stmt->fetchAll();
        
        $menu = [];
        
        foreach ($secciones as $seccion) {
            // Obtener módulos permitidos de esta sección (Nivel 2)
            // ✅ CORREGIDO: m.Id = p.ModuloId (antes decía m.ModuloId = m.Id)
            $query = "SELECT DISTINCT m.*
                      FROM modulo m
                      INNER JOIN permiso p ON m.Id = p.ModuloId
                      WHERE p.RolId = :rol_id 
                        AND m.IdPadre = :seccion_id 
                        AND m.Nivel = 2 
                        AND m.Activo = 1
                      ORDER BY m.Orden ASC";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':rol_id', $rolId);
            $stmt->bindParam(':seccion_id', $seccion['Id']);
            $stmt->execute();
            
            $seccion['modulos'] = $stmt->fetchAll();
            
            // Solo agregar sección si tiene módulos permitidos
            if (!empty($seccion['modulos'])) {
                $menu[] = $seccion;
            }
        }
        
        return $menu;
    }

    /**
     * Verificar si tiene permiso a nivel de módulo (Nivel 2)
     * Útil para mostrar/ocultar botones en vistas
     * 
     * @param string $ruta - Ruta del módulo
     * @return bool
     */
    public static function puedeAccederModulo($ruta) {
        return self::tienePermiso($ruta);
    }

    /**
     * Verificar si tiene algún permiso dentro de una sección
     * 
     * @param int $seccionId - ID de la sección
     * @return bool
     */
    public static function tienePermisoEnSeccion($seccionId) {
        $user = SessionHelper::getUser();
        if (!$user) {
            return false;
        }
        
        // SuperUsuario tiene todo
        if (isset($user['es_super']) && $user['es_super'] == 1) {
            return true;
        }
        
        $rolId = $user['rol_id'];
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "SELECT COUNT(*) as tiene_permiso
                  FROM permiso p
                  INNER JOIN modulo m ON p.ModuloId = m.Id
                  WHERE p.RolId = :rol_id 
                    AND (m.Id = :seccion_id OR m.IdPadre = :seccion_id)
                    AND m.Activo = 1";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':rol_id', $rolId);
        $stmt->bindParam(':seccion_id', $seccionId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['tiene_permiso'] > 0;
    }

    /**
     * Obtener IDs de módulos permitidos para el usuario actual
     * Útil para checkboxes de permisos
     * 
     * @return array - Array de IDs
     */
    public static function getModulosPermitidosIds() {
        $user = SessionHelper::getUser();
        if (!$user) {
            return [];
        }
        
        $rolId = $user['rol_id'];
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "SELECT ModuloId FROM permiso WHERE RolId = :rol_id";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':rol_id', $rolId);
        $stmt->execute();
        
        return array_column($stmt->fetchAll(), 'ModuloId');
    }

    /**
     * Verificar si el usuario actual es SuperUsuario
     * 
     * @return bool
     */
    public static function esSuperUsuario() {
        $user = SessionHelper::getUser();
        return isset($user['es_super']) && $user['es_super'] == 1;
    }

    /**
     * Requerir que el usuario sea SuperUsuario
     * Usar para acciones críticas del sistema
     */
    public static function requireSuperUsuario() {
        if (!self::esSuperUsuario()) {
            SessionHelper::setFlash('danger', 'Esta acción solo está disponible para el SuperUsuario del sistema');
            header('Location: /vetalmacen/public/index.php?url=dashboard');
            exit();
        }
    }
}