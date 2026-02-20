<?php
/**
 * Modelo Permiso
 * Gestión de permisos por rol
 */

require_once __DIR__ . '/Database.php';

class Permiso {
    private $conn;
    private $table = 'permiso';

    public $Id;
    public $RolId;
    public $ModuloId;
    public $FechaAsignacion;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los roles
     */
    public function getAllRoles() {
        $query = "SELECT Id, Nombre FROM rol ORDER BY Id ASC";
        $stmt  = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener todos los roles con conteo de permisos
     */
    public function getRolesConPermisos() {
        $query = "SELECT r.Id, r.Nombre,
                         COUNT(p.ModuloId) as total_permisos
                  FROM rol r
                  LEFT JOIN permiso p ON r.Id = p.RolId
                  GROUP BY r.Id, r.Nombre
                  ORDER BY r.Id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener rol por ID
     */
    public function getRolById($rolId) {
        $query = "SELECT * FROM rol WHERE Id = :id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $rolId);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener IDs de módulos asignados a un rol
     * 
     * @param int $rolId
     * @return array Array de IDs de módulos
     */
    public function getPermisosByRol($rolId) {
        $query = "SELECT ModuloId FROM " . $this->table . " 
                  WHERE RolId = :rol_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rol_id', $rolId);
        $stmt->execute();
        
        return array_column($stmt->fetchAll(), 'ModuloId');
    }

    /**
     * Eliminar todos los permisos de un rol
     * 
     * @param int $rolId
     * @return bool
     */
    public function deletePermisosByRol($rolId) {
        $query = "DELETE FROM " . $this->table . " WHERE RolId = :rol_id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':rol_id', $rolId);
        return $stmt->execute();
    }

    /**
     * Insertar permisos en batch para un rol
     * 
     * @param int $rolId
     * @param array $modulosIds Array de IDs de módulos
     * @return bool
     */
    public function insertPermisosBatch($rolId, $modulosIds) {
        if (empty($modulosIds)) {
            return true; // Sin permisos que insertar
        }

        // Preparar placeholders: (?, ?), (?, ?), ...
        $placeholders = implode(',', array_fill(0, count($modulosIds), '(?, ?)'));
        $query = "INSERT INTO " . $this->table . " (RolId, ModuloId) VALUES " . $placeholders;
        
        $stmt = $this->conn->prepare($query);
        
        // Construir array de valores
        $valores = [];
        foreach ($modulosIds as $moduloId) {
            $valores[] = (int)$rolId;
            $valores[] = (int)$moduloId;
        }
        
        return $stmt->execute($valores);
    }

    /**
     * Actualizar permisos de un rol (DELETE + INSERT)
     * Usa transacción para garantizar consistencia
     * 
     * @param int $rolId
     * @param array $modulosIds Array de IDs de módulos
     * @return bool
     */
    public function actualizarPermisos($rolId, $modulosIds) {
        try {
            $this->conn->beginTransaction();
            
            // 1. Eliminar permisos actuales
            $this->deletePermisosByRol($rolId);
            
            // 2. Insertar nuevos permisos
            if (!empty($modulosIds)) {
                $this->insertPermisosBatch($rolId, $modulosIds);
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('Error actualizando permisos: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Contar total de módulos activos (para cálculo de porcentaje)
     */
    public function getTotalModulosActivos() {
        $query = "SELECT COUNT(*) as total FROM modulo WHERE Activo = 1";
        $stmt  = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch()['total'];
    }

    /**
     * Verificar si un rol tiene un permiso específico
     * 
     * @param int $rolId
     * @param int $moduloId
     * @return bool
     */
    public function tienePermiso($rolId, $moduloId) {
        $query = "SELECT COUNT(*) as tiene 
                  FROM " . $this->table . " 
                  WHERE RolId = :rol_id AND ModuloId = :modulo_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rol_id', $rolId);
        $stmt->bindParam(':modulo_id', $moduloId);
        $stmt->execute();
        
        return $stmt->fetch()['tiene'] > 0;
    }

    /**
     * Copiar permisos de un rol a otro
     * Útil para crear roles similares rápidamente
     * 
     * @param int $rolOrigenId
     * @param int $rolDestinoId
     * @return bool
     */
    public function copiarPermisos($rolOrigenId, $rolDestinoId) {
        try {
            $this->conn->beginTransaction();
            
            // 1. Eliminar permisos actuales del destino
            $this->deletePermisosByRol($rolDestinoId);
            
            // 2. Copiar permisos del origen
            $query = "INSERT INTO " . $this->table . " (RolId, ModuloId)
                      SELECT :rol_destino, ModuloId 
                      FROM " . $this->table . " 
                      WHERE RolId = :rol_origen";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':rol_destino', $rolDestinoId);
            $stmt->bindParam(':rol_origen', $rolOrigenId);
            $stmt->execute();
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('Error copiando permisos: ' . $e->getMessage());
            return false;
        }
    }
}