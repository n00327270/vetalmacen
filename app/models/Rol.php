<?php
require_once __DIR__ . '/Database.php';

class Rol {
    private $conn;
    private $table = 'rol';

    public $Id;
    public $Nombre;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los roles
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY Nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener rol por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener rol por nombre
     */
    public function getByNombre($nombre) {
        $query = "SELECT * FROM " . $this->table . " WHERE Nombre = :nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Crear nuevo rol
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " (Nombre) VALUES (:nombre)";
        $stmt = $this->conn->prepare($query);
        
        $this->Nombre = htmlspecialchars(strip_tags($this->Nombre));
        $stmt->bindParam(':nombre', $this->Nombre);
        
        return $stmt->execute();
    }

    /**
     * Actualizar rol
     */
    public function update() {
        $query = "UPDATE " . $this->table . " SET Nombre = :nombre WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->Nombre = htmlspecialchars(strip_tags($this->Nombre));
        $stmt->bindParam(':nombre', $this->Nombre);
        $stmt->bindParam(':id', $this->Id);
        
        return $stmt->execute();
    }

    /**
     * Eliminar rol
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->Id);
        return $stmt->execute();
    }
}