<?php
/**
 * Modelo Sucursal
 * Maneja las sucursales de la veterinaria
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/Database.php';

class Sucursal {
    private $conn;
    private $table = 'sucursal';

    public $Id;
    public $Sede;
    public $Direccion;
    public $Telefono;
    public $Email;
    public $HorarioEntrega;
    public $Activo;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todas las sucursales
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY Sede ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener solo sucursales activas
     */
    public function getAllActive() {
        $query = "SELECT * FROM " . $this->table . " WHERE Activo = 1 ORDER BY Sede ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener sucursal por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Crear nueva sucursal
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (Sede, Direccion, Telefono, Email, HorarioEntrega, Activo) 
                  VALUES (:sede, :direccion, :telefono, :email, :horario, :activo)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Sede = htmlspecialchars(strip_tags($this->Sede));
        $this->Direccion = htmlspecialchars(strip_tags($this->Direccion));
        $this->Telefono = htmlspecialchars(strip_tags($this->Telefono));
        $this->Email = htmlspecialchars(strip_tags($this->Email));
        $this->HorarioEntrega = htmlspecialchars(strip_tags($this->HorarioEntrega));
        
        $stmt->bindParam(':sede', $this->Sede);
        $stmt->bindParam(':direccion', $this->Direccion);
        $stmt->bindParam(':telefono', $this->Telefono);
        $stmt->bindParam(':email', $this->Email);
        $stmt->bindParam(':horario', $this->HorarioEntrega);
        $stmt->bindParam(':activo', $this->Activo);
        
        return $stmt->execute();
    }

    /**
     * Actualizar sucursal
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET Sede = :sede, 
                      Direccion = :direccion, 
                      Telefono = :telefono, 
                      Email = :email, 
                      HorarioEntrega = :horario, 
                      Activo = :activo 
                  WHERE Id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Sede = htmlspecialchars(strip_tags($this->Sede));
        $this->Direccion = htmlspecialchars(strip_tags($this->Direccion));
        $this->Telefono = htmlspecialchars(strip_tags($this->Telefono));
        $this->Email = htmlspecialchars(strip_tags($this->Email));
        $this->HorarioEntrega = htmlspecialchars(strip_tags($this->HorarioEntrega));
        
        $stmt->bindParam(':sede', $this->Sede);
        $stmt->bindParam(':direccion', $this->Direccion);
        $stmt->bindParam(':telefono', $this->Telefono);
        $stmt->bindParam(':email', $this->Email);
        $stmt->bindParam(':horario', $this->HorarioEntrega);
        $stmt->bindParam(':activo', $this->Activo);
        $stmt->bindParam(':id', $this->Id);
        
        return $stmt->execute();
    }

    /**
     * Eliminar sucursal (soft delete - marcar como inactiva)
     */
    public function delete() {
        $query = "UPDATE " . $this->table . " SET Activo = 0 WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->Id);
        return $stmt->execute();
    }

    /**
     * Eliminar permanentemente
     */
    public function deletePermanent() {
        $query = "DELETE FROM " . $this->table . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->Id);
        return $stmt->execute();
    }
}