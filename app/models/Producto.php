<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../../helpers/AzureBlobHelper.php';
require_once __DIR__ . '/../../helpers/ImageHelper.php';

class Producto {
    private $conn;
    private $table = 'producto';

    public $Id;
    public $Codigo;
    public $Nombre;
    public $Descripcion;
    public $Marca;
    public $SubCategoriaId;
    public $Precio;
    public $ImagenUrl;
    public $BlobName;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los productos con información completa
     */
    public function getAll() {
        $query = "SELECT p.*, 
                         sc.Nombre as SubCategoriaNombre,
                         c.Nombre as CategoriaNombre,
                         c.Id as CategoriaId
                  FROM " . $this->table . " p
                  INNER JOIN subcategoria sc ON p.SubCategoriaId = sc.Id
                  INNER JOIN categoria c ON sc.CategoriaId = c.Id
                  ORDER BY p.Nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener producto por ID
     */
    public function getById($id) {
        $query = "SELECT p.*, 
                         sc.Nombre as SubCategoriaNombre,
                         c.Nombre as CategoriaNombre,
                         c.Id as CategoriaId
                  FROM " . $this->table . " p
                  INNER JOIN subcategoria sc ON p.SubCategoriaId = sc.Id
                  INNER JOIN categoria c ON sc.CategoriaId = c.Id
                  WHERE p.Id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Obtener producto por código
     */
    public function getByCodigo($codigo) {
        $query = "SELECT * FROM " . $this->table . " WHERE Codigo = :codigo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Buscar productos
     */
    public function search($searchTerm) {
        $query = "SELECT p.*, 
                         sc.Nombre as SubCategoriaNombre,
                         c.Nombre as CategoriaNombre
                  FROM " . $this->table . " p
                  INNER JOIN subcategoria sc ON p.SubCategoriaId = sc.Id
                  INNER JOIN categoria c ON sc.CategoriaId = c.Id
                  WHERE p.Nombre LIKE :search 
                     OR p.Codigo LIKE :search 
                     OR p.Marca LIKE :search
                     OR p.Descripcion LIKE :search
                  ORDER BY p.Nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $searchParam = "%{$searchTerm}%";
        $stmt->bindParam(':search', $searchParam);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crear nuevo producto con imagen
     * @param array $imageFile - Archivo de imagen de $_FILES (opcional)
     */
    public function create($imageFile = null) {
        // Procesar imagen si existe
        $imagenUrl = null;
        $blobName = null;

        if ($imageFile && $imageFile['error'] !== UPLOAD_ERR_NO_FILE) {
            // Validar imagen
            $validation = ImageHelper::validateImage($imageFile);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'error' => $validation['error']
                ];
            }

            // Optimizar imagen
            ImageHelper::optimizeImage($imageFile['tmp_name']);

            // Subir a Azure
            $azureHelper = new AzureBlobHelper();
            $blobName = $azureHelper->generateUniqueBlobName($imageFile['name']);
            $uploadResult = $azureHelper->uploadImage($imageFile['tmp_name'], $blobName);

            if (!$uploadResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Error al subir imagen: ' . $uploadResult['error']
                ];
            }

            $imagenUrl = $uploadResult['url'];
        }

        // Insertar producto en BD
        $query = "INSERT INTO " . $this->table . " 
                  (Codigo, Nombre, Descripcion, Marca, SubCategoriaId, Precio, ImagenUrl, BlobName) 
                  VALUES (:codigo, :nombre, :descripcion, :marca, :subcategoria_id, :precio, :imagen_url, :blob_name)";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Codigo = htmlspecialchars(strip_tags($this->Codigo));
        $this->Nombre = htmlspecialchars(strip_tags($this->Nombre));
        $this->Descripcion = htmlspecialchars(strip_tags($this->Descripcion));
        $this->Marca = htmlspecialchars(strip_tags($this->Marca));
        
        $stmt->bindParam(':codigo', $this->Codigo);
        $stmt->bindParam(':nombre', $this->Nombre);
        $stmt->bindParam(':descripcion', $this->Descripcion);
        $stmt->bindParam(':marca', $this->Marca);
        $stmt->bindParam(':subcategoria_id', $this->SubCategoriaId);
        $stmt->bindParam(':precio', $this->Precio);
        $stmt->bindParam(':imagen_url', $imagenUrl);
        $stmt->bindParam(':blob_name', $blobName);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'id' => $this->conn->lastInsertId()
            ];
        } else {
            // Si falla la inserción y se subió imagen, eliminarla de Azure
            if ($blobName) {
                $azureHelper->deleteImage($blobName);
            }
            return [
                'success' => false,
                'error' => 'Error al crear el producto en la base de datos'
            ];
        }
    }

    /**
     * Actualizar producto con posibilidad de cambiar imagen
     */
    public function update($imageFile = null) {
        $oldProduct = $this->getById($this->Id);
        
        if (!$oldProduct) {
            return [
                'success' => false,
                'error' => 'Producto no encontrado'
            ];
        }

        $imagenUrl = $oldProduct['ImagenUrl'];
        $blobName = $oldProduct['BlobName'];
        $oldBlobName = $oldProduct['BlobName'];

        // Si hay nueva imagen
        if ($imageFile && $imageFile['error'] !== UPLOAD_ERR_NO_FILE) {
            // Validar nueva imagen
            $validation = ImageHelper::validateImage($imageFile);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'error' => $validation['error']
                ];
            }

            // Optimizar imagen
            ImageHelper::optimizeImage($imageFile['tmp_name']);

            // Subir nueva imagen a Azure
            $azureHelper = new AzureBlobHelper();
            $blobName = $azureHelper->generateUniqueBlobName($imageFile['name']);
            $uploadResult = $azureHelper->uploadImage($imageFile['tmp_name'], $blobName);

            if (!$uploadResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Error al subir nueva imagen: ' . $uploadResult['error']
                ];
            }

            $imagenUrl = $uploadResult['url'];
        }

        // Actualizar en BD
        $query = "UPDATE " . $this->table . " 
                  SET Codigo = :codigo,
                      Nombre = :nombre,
                      Descripcion = :descripcion,
                      Marca = :marca,
                      SubCategoriaId = :subcategoria_id,
                      Precio = :precio,
                      ImagenUrl = :imagen_url,
                      BlobName = :blob_name
                  WHERE Id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->Codigo = htmlspecialchars(strip_tags($this->Codigo));
        $this->Nombre = htmlspecialchars(strip_tags($this->Nombre));
        $this->Descripcion = htmlspecialchars(strip_tags($this->Descripcion));
        $this->Marca = htmlspecialchars(strip_tags($this->Marca));
        
        $stmt->bindParam(':codigo', $this->Codigo);
        $stmt->bindParam(':nombre', $this->Nombre);
        $stmt->bindParam(':descripcion', $this->Descripcion);
        $stmt->bindParam(':marca', $this->Marca);
        $stmt->bindParam(':subcategoria_id', $this->SubCategoriaId);
        $stmt->bindParam(':precio', $this->Precio);
        $stmt->bindParam(':imagen_url', $imagenUrl);
        $stmt->bindParam(':blob_name', $blobName);
        $stmt->bindParam(':id', $this->Id);
        
        if ($stmt->execute()) {
            // Si se actualizó correctamente y había una imagen antigua diferente, eliminarla de Azure
            if ($oldBlobName && $blobName !== $oldBlobName && $imageFile && $imageFile['error'] !== UPLOAD_ERR_NO_FILE) {
                $azureHelper = new AzureBlobHelper();
                $azureHelper->deleteImage($oldBlobName);
            }
            
            return ['success' => true];
        } else {
            // Si falla la actualización y se subió nueva imagen, eliminarla
            if ($imageFile && $imageFile['error'] !== UPLOAD_ERR_NO_FILE && $blobName !== $oldBlobName) {
                $azureHelper = new AzureBlobHelper();
                $azureHelper->deleteImage($blobName);
            }
            return [
                'success' => false,
                'error' => 'Error al actualizar el producto'
            ];
        }
    }

    /**
     * Eliminar producto y su imagen de Azure
     */
    public function delete() {
        $product = $this->getById($this->Id);
        
        if (!$product) {
            return [
                'success' => false,
                'error' => 'Producto no encontrado'
            ];
        }

        // Eliminar de BD
        $query = "DELETE FROM " . $this->table . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->Id);
        
        if ($stmt->execute()) {
            // Eliminar imagen de Azure si existe
            if ($product['BlobName']) {
                $azureHelper = new AzureBlobHelper();
                $azureHelper->deleteImage($product['BlobName']);
            }
            return ['success' => true];
        } else {
            return [
                'success' => false,
                'error' => 'Error al eliminar el producto'
            ];
        }
    }

    /**
     * Verificar si código ya existe
     */
    public function codigoExists($codigo, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE Codigo = :codigo";
        
        if ($excludeId) {
            $query .= " AND Id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        
        if ($excludeId) {
            $stmt->bindParam(':id', $excludeId);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}