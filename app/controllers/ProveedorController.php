<?php
require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';
require_once __DIR__ . '/../../helpers/ValidationHelper.php';

class ProveedorController {
    
    /**
     * Listar proveedores
     */
    public function index() {
        AuthHelper::requireAuth();
        
        $proveedorModel = new Proveedor();
        $proveedores = $proveedorModel->getAll();
        
        require_once __DIR__ . '/../views/proveedores/index.php';
    }

    /**
     * Mostrar formulario de creación
     */
    public function crear() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        require_once __DIR__ . '/../views/proveedores/crear.php';
    }

    /**
     * Guardar proveedor
     */
    public function guardar() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=proveedores');
            exit();
        }

        $razonSocial = $_POST['razon_social'] ?? '';
        $ruc = $_POST['ruc'] ?? '';
        $nombreContacto = $_POST['nombre_contacto'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $email = $_POST['email'] ?? '';

        // Validaciones
        $errores = [];

        if (!ValidationHelper::required($razonSocial)) {
            $errores[] = 'La razón social es obligatoria';
        }

        if (!empty($ruc) && !ValidationHelper::ruc($ruc)) {
            $errores[] = 'El RUC debe tener 11 dígitos';
        }

        if (!empty($email) && !ValidationHelper::email($email)) {
            $errores[] = 'El email no es válido';
        }

        if (!empty($errores)) {
            SessionHelper::setFlash('danger', implode('<br>', $errores));
            header('Location: /vetalmacen/public/index.php?url=proveedores/crear');
            exit();
        }

        // Verificar si RUC ya existe
        $proveedorModel = new Proveedor();
        if (!empty($ruc) && $proveedorModel->rucExists($ruc)) {
            SessionHelper::setFlash('danger', 'El RUC ya está registrado');
            header('Location: /vetalmacen/public/index.php?url=proveedores/crear');
            exit();
        }

        $proveedorModel->RazonSocial = $razonSocial;
        $proveedorModel->RUC = $ruc;
        $proveedorModel->NombreContacto = $nombreContacto;
        $proveedorModel->Direccion = $direccion;
        $proveedorModel->Telefono = $telefono;
        $proveedorModel->Email = $email;

        if ($proveedorModel->create()) {
            SessionHelper::setFlash('success', 'Proveedor creado exitosamente');
            header('Location: /vetalmacen/public/index.php?url=proveedores');
        } else {
            SessionHelper::setFlash('danger', 'Error al crear el proveedor');
            header('Location: /vetalmacen/public/index.php?url=proveedores/crear');
        }
        exit();
    }

    /**
     * Mostrar formulario de edición
     */
    public function editar($id) {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        $proveedorModel = new Proveedor();
        $proveedor = $proveedorModel->getById($id);
        
        if (!$proveedor) {
            SessionHelper::setFlash('danger', 'Proveedor no encontrado');
            header('Location: /vetalmacen/public/index.php?url=proveedores');
            exit();
        }
        
        require_once __DIR__ . '/../views/proveedores/editar.php';
    }

    /**
     * Actualizar proveedor
     */
    public function actualizar() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=proveedores');
            exit();
        }

        $id = $_POST['id'] ?? '';
        $razonSocial = $_POST['razon_social'] ?? '';
        $ruc = $_POST['ruc'] ?? '';
        $nombreContacto = $_POST['nombre_contacto'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $email = $_POST['email'] ?? '';

        // Validaciones
        $errores = [];

        if (!ValidationHelper::positiveInteger($id)) {
            $errores[] = 'ID inválido';
        }

        if (!ValidationHelper::required($razonSocial)) {
            $errores[] = 'La razón social es obligatoria';
        }

        if (!empty($ruc) && !ValidationHelper::ruc($ruc)) {
            $errores[] = 'El RUC debe tener 11 dígitos';
        }

        if (!empty($email) && !ValidationHelper::email($email)) {
            $errores[] = 'El email no es válido';
        }

        if (!empty($errores)) {
            SessionHelper::setFlash('danger', implode('<br>', $errores));
            header('Location: /vetalmacen/public/index.php?url=proveedores/editar/' . $id);
            exit();
        }

        // Verificar si RUC ya existe (excluyendo el actual)
        $proveedorModel = new Proveedor();
        if (!empty($ruc) && $proveedorModel->rucExists($ruc, $id)) {
            SessionHelper::setFlash('danger', 'El RUC ya está registrado');
            header('Location: /vetalmacen/public/index.php?url=proveedores/editar/' . $id);
            exit();
        }

        $proveedorModel->Id = $id;
        $proveedorModel->RazonSocial = $razonSocial;
        $proveedorModel->RUC = $ruc;
        $proveedorModel->NombreContacto = $nombreContacto;
        $proveedorModel->Direccion = $direccion;
        $proveedorModel->Telefono = $telefono;
        $proveedorModel->Email = $email;

        if ($proveedorModel->update()) {
            SessionHelper::setFlash('success', 'Proveedor actualizado exitosamente');
            header('Location: /vetalmacen/public/index.php?url=proveedores');
        } else {
            SessionHelper::setFlash('danger', 'Error al actualizar el proveedor');
            header('Location: /vetalmacen/public/index.php?url=proveedores/editar/' . $id);
        }
        exit();
    }

    /**
     * Ver detalle del proveedor
     */
    public function detalle($id) {
        AuthHelper::requireAuth();
        
        $proveedorModel = new Proveedor();
        $proveedor = $proveedorModel->getById($id);
        
        if (!$proveedor) {
            SessionHelper::setFlash('danger', 'Proveedor no encontrado');
            header('Location: /vetalmacen/public/index.php?url=proveedores');
            exit();
        }
        
        require_once __DIR__ . '/../views/proveedores/detalle.php';
    }

    /**
     * Eliminar proveedor
     */
    public function eliminar($id) {
        AuthHelper::requireRole('Administrador');
        
        $proveedorModel = new Proveedor();
        $proveedorModel->Id = $id;
        
        if ($proveedorModel->delete()) {
            SessionHelper::setFlash('success', 'Proveedor eliminado exitosamente');
        } else {
            SessionHelper::setFlash('danger', 'Error al eliminar. Puede tener órdenes asociadas');
        }
        
        header('Location: /vetalmacen/public/index.php?url=proveedores');
        exit();
    }

    /**
     * Buscar proveedores (AJAX)
     */
    public function buscar() {
        AuthHelper::requireAuth();
        
        $searchTerm = $_GET['q'] ?? '';
        
        if (empty($searchTerm)) {
            echo json_encode([]);
            exit();
        }

        $proveedorModel = new Proveedor();
        $proveedores = $proveedorModel->search($searchTerm);
        
        header('Content-Type: application/json');
        echo json_encode($proveedores);
        exit();
    }
}