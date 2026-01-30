<?php
/**
 * Modelo Categoria
 * Maneja las categorías de productos
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/Database.php';

class Categoria {
    private $conn;
    private $table = 'categoria';

    public $Id;
    public $Nombre;
    public $Descripcion;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todas las categorías
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY Nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener categoría por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener categoría con conteo de subcategorías
     */
    public function getAllWithSubcategoriaCount() {
        $query = "SELECT c.*, 
                         COUNT(sc.Id) as SubcategoriaCount
                  FROM " . $this->table . " c
                  LEFT JOIN subcategoria sc ON c.Id = sc.CategoriaId
                  GROUP BY c.Id
                  ORDER BY c.Nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crear nueva categoría
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (Nombre, Descripcion) 
                  VALUES (:nombre, :descripcion)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Nombre = htmlspecialchars(strip_tags($this->Nombre));
        $this->Descripcion = htmlspecialchars(strip_tags($this->Descripcion));
        
        $stmt->bindParam(':nombre', $this->Nombre);
        $stmt->bindParam(':descripcion', $this->Descripcion);
        
        return $stmt->execute();
    }

    /**
     * Actualizar categoría
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET Nombre = :nombre, 
                      Descripcion = :descripcion 
                  WHERE Id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Nombre = htmlspecialchars(strip_tags($this->Nombre));
        $this->Descripcion = htmlspecialchars(strip_tags($this->Descripcion));
        
        $stmt->bindParam(':nombre', $this->Nombre);
        $stmt->bindParam(':descripcion', $this->Descripcion);
        $stmt->bindParam(':id', $this->Id);
        
        return $stmt->execute();
    }

    /**
     * Eliminar categoría
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->Id);
        return $stmt->execute();
    }

    /**
     * Verificar si el nombre ya existe
     */
    public function nombreExists($nombre, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE Nombre = :nombre";
        
        if ($excludeId) {
            $query .= " AND Id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        
        if ($excludeId) {
            $stmt->bindParam(':id', $excludeId);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}