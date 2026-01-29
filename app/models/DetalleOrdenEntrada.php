<?php
/**
 * Modelo DetalleOrdenEntrada
 * Maneja los detalles de las órdenes de entrada
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/Database.php';

class DetalleOrdenEntrada {
    private $conn;
    private $table = 'detalleordenentrada';

    public $Id;
    public $OrdenEntradaId;
    public $ProductoId;
    public $Cantidad;
    public $PrecioUnitario;
    public $SubTotal;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener detalles por orden de entrada
     */
    public function getByOrdenEntrada($ordenEntradaId) {
        $query = "SELECT doe.*, 
                         p.Codigo, p.Nombre as ProductoNombre, p.Marca, p.ImagenUrl
                  FROM " . $this->table . " doe
                  INNER JOIN producto p ON doe.ProductoId = p.Id
                  WHERE doe.OrdenEntradaId = :orden_entrada_id
                  ORDER BY p.Nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':orden_entrada_id', $ordenEntradaId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener detalle específico por ID
     */
    public function getById($id) {
        $query = "SELECT doe.*, 
                         p.Codigo, p.Nombre as ProductoNombre, p.Marca
                  FROM " . $this->table . " doe
                  INNER JOIN producto p ON doe.ProductoId = p.Id
                  WHERE doe.Id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Crear nuevo detalle
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (OrdenEntradaId, ProductoId, Cantidad, PrecioUnitario, SubTotal) 
                  VALUES (:orden_entrada_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
        
        $stmt = $this->conn->prepare($query);
        
        // Calcular subtotal
        $this->SubTotal = $this->Cantidad * $this->PrecioUnitario;
        
        $stmt->bindParam(':orden_entrada_id', $this->OrdenEntradaId);
        $stmt->bindParam(':producto_id', $this->ProductoId);
        $stmt->bindParam(':cantidad', $this->Cantidad);
        $stmt->bindParam(':precio_unitario', $this->PrecioUnitario);
        $stmt->bindParam(':subtotal', $this->SubTotal);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Actualizar detalle
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET ProductoId = :producto_id,
                      Cantidad = :cantidad,
                      PrecioUnitario = :precio_unitario,
                      SubTotal = :subtotal
                  WHERE Id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Calcular subtotal
        $this->SubTotal = $this->Cantidad * $this->PrecioUnitario;
        
        $stmt->bindParam(':producto_id', $this->ProductoId);
        $stmt->bindParam(':cantidad', $this->Cantidad);
        $stmt->bindParam(':precio_unitario', $this->PrecioUnitario);
        $stmt->bindParam(':subtotal', $this->SubTotal);
        $stmt->bindParam(':id', $this->Id);
        
        return $stmt->execute();
    }

    /**
     * Eliminar detalle
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->Id);
        return $stmt->execute();
    }

    /**
     * Eliminar todos los detalles de una orden
     */
    public function deleteByOrdenEntrada($ordenEntradaId) {
        $query = "DELETE FROM " . $this->table . " WHERE OrdenEntradaId = :orden_entrada_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':orden_entrada_id', $ordenEntradaId);
        return $stmt->execute();
    }

    /**
     * Calcular total de la orden
     */
    public function calculateTotalOrden($ordenEntradaId) {
        $query = "SELECT SUM(SubTotal) as Total 
                  FROM " . $this->table . " 
                  WHERE OrdenEntradaId = :orden_entrada_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':orden_entrada_id', $ordenEntradaId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ? $result['Total'] : 0;
    }

    /**
     * Verificar si producto ya existe en la orden
     */
    public function productoExistsInOrden($ordenEntradaId, $productoId, $excludeId = null) {
        $query = "SELECT COUNT(*) as count 
                  FROM " . $this->table . " 
                  WHERE OrdenEntradaId = :orden_entrada_id 
                    AND ProductoId = :producto_id";
        
        if ($excludeId) {
            $query .= " AND Id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':orden_entrada_id', $ordenEntradaId);
        $stmt->bindParam(':producto_id', $productoId);
        
        if ($excludeId) {
            $stmt->bindParam(':id', $excludeId);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}