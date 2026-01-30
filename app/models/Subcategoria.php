<?php
/**
 * Modelo Subcategoria
 * Maneja las subcategorías de productos
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/Database.php';

class Subcategoria {
    private $conn;
    private $table = 'subcategoria';

    public $Id;
    public $CategoriaId;
    public $Nombre;
    public $Descripcion;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todas las subcategorías con nombre de categoría
     */
    public function getAll() {
        $query = "SELECT sc.*, c.Nombre as CategoriaNombre
                  FROM " . $this->table . " sc
                  INNER JOIN categoria c ON sc.CategoriaId = c.Id
                  ORDER BY c.Nombre, sc.Nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener subcategoría por ID
     */
    public function getById($id) {
        $query = "SELECT sc.*, c.Nombre as CategoriaNombre
                  FROM " . $this->table . " sc
                  INNER JOIN categoria c ON sc.CategoriaId = c.Id
                  WHERE sc.Id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener subcategorías por categoría
     */
    public function getByCategoria($categoriaId) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE CategoriaId = :categoria_id 
                  ORDER BY Nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categoria_id', $categoriaId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crear nueva subcategoría
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (CategoriaId, Nombre, Descripcion) 
                  VALUES (:categoria_id, :nombre, :descripcion)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Nombre = htmlspecialchars(strip_tags($this->Nombre));
        $this->Descripcion = htmlspecialchars(strip_tags($this->Descripcion));
        
        $stmt->bindParam(':categoria_id', $this->CategoriaId);
        $stmt->bindParam(':nombre', $this->Nombre);
        $stmt->bindParam(':descripcion', $this->Descripcion);
        
        return $stmt->execute();
    }

    /**
     * Actualizar subcategoría
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET CategoriaId = :categoria_id,
                      Nombre = :nombre, 
                      Descripcion = :descripcion 
                  WHERE Id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Nombre = htmlspecialchars(strip_tags($this->Nombre));
        $this->Descripcion = htmlspecialchars(strip_tags($this->Descripcion));
        
        $stmt->bindParam(':categoria_id', $this->CategoriaId);
        $stmt->bindParam(':nombre', $this->Nombre);
        $stmt->bindParam(':descripcion', $this->Descripcion);
        $stmt->bindParam(':id', $this->Id);
        
        return $stmt->execute();
    }

    /**
     * Eliminar subcategoría
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->Id);
        return $stmt->execute();
    }
}