<?php
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Subcategoria.php';
require_once __DIR__ . '/../models/StockSucursal.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';
require_once __DIR__ . '/../../helpers/ValidationHelper.php';

class ProductoController {
    
    /**
     * Listar todos los productos
     */
    public function index() {
        AuthHelper::requireAuth();
        
        $productoModel = new Producto();
        $productos = $productoModel->getAll();
        
        require_once __DIR__ . '/../views/productos/index.php';
    }

    /**
     * Mostrar formulario de creación
     */
    public function crear() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        $categoriaModel = new Categoria();
        $subcategoriaModel = new Subcategoria();
        
        $categorias = $categoriaModel->getAll();
        $subcategorias = $subcategoriaModel->getAll();
        
        require_once __DIR__ . '/../views/productos/crear.php';
    }

    /**
     * Guardar nuevo producto
     */
    public function guardar() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=productos');
            exit();
        }

        // Obtener datos del formulario
        $codigo = $_POST['codigo'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $marca = $_POST['marca'] ?? '';
        $subcategoriaId = $_POST['subcategoria_id'] ?? '';
        $precio = $_POST['precio'] ?? 0;

        // Validaciones
        $errores = [];

        if (!ValidationHelper::required($codigo)) {
            $errores[] = 'El código es obligatorio';
        }

        if (!ValidationHelper::required($nombre)) {
            $errores[] = 'El nombre es obligatorio';
        }

        if (!ValidationHelper::required($descripcion)) {
            $errores[] = 'La descripción es obligatoria';
        }

        if (!ValidationHelper::required($marca)) {
            $errores[] = 'La marca es obligatoria';
        }

        if (!ValidationHelper::positiveInteger($subcategoriaId)) {
            $errores[] = 'Debe seleccionar una subcategoría válida';
        }

        if (!ValidationHelper::positiveDecimal($precio)) {
            $errores[] = 'El precio debe ser mayor o igual a 0';
        }

        // Verificar si el código ya existe
        $productoModel = new Producto();
        if ($productoModel->codigoExists($codigo)) {
            $errores[] = 'El código ya existe';
        }

        if (!empty($errores)) {
            SessionHelper::setFlash('danger', implode('<br>', $errores));
            header('Location: /vetalmacen/public/index.php?url=productos/crear');
            exit();
        }

        // Preparar datos del producto
        $productoModel->Codigo = $codigo;
        $productoModel->Nombre = $nombre;
        $productoModel->Descripcion = $descripcion;
        $productoModel->Marca = $marca;
        $productoModel->SubCategoriaId = $subcategoriaId;
        $productoModel->Precio = $precio;

        // Manejar imagen si existe
        $imageFile = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imageFile = $_FILES['imagen'];
        }

        // Crear producto
        $result = $productoModel->create($imageFile);

        if ($result['success']) {
            // Inicializar stock en 0 para todas las sucursales activas
            $stockModel = new StockSucursal();
            $stockModel->initializeStockForProduct($result['id']);
            
            SessionHelper::setFlash('success', 'Producto creado exitosamente');
            header('Location: /vetalmacen/public/index.php?url=productos');
        } else {
            SessionHelper::setFlash('danger', $result['error']);
            header('Location: /vetalmacen/public/index.php?url=productos/crear');
        }
        exit();
    }

    /**
     * Mostrar formulario de edición
     */
    public function editar($id) {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        $productoModel = new Producto();
        $producto = $productoModel->getById($id);
        
        if (!$producto) {
            SessionHelper::setFlash('danger', 'Producto no encontrado');
            header('Location: /vetalmacen/public/index.php?url=productos');
            exit();
        }

        $categoriaModel = new Categoria();
        $subcategoriaModel = new Subcategoria();
        
        $categorias = $categoriaModel->getAll();
        $subcategorias = $subcategoriaModel->getAll();
        
        require_once __DIR__ . '/../views/productos/editar.php';
    }

    /**
     * Actualizar producto
     */
    public function actualizar() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=productos');
            exit();
        }

        $id = $_POST['id'] ?? '';
        $codigo = $_POST['codigo'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $marca = $_POST['marca'] ?? '';
        $subcategoriaId = $_POST['subcategoria_id'] ?? '';
        $precio = $_POST['precio'] ?? 0;

        // Validaciones
        $errores = [];

        if (!ValidationHelper::positiveInteger($id)) {
            $errores[] = 'ID de producto inválido';
        }

        if (!ValidationHelper::required($codigo)) {
            $errores[] = 'El código es obligatorio';
        }

        if (!ValidationHelper::required($nombre)) {
            $errores[] = 'El nombre es obligatorio';
        }

        if (!ValidationHelper::required($descripcion)) {
            $errores[] = 'La descripción es obligatoria';
        }

        if (!ValidationHelper::required($marca)) {
            $errores[] = 'La marca es obligatoria';
        }

        if (!ValidationHelper::positiveInteger($subcategoriaId)) {
            $errores[] = 'Debe seleccionar una subcategoría válida';
        }

        if (!ValidationHelper::positiveDecimal($precio)) {
            $errores[] = 'El precio debe ser mayor o igual a 0';
        }

        // Verificar si el código ya existe (excluyendo el producto actual)
        $productoModel = new Producto();
        if ($productoModel->codigoExists($codigo, $id)) {
            $errores[] = 'El código ya existe';
        }

        if (!empty($errores)) {
            SessionHelper::setFlash('danger', implode('<br>', $errores));
            header('Location: /vetalmacen/public/index.php?url=productos/editar/' . $id);
            exit();
        }

        // Preparar datos
        $productoModel->Id = $id;
        $productoModel->Codigo = $codigo;
        $productoModel->Nombre = $nombre;
        $productoModel->Descripcion = $descripcion;
        $productoModel->Marca = $marca;
        $productoModel->SubCategoriaId = $subcategoriaId;
        $productoModel->Precio = $precio;

        // Manejar imagen si existe
        $imageFile = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imageFile = $_FILES['imagen'];
        }

        // Actualizar producto
        $result = $productoModel->update($imageFile);

        if ($result['success']) {
            SessionHelper::setFlash('success', 'Producto actualizado exitosamente');
            header('Location: /vetalmacen/public/index.php?url=productos');
        } else {
            SessionHelper::setFlash('danger', $result['error']);
            header('Location: /vetalmacen/public/index.php?url=productos/editar/' . $id);
        }
        exit();
    }

    /**
     * Ver detalle del producto
     */
    public function detalle($id) {
        AuthHelper::requireAuth();
        
        $productoModel = new Producto();
        $producto = $productoModel->getById($id);
        
        if (!$producto) {
            SessionHelper::setFlash('danger', 'Producto no encontrado');
            header('Location: /vetalmacen/public/index.php?url=productos');
            exit();
        }

        // Obtener stock por sucursal
        $stockModel = new StockSucursal();
        $stockPorSucursal = $stockModel->getByProducto($id);
        
        require_once __DIR__ . '/../views/productos/detalle.php';
    }

    /**
     * Eliminar producto
     */
    public function eliminar($id) {
        AuthHelper::requireRole('Administrador');
        
        $productoModel = new Producto();
        $productoModel->Id = $id;
        
        $result = $productoModel->delete();
        
        if ($result['success']) {
            SessionHelper::setFlash('success', 'Producto eliminado exitosamente');
        } else {
            SessionHelper::setFlash('danger', $result['error']);
        }
        
        header('Location: /vetalmacen/public/index.php?url=productos');
        exit();
    }

    /**
     * Buscar productos (AJAX)
     */
    public function buscar() {
        AuthHelper::requireAuth();
        
        $searchTerm = $_GET['q'] ?? '';
        
        if (empty($searchTerm)) {
            echo json_encode([]);
            exit();
        }

        $productoModel = new Producto();
        $productos = $productoModel->search($searchTerm);
        
        header('Content-Type: application/json');
        echo json_encode($productos);
        exit();
    }

    /**
     * Obtener subcategorías por categoría (AJAX)
     */
    public function getSubcategorias() {
        AuthHelper::requireAuth();
        
        $categoriaId = $_GET['categoria_id'] ?? '';
        
        if (empty($categoriaId)) {
            echo json_encode([]);
            exit();
        }

        $subcategoriaModel = new Subcategoria();
        $subcategorias = $subcategoriaModel->getByCategoria($categoriaId);
        
        header('Content-Type: application/json');
        echo json_encode($subcategorias);
        exit();
    }
}