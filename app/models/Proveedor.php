<?php
require_once __DIR__ . '/Database.php';

class Proveedor {
    private $conn;
    private $table = 'proveedor';

    public $Id;
    public $RazonSocial;
    public $RUC;
    public $NombreContacto;
    public $Direccion;
    public $Telefono;
    public $Email;
    public $CreatedAt;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los proveedores
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY RazonSocial ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener proveedor por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Buscar proveedores
     */
    public function search($searchTerm) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE RazonSocial LIKE :search 
                     OR RUC LIKE :search 
                     OR NombreContacto LIKE :search
                  ORDER BY RazonSocial ASC";
        
        $stmt = $this->conn->prepare($query);
        $searchParam = "%{$searchTerm}%";
        $stmt->bindParam(':search', $searchParam);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crear nuevo proveedor
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (RazonSocial, RUC, NombreContacto, Direccion, Telefono, Email) 
                  VALUES (:razon_social, :ruc, :nombre_contacto, :direccion, :telefono, :email)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->RazonSocial = htmlspecialchars(strip_tags($this->RazonSocial));
        $this->RUC = htmlspecialchars(strip_tags($this->RUC));
        $this->NombreContacto = htmlspecialchars(strip_tags($this->NombreContacto));
        $this->Direccion = htmlspecialchars(strip_tags($this->Direccion));
        $this->Telefono = htmlspecialchars(strip_tags($this->Telefono));
        $this->Email = htmlspecialchars(strip_tags($this->Email));
        
        $stmt->bindParam(':razon_social', $this->RazonSocial);
        $stmt->bindParam(':ruc', $this->RUC);
        $stmt->bindParam(':nombre_contacto', $this->NombreContacto);
        $stmt->bindParam(':direccion', $this->Direccion);
        $stmt->bindParam(':telefono', $this->Telefono);
        $stmt->bindParam(':email', $this->Email);
        
        return $stmt->execute();
    }

    /**
     * Actualizar proveedor
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET RazonSocial = :razon_social,
                      RUC = :ruc,
                      NombreContacto = :nombre_contacto,
                      Direccion = :direccion,
                      Telefono = :telefono,
                      Email = :email
                  WHERE Id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->RazonSocial = htmlspecialchars(strip_tags($this->RazonSocial));
        $this->RUC = htmlspecialchars(strip_tags($this->RUC));
        $this->NombreContacto = htmlspecialchars(strip_tags($this->NombreContacto));
        $this->Direccion = htmlspecialchars(strip_tags($this->Direccion));
        $this->Telefono = htmlspecialchars(strip_tags($this->Telefono));
        $this->Email = htmlspecialchars(strip_tags($this->Email));
        
        $stmt->bindParam(':razon_social', $this->RazonSocial);
        $stmt->bindParam(':ruc', $this->RUC);
        $stmt->bindParam(':nombre_contacto', $this->NombreContacto);
        $stmt->bindParam(':direccion', $this->Direccion);
        $stmt->bindParam(':telefono', $this->Telefono);
        $stmt->bindParam(':email', $this->Email);
        $stmt->bindParam(':id', $this->Id);
        
        return $stmt->execute();
    }

    /**
     * Eliminar proveedor
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->Id);
        return $stmt->execute();
    }

    /**
     * Verificar si RUC ya existe
     */
    public function rucExists($ruc, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE RUC = :ruc";
        
        if ($excludeId) {
            $query .= " AND Id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':ruc', $ruc);
        
        if ($excludeId) {
            $stmt->bindParam(':id', $excludeId);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}