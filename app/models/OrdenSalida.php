<?php
/**
 * Modelo OrdenSalida
 * Maneja las órdenes de salida de mercancía
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/Database.php';

class OrdenSalida {
    private $conn;
    private $table = 'ordensalida';

    public $Id;
    public $UsuarioId;
    public $SucursalId;
    public $Fecha;
    public $TipoSalida;
    public $Estado;
    public $Total;
    public $Observacion;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todas las órdenes de salida
     */
    public function getAll() {
        $query = "SELECT os.*, 
                         u.Username as UsuarioNombre,
                         s.Sede as SucursalNombre
                  FROM " . $this->table . " os
                  INNER JOIN usuario u ON os.UsuarioId = u.Id
                  LEFT JOIN sucursal s ON os.SucursalId = s.Id
                  ORDER BY os.Fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener orden por ID
     */
    public function getById($id) {
        $query = "SELECT os.*, 
                         u.Username as UsuarioNombre,
                         s.Sede as SucursalNombre, s.Direccion as SucursalDireccion
                  FROM " . $this->table . " os
                  INNER JOIN usuario u ON os.UsuarioId = u.Id
                  LEFT JOIN sucursal s ON os.SucursalId = s.Id
                  WHERE os.Id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener órdenes por sucursal
     */
    public function getBySucursal($sucursalId, $limit = null) {
        $query = "SELECT os.*, 
                         u.Username as UsuarioNombre
                  FROM " . $this->table . " os
                  INNER JOIN usuario u ON os.UsuarioId = u.Id
                  WHERE os.SucursalId = :sucursal_id
                  ORDER BY os.Fecha DESC";
        
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
        $query = "SELECT os.*, 
                         u.Username as UsuarioNombre,
                         s.Sede as SucursalNombre
                  FROM " . $this->table . " os
                  INNER JOIN usuario u ON os.UsuarioId = u.Id
                  LEFT JOIN sucursal s ON os.SucursalId = s.Id
                  WHERE os.Estado = :estado
                  ORDER BY os.Fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener órdenes por tipo de salida
     */
    public function getByTipo($tipoSalida) {
        $query = "SELECT os.*, 
                         u.Username as UsuarioNombre,
                         s.Sede as SucursalNombre
                  FROM " . $this->table . " os
                  INNER JOIN usuario u ON os.UsuarioId = u.Id
                  LEFT JOIN sucursal s ON os.SucursalId = s.Id
                  WHERE os.TipoSalida = :tipo_salida
                  ORDER BY os.Fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipo_salida', $tipoSalida);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crear nueva orden de salida
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (UsuarioId, SucursalId, TipoSalida, Estado, Total, Observacion) 
                  VALUES (:usuario_id, :sucursal_id, :tipo_salida, :estado, :total, :observacion)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Observacion = htmlspecialchars(strip_tags($this->Observacion));
        
        $stmt->bindParam(':usuario_id', $this->UsuarioId);
        $stmt->bindParam(':sucursal_id', $this->SucursalId);
        $stmt->bindParam(':tipo_salida', $this->TipoSalida);
        $stmt->bindParam(':estado', $this->Estado);
        $stmt->bindParam(':total', $this->Total);
        $stmt->bindParam(':observacion', $this->Observacion);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Actualizar orden de salida
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET SucursalId = :sucursal_id,
                      TipoSalida = :tipo_salida,
                      Estado = :estado,
                      Total = :total,
                      Observacion = :observacion
                  WHERE Id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Observacion = htmlspecialchars(strip_tags($this->Observacion));
        
        $stmt->bindParam(':sucursal_id', $this->SucursalId);
        $stmt->bindParam(':tipo_salida', $this->TipoSalida);
        $stmt->bindParam(':estado', $this->Estado);
        $stmt->bindParam(':total', $this->Total);
        $stmt->bindParam(':observacion', $this->Observacion);
        $stmt->bindParam(':id', $this->Id);
        
        return $stmt->execute();
    }

    /**
     * Actualizar estado
     */
    public function updateEstado($estado) {
        $query = "UPDATE " . $this->table . " SET Estado = :estado WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $this->Id);
        return $stmt->execute();
    }

    /**
     * Actualizar total
     */
    public function updateTotal($total) {
        $query = "UPDATE " . $this->table . " SET Total = :total WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':id', $this->Id);
        return $stmt->execute();
    }

    /**
     * Eliminar orden de salida
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
        $query = "SELECT os.*, 
                         u.Username as UsuarioNombre,
                         s.Sede as SucursalNombre
                  FROM " . $this->table . " os
                  INNER JOIN usuario u ON os.UsuarioId = u.Id
                  LEFT JOIN sucursal s ON os.SucursalId = s.Id
                  WHERE DATE(os.Fecha) BETWEEN :fecha_inicio AND :fecha_fin
                  ORDER BY os.Fecha DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fecha_inicio', $fechaInicio);
        $stmt->bindParam(':fecha_fin', $fechaFin);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener total de ventas por mes
     */
    public function getTotalVentasPorMes($year, $month) {
        $query = "SELECT SUM(Total) as TotalMes
                  FROM " . $this->table . "
                  WHERE YEAR(Fecha) = :year 
                    AND MONTH(Fecha) = :month
                    AND TipoSalida = 'Venta'
                    AND Estado = 'Procesado'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->bindParam(':month', $month);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ? $result['TotalMes'] : 0;
    }
}