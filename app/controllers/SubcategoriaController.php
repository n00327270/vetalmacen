<?php
/**
 * SubcategoriaController
 * Maneja CRUD de subcategorías
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/../models/Subcategoria.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';
require_once __DIR__ . '/../../helpers/ValidationHelper.php';

class SubcategoriaController {
    
    /**
     * Listar subcategorías
     */
    public function index() {
        AuthHelper::requireAuth();
        
        $subcategoriaModel = new Subcategoria();
        $subcategorias = $subcategoriaModel->getAll();
        
        require_once __DIR__ . '/../views/subcategorias/index.php';
    }

    /**
     * Mostrar formulario de creación
     */
    public function crear() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        $categoriaModel = new Categoria();
        $categorias = $categoriaModel->getAll();
        
        require_once __DIR__ . '/../views/subcategorias/crear.php';
    }

    /**
     * Guardar subcategoría
     */
    public function guardar() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=subcategorias');
            exit();
        }

        $categoriaId = $_POST['categoria_id'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        // Validaciones
        $errores = [];

        if (!ValidationHelper::positiveInteger($categoriaId)) {
            $errores[] = 'Debe seleccionar una categoría válida';
        }

        if (!ValidationHelper::required($nombre)) {
            $errores[] = 'El nombre es obligatorio';
        }

        if (!empty($errores)) {
            SessionHelper::setFlash('danger', implode('<br>', $errores));
            header('Location: /vetalmacen/public/index.php?url=subcategorias/crear');
            exit();
        }

        $subcategoriaModel = new Subcategoria();
        $subcategoriaModel->CategoriaId = $categoriaId;
        $subcategoriaModel->Nombre = $nombre;
        $subcategoriaModel->Descripcion = $descripcion;

        if ($subcategoriaModel->create()) {
            SessionHelper::setFlash('success', 'Subcategoría creada exitosamente');
            header('Location: /vetalmacen/public/index.php?url=subcategorias');
        } else {
            SessionHelper::setFlash('danger', 'Error al crear la subcategoría');
            header('Location: /vetalmacen/public/index.php?url=subcategorias/crear');
        }
        exit();
    }

    /**
     * Mostrar formulario de edición
     */
    public function editar($id) {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        $subcategoriaModel = new Subcategoria();
        $subcategoria = $subcategoriaModel->getById($id);
        
        if (!$subcategoria) {
            SessionHelper::setFlash('danger', 'Subcategoría no encontrada');
            header('Location: /vetalmacen/public/index.php?url=subcategorias');
            exit();
        }

        $categoriaModel = new Categoria();
        $categorias = $categoriaModel->getAll();
        
        require_once __DIR__ . '/../views/subcategorias/editar.php';
    }

    /**
     * Actualizar subcategoría
     */
    public function actualizar() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=subcategorias');
            exit();
        }

        $id = $_POST['id'] ?? '';
        $categoriaId = $_POST['categoria_id'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        // Validaciones
        $errores = [];

        if (!ValidationHelper::positiveInteger($id)) {
            $errores[] = 'ID inválido';
        }

        if (!ValidationHelper::positiveInteger($categoriaId)) {
            $errores[] = 'Debe seleccionar una categoría válida';
        }

        if (!ValidationHelper::required($nombre)) {
            $errores[] = 'El nombre es obligatorio';
        }

        if (!empty($errores)) {
            SessionHelper::setFlash('danger', implode('<br>', $errores));
            header('Location: /vetalmacen/public/index.php?url=subcategorias/editar/' . $id);
            exit();
        }

        $subcategoriaModel = new Subcategoria();
        $subcategoriaModel->Id = $id;
        $subcategoriaModel->CategoriaId = $categoriaId;
        $subcategoriaModel->Nombre = $nombre;
        $subcategoriaModel->Descripcion = $descripcion;

        if ($subcategoriaModel->update()) {
            SessionHelper::setFlash('success', 'Subcategoría actualizada exitosamente');
            header('Location: /vetalmacen/public/index.php?url=subcategorias');
        } else {
            SessionHelper::setFlash('danger', 'Error al actualizar la subcategoría');
            header('Location: /vetalmacen/public/index.php?url=subcategorias/editar/' . $id);
        }
        exit();
    }

    /**
     * Eliminar subcategoría
     */
    public function eliminar($id) {
        AuthHelper::requireRole('Administrador');
        
        $subcategoriaModel = new Subcategoria();
        $subcategoriaModel->Id = $id;
        
        if ($subcategoriaModel->delete()) {
            SessionHelper::setFlash('success', 'Subcategoría eliminada exitosamente');
        } else {
            SessionHelper::setFlash('danger', 'Error al eliminar. Puede tener productos asociados');
        }
        
        header('Location: /vetalmacen/public/index.php?url=subcategorias');
        exit();
    }
}