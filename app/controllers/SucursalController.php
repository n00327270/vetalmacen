<?php
require_once __DIR__ . '/../models/Sucursal.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';
require_once __DIR__ . '/../../helpers/ValidationHelper.php';

class SucursalController {
    
    /**
     * Listar sucursales
     */
    public function index() {
        AuthHelper::requireAuth();
        
        $sucursalModel = new Sucursal();
        $sucursales = $sucursalModel->getAll();
        
        require_once __DIR__ . '/../views/sucursales/index.php';
    }

    /**
     * Mostrar formulario de creación
     */
    public function crear() {
        AuthHelper::requireRole('Administrador');
        
        require_once __DIR__ . '/../views/sucursales/crear.php';
    }

    /**
     * Guardar sucursal
     */
    public function guardar() {
        AuthHelper::requireRole('Administrador');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=sucursales');
            exit();
        }

        $sede = $_POST['sede'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $email = $_POST['email'] ?? '';
        $horarioEntrega = $_POST['horario_entrega'] ?? '';
        $activo = isset($_POST['activo']) ? 1 : 0;

        // Validaciones
        $errores = [];

        if (!ValidationHelper::required($sede)) {
            $errores[] = 'La sede es obligatoria';
        }

        if (!ValidationHelper::required($direccion)) {
            $errores[] = 'La dirección es obligatoria';
        }

        if (!empty($email) && !ValidationHelper::email($email)) {
            $errores[] = 'El email no es válido';
        }

        if (!empty($errores)) {
            SessionHelper::setFlash('danger', implode('<br>', $errores));
            header('Location: /vetalmacen/public/index.php?url=sucursales/crear');
            exit();
        }

        $sucursalModel = new Sucursal();
        $sucursalModel->Sede = $sede;
        $sucursalModel->Direccion = $direccion;
        $sucursalModel->Telefono = $telefono;
        $sucursalModel->Email = $email;
        $sucursalModel->HorarioEntrega = $horarioEntrega;
        $sucursalModel->Activo = $activo;

        if ($sucursalModel->create()) {
            SessionHelper::setFlash('success', 'Sucursal creada exitosamente');
            header('Location: /vetalmacen/public/index.php?url=sucursales');
        } else {
            SessionHelper::setFlash('danger', 'Error al crear la sucursal');
            header('Location: /vetalmacen/public/index.php?url=sucursales/crear');
        }
        exit();
    }

    /**
     * Mostrar formulario de edición
     */
    public function editar($id) {
        AuthHelper::requireRole('Administrador');
        
        $sucursalModel = new Sucursal();
        $sucursal = $sucursalModel->getById($id);
        
        if (!$sucursal) {
            SessionHelper::setFlash('danger', 'Sucursal no encontrada');
            header('Location: /vetalmacen/public/index.php?url=sucursales');
            exit();
        }
        
        require_once __DIR__ . '/../views/sucursales/editar.php';
    }

    /**
     * Actualizar sucursal
     */
    public function actualizar() {
        AuthHelper::requireRole('Administrador');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=sucursales');
            exit();
        }

        $id = $_POST['id'] ?? '';
        $sede = $_POST['sede'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $email = $_POST['email'] ?? '';
        $horarioEntrega = $_POST['horario_entrega'] ?? '';
        $activo = isset($_POST['activo']) ? 1 : 0;

        // Validaciones
        $errores = [];

        if (!ValidationHelper::positiveInteger($id)) {
            $errores[] = 'ID inválido';
        }

        if (!ValidationHelper::required($sede)) {
            $errores[] = 'La sede es obligatoria';
        }

        if (!ValidationHelper::required($direccion)) {
            $errores[] = 'La dirección es obligatoria';
        }

        if (!empty($email) && !ValidationHelper::email($email)) {
            $errores[] = 'El email no es válido';
        }

        if (!empty($errores)) {
            SessionHelper::setFlash('danger', implode('<br>', $errores));
            header('Location: /vetalmacen/public/index.php?url=sucursales/editar/' . $id);
            exit();
        }

        $sucursalModel = new Sucursal();
        $sucursalModel->Id = $id;
        $sucursalModel->Sede = $sede;
        $sucursalModel->Direccion = $direccion;
        $sucursalModel->Telefono = $telefono;
        $sucursalModel->Email = $email;
        $sucursalModel->HorarioEntrega = $horarioEntrega;
        $sucursalModel->Activo = $activo;

        if ($sucursalModel->update()) {
            SessionHelper::setFlash('success', 'Sucursal actualizada exitosamente');
            header('Location: /vetalmacen/public/index.php?url=sucursales');
        } else {
            SessionHelper::setFlash('danger', 'Error al actualizar la sucursal');
            header('Location: /vetalmacen/public/index.php?url=sucursales/editar/' . $id);
        }
        exit();
    }

    /**
     * Eliminar sucursal (soft delete)
     */
    public function eliminar($id) {
        AuthHelper::requireRole('Administrador');
        
        $sucursalModel = new Sucursal();
        $sucursalModel->Id = $id;
        
        if ($sucursalModel->delete()) {
            SessionHelper::setFlash('success', 'Sucursal desactivada exitosamente');
        } else {
            SessionHelper::setFlash('danger', 'Error al desactivar la sucursal');
        }
        
        header('Location: /vetalmacen/public/index.php?url=sucursales');
        exit();
    }
}