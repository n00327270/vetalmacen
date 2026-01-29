<?php
/**
 * Modelo DetalleOrdenSalida
 * Maneja los detalles de las órdenes de salida
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/Database.php';

class DetalleOrdenSalida {
    private $conn;
    private $table = 'detalleordensalida';

    public $Id;
    public $OrdenSalidaId;
    public $ProductoId;
    public $Cantidad;
    public $PrecioUnitario;
    public $SubTotal;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener detalles por orden de salida
     */
    public function getByOrdenSalida($ordenSalidaId) {
        $query = "SELECT dos.*, 
                         p.Codigo, p.Nombre as ProductoNombre, p.Marca, p.ImagenUrl
                  FROM " . $this->table . " dos
                  INNER JOIN producto p ON dos.ProductoId = p.Id
                  WHERE dos.OrdenSalidaId = :orden_salida_id
                  ORDER BY p.Nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':orden_salida_id', $ordenSalidaId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener detalle específico por ID
     */
    public function getById($id) {
        $query = "SELECT dos.*, 
                         p.Codigo, p.Nombre as ProductoNombre, p.Marca
                  FROM " . $this->table . " dos
                  INNER JOIN producto p ON dos.ProductoId = p.Id
                  WHERE dos.Id = :id";
        
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
                  (OrdenSalidaId, ProductoId, Cantidad, PrecioUnitario, SubTotal) 
                  VALUES (:orden_salida_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
        
        $stmt = $this->conn->prepare($query);
        
        // Calcular subtotal
        $this->SubTotal = $this->Cantidad * $this->PrecioUnitario;
        
        $stmt->bindParam(':orden_salida_id', $this->OrdenSalidaId);
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
    public function deleteByOrdenSalida($ordenSalidaId) {
        $query = "DELETE FROM " . $this->table . " WHERE OrdenSalidaId = :orden_salida_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':orden_salida_id', $ordenSalidaId);
        return $stmt->execute();
    }

    /**
     * Calcular total de la orden
     */
    public function calculateTotalOrden($ordenSalidaId) {
        $query = "SELECT SUM(SubTotal) as Total 
                  FROM " . $this->table . " 
                  WHERE OrdenSalidaId = :orden_salida_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':orden_salida_id', $ordenSalidaId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ? $result['Total'] : 0;
    }

    /**
     * Verificar si producto ya existe en la orden
     */
    public function productoExistsInOrden($ordenSalidaId, $productoId, $excludeId = null) {
        $query = "SELECT COUNT(*) as count 
                  FROM " . $this->table . " 
                  WHERE OrdenSalidaId = :orden_salida_id 
                    AND ProductoId = :producto_id";
        
        if ($excludeId) {
            $query .= " AND Id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':orden_salida_id', $ordenSalidaId);
        $stmt->bindParam(':producto_id', $productoId);
        
        if ($excludeId) {
            $stmt->bindParam(':id', $excludeId);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }

    /**
     * Obtener productos más vendidos
     */
    public function getProductosMasVendidos($limit = 10) {
        $query = "SELECT p.Id, p.Codigo, p.Nombre, p.Marca, p.ImagenUrl,
                         SUM(dos.Cantidad) as CantidadVendida,
                         SUM(dos.SubTotal) as TotalVendido
                  FROM " . $this->table . " dos
                  INNER JOIN producto p ON dos.ProductoId = p.Id
                  INNER JOIN ordensalida os ON dos.OrdenSalidaId = os.Id
                  WHERE os.TipoSalida = 'Venta' AND os.Estado = 'Procesado'
                  GROUP BY p.Id
                  ORDER BY CantidadVendida DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}