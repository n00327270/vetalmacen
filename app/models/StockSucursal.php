<?php
require_once __DIR__ . '/Database.php';

class StockSucursal {
    private $conn;
    private $table = 'stock_sucursal';

    public $ProductoId;
    public $SucursalId;
    public $Stock;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todo el stock con información de producto y sucursal
     */
    public function getAll() {
        $query = "SELECT ss.*, 
                         p.Codigo, p.Nombre as ProductoNombre, p.Marca, p.Precio, p.ImagenUrl, p.BlobName,
                         s.Sede as SucursalNombre, s.Direccion,
                         sc.Nombre as SubCategoriaNombre,
                         c.Nombre as CategoriaNombre
                  FROM " . $this->table . " ss
                  INNER JOIN producto p ON ss.ProductoId = p.Id
                  INNER JOIN sucursal s ON ss.SucursalId = s.Id
                  INNER JOIN subcategoria sc ON p.SubCategoriaId = sc.Id
                  INNER JOIN categoria c ON sc.CategoriaId = c.Id
                  WHERE s.Activo = 1
                  ORDER BY s.Sede, p.Nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener stock por sucursal
     */
    public function getBySucursal($sucursalId) {
        $query = "SELECT ss.*, 
                         p.Codigo, p.Nombre as ProductoNombre, p.Marca, p.Precio, p.ImagenUrl,
                         sc.Nombre as SubCategoriaNombre,
                         c.Nombre as CategoriaNombre
                  FROM " . $this->table . " ss
                  INNER JOIN producto p ON ss.ProductoId = p.Id
                  INNER JOIN subcategoria sc ON p.SubCategoriaId = sc.Id
                  INNER JOIN categoria c ON sc.CategoriaId = c.Id
                  WHERE ss.SucursalId = :sucursal_id
                  ORDER BY p.Nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sucursal_id', $sucursalId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener stock por producto
     */
    public function getByProducto($productoId) {
        $query = "SELECT ss.*, 
                         s.Sede as SucursalNombre, s.Direccion
                  FROM " . $this->table . " ss
                  INNER JOIN sucursal s ON ss.SucursalId = s.Id
                  WHERE ss.ProductoId = :producto_id
                  ORDER BY s.Sede ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':producto_id', $productoId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener stock específico de un producto en una sucursal
     */
    public function getStock($productoId, $sucursalId) {
        $query = "SELECT Stock FROM " . $this->table . " 
                  WHERE ProductoId = :producto_id AND SucursalId = :sucursal_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':producto_id', $productoId);
        $stmt->bindParam(':sucursal_id', $sucursalId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ? $result['Stock'] : 0;
    }

    /**
     * Obtener productos con stock bajo (menos de X unidades)
     */
    public function getStockBajo($limite = 10, $sucursalId = null) {
        $query = "SELECT ss.*, 
                         p.Codigo, p.Nombre as ProductoNombre, p.Marca,
                         s.Sede as SucursalNombre
                  FROM " . $this->table . " ss
                  INNER JOIN producto p ON ss.ProductoId = p.Id
                  INNER JOIN sucursal s ON ss.SucursalId = s.Id
                  WHERE ss.Stock <= :limite";
        
        if ($sucursalId) {
            $query .= " AND ss.SucursalId = :sucursal_id";
        }
        
        $query .= " ORDER BY ss.Stock ASC, p.Nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limite', $limite);
        
        if ($sucursalId) {
            $stmt->bindParam(':sucursal_id', $sucursalId);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crear o actualizar stock (usado manualmente si es necesario)
     * Nota: Los triggers se encargan automáticamente del stock
     */
    public function createOrUpdate() {
        $query = "INSERT INTO " . $this->table . " 
                  (ProductoId, SucursalId, Stock) 
                  VALUES (:producto_id, :sucursal_id, :stock)
                  ON DUPLICATE KEY UPDATE Stock = :stock";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':producto_id', $this->ProductoId);
        $stmt->bindParam(':sucursal_id', $this->SucursalId);
        $stmt->bindParam(':stock', $this->Stock);
        
        return $stmt->execute();
    }

    /**
     * Obtener stock total de un producto (suma de todas las sucursales)
     */
    public function getStockTotal($productoId) {
        $query = "SELECT SUM(Stock) as StockTotal 
                  FROM " . $this->table . " 
                  WHERE ProductoId = :producto_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':producto_id', $productoId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ? $result['StockTotal'] : 0;
    }

    /**
     * Inicializar stock en 0 para un producto en todas las sucursales activas
     */
    public function initializeStockForProduct($productoId) {
        $query = "INSERT INTO " . $this->table . " (ProductoId, SucursalId, Stock)
                  SELECT :producto_id, Id, 0
                  FROM sucursal
                  WHERE Activo = 1
                  ON DUPLICATE KEY UPDATE Stock = Stock";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':producto_id', $productoId);
        
        return $stmt->execute();
    }
}