<?php
require_once __DIR__ . '/Database.php';

class MasterTable {
    private $conn;
    private $table = 'mastertable';

    public $IdMasterTable;
    public $IdMasterTableParent;
    public $Value;
    public $Description;
    public $Name;
    public $Order;
    public $AdditionalOne;
    public $AdditionalTwo;
    public $AdditionalThree;
    public $UserNew;
    public $DateNew;
    public $UserEdit;
    public $DateEdit;
    public $States;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los padres (registros sin parent)
     * ORDENADOS POR ID (100, 200, 300...)
     */
    public function getAllPadres() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE IdMasterTableParent IS NULL 
                  ORDER BY IdMasterTable ASC";  // ← CAMBIO: Orden por ID
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener hijos de un padre específico
     */
    public function getHijosByPadreId($padreId) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE IdMasterTableParent = :padre_id 
                  ORDER BY `Order` ASC, Name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':padre_id', $padreId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener registro por ID
     */
    public function getById($id) {
        $query = "SELECT mt.*, 
                         parent.Name as ParentName
                  FROM " . $this->table . " mt
                  LEFT JOIN " . $this->table . " parent ON mt.IdMasterTableParent = parent.IdMasterTable
                  WHERE mt.IdMasterTable = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener árbol completo (padres con sus hijos)
     */
    public function getArbolCompleto() {
        $padres = $this->getAllPadres();
        $arbol = [];
        
        foreach ($padres as $padre) {
            $padre['hijos'] = $this->getHijosByPadreId($padre['IdMasterTable']);
            $arbol[] = $padre;
        }
        
        return $arbol;
    }

    /**
     * Obtener siguiente ID para PADRE (múltiplos de 100)
     */
    public function getNextIdPadre() {
        $query = "SELECT MAX(IdMasterTable) as max_id 
                  FROM " . $this->table . " 
                  WHERE IdMasterTableParent IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        $maxId = $result['max_id'] ?? 0;
        
        // Si no hay padres, empieza en 100
        if ($maxId == 0) {
            return 100;
        }
        
        // Calcular siguiente múltiplo de 100
        $nextId = (floor($maxId / 100) + 1) * 100;
        
        return $nextId;
    }

    /**
     * Obtener siguiente ID para HIJO
     */
    public function getNextIdHijo() {
        $query = "SELECT MAX(IdMasterTable) as max_id FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return ($result['max_id'] ?? 0) + 1;
    }

    /**
     * Crear nuevo registro
     */
    public function create() {
        // Convertir strings vacíos a NULL
        $this->Value = $this->emptyToNull($this->Value);
        $this->Description = $this->emptyToNull($this->Description);
        $this->AdditionalOne = $this->emptyToNull($this->AdditionalOne);
        $this->AdditionalTwo = $this->emptyToNull($this->AdditionalTwo);
        $this->AdditionalThree = $this->emptyToNull($this->AdditionalThree);
        
        // Generar ID manual según tipo
        if ($this->IdMasterTableParent === null) {
            // Es padre: ID en múltiplos de 100
            $this->IdMasterTable = $this->getNextIdPadre();
        } else {
            // Es hijo: ID consecutivo
            $this->IdMasterTable = $this->getNextIdHijo();
        }
        
        $query = "INSERT INTO " . $this->table . " 
                  (IdMasterTable, IdMasterTableParent, Value, Description, Name, `Order`, 
                   AdditionalOne, AdditionalTwo, AdditionalThree, 
                   UserNew, DateNew, States) 
                  VALUES (:id, :parent_id, :value, :description, :name, :order, 
                          :add1, :add2, :add3, :user_new, NOW(), :states)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar
        $this->Name = htmlspecialchars(strip_tags($this->Name));
        if ($this->Value !== null) {
            $this->Value = htmlspecialchars(strip_tags($this->Value));
        }
        if ($this->Description !== null) {
            $this->Description = htmlspecialchars(strip_tags($this->Description));
        }
        
        // Bind
        $stmt->bindParam(':id', $this->IdMasterTable);
        $stmt->bindParam(':parent_id', $this->IdMasterTableParent);
        $stmt->bindParam(':value', $this->Value);
        $stmt->bindParam(':description', $this->Description);
        $stmt->bindParam(':name', $this->Name);
        $stmt->bindParam(':order', $this->Order);
        $stmt->bindParam(':add1', $this->AdditionalOne);
        $stmt->bindParam(':add2', $this->AdditionalTwo);
        $stmt->bindParam(':add3', $this->AdditionalThree);
        $stmt->bindParam(':user_new', $this->UserNew);
        $stmt->bindParam(':states', $this->States);
        
        if ($stmt->execute()) {
            return $this->IdMasterTable;
        }
        return false;
    }

    /**
     * Actualizar registro
     */
    public function update() {
        // Convertir strings vacíos a NULL
        $this->Value = $this->emptyToNull($this->Value);
        $this->Description = $this->emptyToNull($this->Description);
        $this->AdditionalOne = $this->emptyToNull($this->AdditionalOne);
        $this->AdditionalTwo = $this->emptyToNull($this->AdditionalTwo);
        $this->AdditionalThree = $this->emptyToNull($this->AdditionalThree);
        
        $query = "UPDATE " . $this->table . " 
                  SET Value = :value,
                      Description = :description,
                      Name = :name,
                      `Order` = :order,
                      AdditionalOne = :add1,
                      AdditionalTwo = :add2,
                      AdditionalThree = :add3,
                      UserEdit = :user_edit,
                      DateEdit = NOW(),
                      States = :states
                  WHERE IdMasterTable = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar
        $this->Name = htmlspecialchars(strip_tags($this->Name));
        if ($this->Value !== null) {
            $this->Value = htmlspecialchars(strip_tags($this->Value));
        }
        if ($this->Description !== null) {
            $this->Description = htmlspecialchars(strip_tags($this->Description));
        }
        
        // Bind
        $stmt->bindParam(':value', $this->Value);
        $stmt->bindParam(':description', $this->Description);
        $stmt->bindParam(':name', $this->Name);
        $stmt->bindParam(':order', $this->Order);
        $stmt->bindParam(':add1', $this->AdditionalOne);
        $stmt->bindParam(':add2', $this->AdditionalTwo);
        $stmt->bindParam(':add3', $this->AdditionalThree);
        $stmt->bindParam(':user_edit', $this->UserEdit);
        $stmt->bindParam(':states', $this->States);
        $stmt->bindParam(':id', $this->IdMasterTable);
        
        return $stmt->execute();
    }

    /**
     * Eliminar registro (soft delete - cambiar estado)
     */
    public function delete() {
        $query = "UPDATE " . $this->table . " 
                  SET States = 0,
                      UserEdit = :user_edit,
                      DateEdit = NOW()
                  WHERE IdMasterTable = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_edit', $this->UserEdit);
        $stmt->bindParam(':id', $this->IdMasterTable);
        
        return $stmt->execute();
    }

    /**
     * Verificar si un padre tiene hijos activos
     */
    public function tieneHijosActivos($id) {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table . " 
                  WHERE IdMasterTableParent = :id 
                    AND States = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }

    /**
     * Verificar si está en uso en tabla proveedor
     */
    public function estaEnUso($id) {
        $query = "SELECT COUNT(*) as total 
                  FROM proveedor 
                  WHERE DenominacionId = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }

    /**
     * Obtener siguiente número de orden
     */
    public function getNextOrder($parentId) {
        $query = "SELECT COALESCE(MAX(`Order`), 0) + 1 as next_order 
                  FROM " . $this->table . " 
                  WHERE IdMasterTableParent " . ($parentId ? "= :parent_id" : "IS NULL");
        
        $stmt = $this->conn->prepare($query);
        if ($parentId) {
            $stmt->bindParam(':parent_id', $parentId);
        }
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['next_order'];
    }

    /**
     * Convertir string vacío a NULL
     */
    private function emptyToNull($value) {
        // Si es string vacío o solo espacios, retornar NULL
        if (is_string($value) && trim($value) === '') {
            return null;
        }
        return $value;
    }
}