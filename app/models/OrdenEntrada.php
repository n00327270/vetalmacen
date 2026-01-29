<?php
/**
 * Modelo OrdenEntrada
 * Maneja las órdenes de entrada de mercancía
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/Database.php';

class OrdenEntrada {
    private $conn;
    private $table = 'ordenentrada';

    public $Id;
    public $ProveedorId;
    public $UsuarioId;
    public $SucursalId;
    public $Fecha;
    public $Estado;
    public $Total;
    public $Observacion;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todas las órdenes de entrada con información completa
     */
    public function getAll() {
        $query = "SELECT oe.*, 
                         p.RazonSocial as ProveedorNombre,
                         u.Username as UsuarioNombre,
                         s.Sede as SucursalNombre
                  FROM " . $this->table . " oe
                  INNER JOIN proveedor p ON oe.ProveedorId = p.Id
                  INNER JOIN usuario u ON oe.UsuarioId = u.Id
                  INNER JOIN sucursal s ON oe.SucursalId = s.Id
                  ORDER BY oe.Fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener orden por ID
     */
    public function getById($id) {
        $query = "SELECT oe.*, 
                         p.RazonSocial as ProveedorNombre, p.RUC, p.Telefono as ProveedorTelefono,
                         u.Username as UsuarioNombre,
                         s.Sede as SucursalNombre, s.Direccion as SucursalDireccion
                  FROM " . $this->table . " oe
                  INNER JOIN proveedor p ON oe.ProveedorId = p.Id
                  INNER JOIN usuario u ON oe.UsuarioId = u.Id
                  INNER JOIN sucursal s ON oe.SucursalId = s.Id
                  WHERE oe.Id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener órdenes por sucursal
     */
    public function getBySucursal($sucursalId, $limit = null) {
        $query = "SELECT oe.*, 
                         p.RazonSocial as ProveedorNombre,
                         u.Username as UsuarioNombre
                  FROM " . $this->table . " oe
                  INNER JOIN proveedor p ON oe.ProveedorId = p.Id
                  INNER JOIN usuario u ON oe.UsuarioId = u.Id
                  WHERE oe.SucursalId = :sucursal_id
                  ORDER BY oe.Fecha DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sucursal_id', $sucursalId);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener órdenes por estado
     */
    public function getByEstado($estado) {
        $query = "SELECT oe.*, 
                         p.RazonSocial as ProveedorNombre,
                         u.Username as UsuarioNombre,
                         s.Sede as SucursalNombre
                  FROM " . $this->table . " oe
                  INNER JOIN proveedor p ON oe.ProveedorId = p.Id
                  INNER JOIN usuario u ON oe.UsuarioId = u.Id
                  INNER JOIN sucursal s ON oe.SucursalId = s.Id
                  WHERE oe.Estado = :estado
                  ORDER BY oe.Fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crear nueva orden de entrada
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (ProveedorId, UsuarioId, SucursalId, Estado, Total, Observacion) 
                  VALUES (:proveedor_id, :usuario_id, :sucursal_id, :estado, :total, :observacion)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Observacion = htmlspecialchars(strip_tags($this->Observacion));
        
        $stmt->bindParam(':proveedor_id', $this->ProveedorId);
        $stmt->bindParam(':usuario_id', $this->UsuarioId);
        $stmt->bindParam(':sucursal_id', $this->SucursalId);
        $stmt->bindParam(':estado', $this->Estado);
        $stmt->bindParam(':total', $this->Total);
        $stmt->bindParam(':observacion', $this->Observacion);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Actualizar orden de entrada
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET ProveedorId = :proveedor_id,
                      SucursalId = :sucursal_id,
                      Estado = :estado,
                      Total = :total,
                      Observacion = :observacion
                  WHERE Id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Observacion = htmlspecialchars(strip_tags($this->Observacion));
        
        $stmt->bindParam(':proveedor_id', $this->ProveedorId);
        $stmt->bindParam(':sucursal_id', $this->SucursalId);
        $stmt->bindParam(':estado', $this->Estado);
        $stmt->bindParam(':total', $this->Total);
        $stmt->bindParam(':observacion', $this->Observacion);
        $stmt->bindParam(':id', $this->Id);
        
        return $stmt->execute();
    }

    /**
     * Actualizar estado de la orden
     */
    public function updateEstado($estado) {
        $query = "UPDATE " . $this->table . " SET Estado = :estado WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $this->Id);
        return $stmt->execute();
    }

    /**
     * Actualizar total de la orden
     */
    public function updateTotal($total) {
        $query = "UPDATE " . $this->table . " SET Total = :total WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':id', $this->Id);
        return $stmt->execute();
    }

    /**
     * Eliminar orden de entrada
     */
    public function delete() {
        // Solo se puede eliminar si está en estado Pendiente o Cancelado
        $query = "DELETE FROM " . $this->table . " 
                  WHERE Id = :id AND Estado IN ('Pendiente', 'Cancelado')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->Id);
        return $stmt->execute();
    }

    /**
     * Obtener órdenes por rango de fechas
     */
    public function getByDateRange($fechaInicio, $fechaFin) {
        $query = "SELECT oe.*, 
                         p.RazonSocial as ProveedorNombre,
                         u.Username as UsuarioNombre,
                         s.Sede as SucursalNombre
                  FROM " . $this->table . " oe
                  INNER JOIN proveedor p ON oe.ProveedorId = p.Id
                  INNER JOIN usuario u ON oe.UsuarioId = u.Id
                  INNER JOIN sucursal s ON oe.SucursalId = s.Id
                  WHERE DATE(oe.Fecha) BETWEEN :fecha_inicio AND :fecha_fin
                  ORDER BY oe.Fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fecha_inicio', $fechaInicio);
        $stmt->bindParam(':fecha_fin', $fechaFin);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener total de entradas por mes
     */
    public function getTotalPorMes($year, $month) {
        $query = "SELECT SUM(Total) as TotalMes
                  FROM " . $this->table . "
                  WHERE YEAR(Fecha) = :year 
                    AND MONTH(Fecha) = :month
                    AND Estado = 'Recibido'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ? $result['TotalMes'] : 0;
    }
}