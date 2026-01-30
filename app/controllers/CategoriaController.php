<?php
/**
 * CategoriaController
 * Maneja CRUD de categorías
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';
require_once __DIR__ . '/../../helpers/ValidationHelper.php';

class CategoriaController {
    
    /**
     * Listar categorías
     */
    public function index() {
        AuthHelper::requireAuth();
        
        $categoriaModel = new Categoria();
        $categorias = $categoriaModel->getAllWithSubcategoriaCount();
        
        require_once __DIR__ . '/../views/categorias/index.php';
    }

    /**
     * Mostrar formulario de creación
     */
    public function crear() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        require_once __DIR__ . '/../views/categorias/crear.php';
    }

    /**
     * Guardar categoría
     */
    public function guardar() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=categorias');
            exit();
        }

        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        // Validaciones
        if (!ValidationHelper::required($nombre)) {
            SessionHelper::setFlash('danger', 'El nombre es obligatorio');
            header('Location: /vetalmacen/public/index.php?url=categorias/crear');
            exit();
        }

        // Verificar si ya existe
        $categoriaModel = new Categoria();
        if ($categoriaModel->nombreExists($nombre)) {
            SessionHelper::setFlash('danger', 'El nombre de categoría ya existe');
            header('Location: /vetalmacen/public/index.php?url=categorias/crear');
            exit();
        }

        $categoriaModel->Nombre = $nombre;
        $categoriaModel->Descripcion = $descripcion;

        if ($categoriaModel->create()) {
            SessionHelper::setFlash('success', 'Categoría creada exitosamente');
            header('Location: /vetalmacen/public/index.php?url=categorias');
        } else {
            SessionHelper::setFlash('danger', 'Error al crear la categoría');
            header('Location: /vetalmacen/public/index.php?url=categorias/crear');
        }
        exit();
    }

    /**
     * Mostrar formulario de edición
     */
    public function editar($id) {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        $categoriaModel = new Categoria();
        $categoria = $categoriaModel->getById($id);
        
        if (!$categoria) {
            SessionHelper::setFlash('danger', 'Categoría no encontrada');
            header('Location: /vetalmacen/public/index.php?url=categorias');
            exit();
        }
        
        require_once __DIR__ . '/../views/categorias/editar.php';
    }

    /**
     * Actualizar categoría
     */
    public function actualizar() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=categorias');
            exit();
        }

        $id = $_POST['id'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        // Validaciones
        if (!ValidationHelper::required($nombre)) {
            SessionHelper::setFlash('danger', 'El nombre es obligatorio');
            header('Location: /vetalmacen/public/index.php?url=categorias/editar/' . $id);
            exit();
        }

        $categoriaModel = new Categoria();
        if ($categoriaModel->nombreExists($nombre, $id)) {
            SessionHelper::setFlash('danger', 'El nombre de categoría ya existe');
            header('Location: /vetalmacen/public/index.php?url=categorias/editar/' . $id);
            exit();
        }

        $categoriaModel->Id = $id;
        $categoriaModel->Nombre = $nombre;
        $categoriaModel->Descripcion = $descripcion;

        if ($categoriaModel->update()) {
            SessionHelper::setFlash('success', 'Categoría actualizada exitosamente');
            header('Location: /vetalmacen/public/index.php?url=categorias');
        } else {
            SessionHelper::setFlash('danger', 'Error al actualizar la categoría');
            header('Location: /vetalmacen/public/index.php?url=categorias/editar/' . $id);
        }
        exit();
    }

    /**
     * Eliminar categoría
     */
    public function eliminar($id) {
        AuthHelper::requireRole('Administrador');
        
        $categoriaModel = new Categoria();
        $categoriaModel->Id = $id;
        
        if ($categoriaModel->delete()) {
            SessionHelper::setFlash('success', 'Categoría eliminada exitosamente');
        } else {
            SessionHelper::setFlash('danger', 'Error al eliminar. Puede tener subcategorías asociadas');
        }
        
        header('Location: /vetalmacen/public/index.php?url=categorias');
        exit();
    }
}