<?php
/**
 * Modelo Usuario
 * Maneja usuarios del sistema con autenticación
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/Database.php';

class Usuario {
    private $conn;
    private $table = 'usuario';

    public $Id;
    public $Username;
    public $Password;
    public $RolId;
    public $SucursalId;
    public $CreatedAt;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los usuarios con información de rol y sucursal
     */
    public function getAll() {
        $query = "SELECT u.*, r.Nombre as RolNombre, s.Sede as SucursalNombre
                  FROM " . $this->table . " u
                  INNER JOIN rol r ON u.RolId = r.Id
                  LEFT JOIN sucursal s ON u.SucursalId = s.Id
                  ORDER BY u.Username ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener usuario por ID con información completa
     */
    public function getById($id) {
        $query = "SELECT u.*, r.Nombre as RolNombre, s.Sede as SucursalNombre
                  FROM " . $this->table . " u
                  INNER JOIN rol r ON u.RolId = r.Id
                  LEFT JOIN sucursal s ON u.SucursalId = s.Id
                  WHERE u.Id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener usuario por username
     */
    public function getByUsername($username) {
        $query = "SELECT u.*, r.Nombre as RolNombre, s.Sede as SucursalNombre
                  FROM " . $this->table . " u
                  INNER JOIN rol r ON u.RolId = r.Id
                  LEFT JOIN sucursal s ON u.SucursalId = s.Id
                  WHERE u.Username = :username";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Autenticar usuario (login)
     */
    public function authenticate($username, $password) {
        $user = $this->getByUsername($username);
        
        if ($user && password_verify($password, $user['Password'])) {
            return $user;
        }
        
        return false;
    }

    /**
     * Crear nuevo usuario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (Username, Password, RolId, SucursalId) 
                  VALUES (:username, :password, :rol_id, :sucursal_id)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Username = htmlspecialchars(strip_tags($this->Username));
        $hashedPassword = password_hash($this->Password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':username', $this->Username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':rol_id', $this->RolId);
        $stmt->bindParam(':sucursal_id', $this->SucursalId);
        
        return $stmt->execute();
    }

    /**
     * Actualizar usuario
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET Username = :username, 
                      RolId = :rol_id, 
                      SucursalId = :sucursal_id 
                  WHERE Id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Username = htmlspecialchars(strip_tags($this->Username));
        
        $stmt->bindParam(':username', $this->Username);
        $stmt->bindParam(':rol_id', $this->RolId);
        $stmt->bindParam(':sucursal_id', $this->SucursalId);
        $stmt->bindParam(':id', $this->Id);
        
        return $stmt->execute();
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword($newPassword) {
        $query = "UPDATE " . $this->table . " SET Password = :password WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $this->Id);
        
        return $stmt->execute();
    }

    /**
     * Eliminar usuario
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->Id);
        return $stmt->execute();
    }

    /**
     * Verificar si un username ya existe
     */
    public function usernameExists($username, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE Username = :username";
        
        if ($excludeId) {
            $query .= " AND Id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        
        if ($excludeId) {
            $stmt->bindParam(':id', $excludeId);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}