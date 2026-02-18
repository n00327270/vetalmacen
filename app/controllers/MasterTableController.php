<?php
require_once __DIR__ . '/../models/MasterTable.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';
require_once __DIR__ . '/../../helpers/PermisoHelper.php';

class MasterTableController {
    
    /**
     * Vista principal - Árbol jerárquico
     */
    public function index() {
        PermisoHelper::requirePermiso('mastertable/index');
        AuthHelper::requireAuth();
        
        $masterTableModel = new MasterTable();
        $arbol = $masterTableModel->getArbolCompleto();
        
        require_once __DIR__ . '/../views/mastertable/index.php';
    }
    
    /**
     * Formulario crear registro
     */
    public function crear() {
        PermisoHelper::requirePermiso('mastertable/crear');
        
        // Obtener padres para el select
        $masterTableModel = new MasterTable();
        $padres = $masterTableModel->getAllPadres();
        
        // Si viene parent_id por URL, pre-seleccionar
        $parentIdPreselect = $_GET['parent_id'] ?? null;
        
        require_once __DIR__ . '/../views/mastertable/crear.php';
    }
    
    /**
     * Procesar creación
     */
    public function guardar() {
        PermisoHelper::requirePermiso('mastertable/crear');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=mastertable');
            exit();
        }
        
        $masterTableModel = new MasterTable();
        $user = SessionHelper::getUser();
        
        // Datos del formulario
        $tipo = $_POST['tipo'] ?? 'hijo';
        $masterTableModel->IdMasterTableParent = ($tipo === 'padre') ? null : ($_POST['parent_id'] ?? null);
        $masterTableModel->Name = trim($_POST['name'] ?? '');
        $masterTableModel->Value = trim($_POST['value'] ?? '');
        $masterTableModel->Description = trim($_POST['description'] ?? '');
        $masterTableModel->Order = $_POST['order'] ?? $masterTableModel->getNextOrder($masterTableModel->IdMasterTableParent);
        $masterTableModel->AdditionalOne = trim($_POST['additional_one'] ?? '');
        $masterTableModel->AdditionalTwo = trim($_POST['additional_two'] ?? '');
        $masterTableModel->AdditionalThree = trim($_POST['additional_three'] ?? '');
        $masterTableModel->UserNew = $user['username'];
        $masterTableModel->States = 1;
        
        // Validaciones
        if (empty($masterTableModel->Name)) {
            SessionHelper::setFlash('danger', 'El nombre es requerido');
            header('Location: /vetalmacen/public/index.php?url=mastertable/crear');
            exit();
        }
        
        if ($tipo === 'hijo' && empty($masterTableModel->IdMasterTableParent)) {
            SessionHelper::setFlash('danger', 'Debe seleccionar un catálogo padre');
            header('Location: /vetalmacen/public/index.php?url=mastertable/crear');
            exit();
        }
        
        // Crear
        $nuevoId = $masterTableModel->create();
        if ($nuevoId) {
            if ($tipo === 'padre') {
                SessionHelper::setFlash('success', "Catálogo padre creado con ID $nuevoId");
            } else {
                SessionHelper::setFlash('success', "Registro hijo creado con ID $nuevoId");
            }
        } else {
            SessionHelper::setFlash('danger', 'Error al crear el registro');
        }
        
        header('Location: /vetalmacen/public/index.php?url=mastertable');
        exit();
    }
    
    /**
     * Formulario editar registro
     */
    public function editar($id) {
        PermisoHelper::requirePermiso('mastertable/editar');
        
        $masterTableModel = new MasterTable();
        $registro = $masterTableModel->getById($id);
        
        if (!$registro) {
            SessionHelper::setFlash('danger', 'Registro no encontrado');
            header('Location: /vetalmacen/public/index.php?url=mastertable');
            exit();
        }
        
        $padres = $masterTableModel->getAllPadres();
        
        require_once __DIR__ . '/../views/mastertable/editar.php';
    }
    
    /**
     * Procesar actualización
     */
    public function actualizar() {
        PermisoHelper::requirePermiso('mastertable/editar');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=mastertable');
            exit();
        }
        
        $masterTableModel = new MasterTable();
        $user = SessionHelper::getUser();
        
        $masterTableModel->IdMasterTable = $_POST['id'] ?? '';
        $masterTableModel->Name = trim($_POST['name'] ?? '');
        $masterTableModel->Value = trim($_POST['value'] ?? '');
        $masterTableModel->Description = trim($_POST['description'] ?? '');
        $masterTableModel->Order = $_POST['order'] ?? 0;
        $masterTableModel->AdditionalOne = trim($_POST['additional_one'] ?? '');
        $masterTableModel->AdditionalTwo = trim($_POST['additional_two'] ?? '');
        $masterTableModel->AdditionalThree = trim($_POST['additional_three'] ?? '');
        $masterTableModel->States = isset($_POST['states']) ? 1 : 0;
        $masterTableModel->UserEdit = $user['username'];
        
        // Validaciones
        if (empty($masterTableModel->Name)) {
            SessionHelper::setFlash('danger', 'El nombre es requerido');
            header('Location: /vetalmacen/public/index.php?url=mastertable/editar/' . $masterTableModel->IdMasterTable);
            exit();
        }
        
        // Actualizar
        if ($masterTableModel->update()) {
            SessionHelper::setFlash('success', 'Registro actualizado exitosamente');
        } else {
            SessionHelper::setFlash('danger', 'Error al actualizar el registro');
        }
        
        header('Location: /vetalmacen/public/index.php?url=mastertable');
        exit();
    }
    
    /**
     * Eliminar registro (soft delete)
     */
    public function eliminar($id) {
        PermisoHelper::requirePermiso('mastertable/eliminar');
        
        $masterTableModel = new MasterTable();
        $registro = $masterTableModel->getById($id);
        
        if (!$registro) {
            SessionHelper::setFlash('danger', 'Registro no encontrado');
            header('Location: /vetalmacen/public/index.php?url=mastertable');
            exit();
        }
        
        // Validar si tiene hijos activos
        if ($masterTableModel->tieneHijosActivos($id)) {
            SessionHelper::setFlash('danger', 'No se puede eliminar. Tiene registros hijos activos');
            header('Location: /vetalmacen/public/index.php?url=mastertable');
            exit();
        }
        
        // Validar si está en uso
        if ($masterTableModel->estaEnUso($id)) {
            SessionHelper::setFlash('danger', 'No se puede eliminar. Está siendo utilizado en otros registros');
            header('Location: /vetalmacen/public/index.php?url=mastertable');
            exit();
        }
        
        // Eliminar (soft delete)
        $user = SessionHelper::getUser();
        $masterTableModel->IdMasterTable = $id;
        $masterTableModel->UserEdit = $user['username'];
        
        if ($masterTableModel->delete()) {
            SessionHelper::setFlash('success', 'Registro eliminado exitosamente');
        } else {
            SessionHelper::setFlash('danger', 'Error al eliminar el registro');
        }
        
        header('Location: /vetalmacen/public/index.php?url=mastertable');
        exit();
    }
    
    /**
     * Cambiar estado (activar/desactivar)
     */
    public function toggleEstado($id) {
        PermisoHelper::requirePermiso('mastertable/toggleEstado');
        
        $masterTableModel = new MasterTable();
        $registro = $masterTableModel->getById($id);
        
        if (!$registro) {
            SessionHelper::setFlash('danger', 'Registro no encontrado');
            header('Location: /vetalmacen/public/index.php?url=mastertable');
            exit();
        }
        
        // Cambiar estado
        $user = SessionHelper::getUser();
        $masterTableModel->IdMasterTable = $id;
        $masterTableModel->States = $registro['States'] == 1 ? 0 : 1;
        $masterTableModel->UserEdit = $user['username'];
        
        $query = "UPDATE mastertable SET States = :states, UserEdit = :user_edit, DateEdit = NOW() WHERE IdMasterTable = :id";
        $database = new Database();
        $conn = $database->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':states', $masterTableModel->States);
        $stmt->bindParam(':user_edit', $masterTableModel->UserEdit);
        $stmt->bindParam(':id', $masterTableModel->IdMasterTable);
        
        if ($stmt->execute()) {
            $mensaje = $masterTableModel->States == 1 ? 'activado' : 'desactivado';
            SessionHelper::setFlash('success', "Registro $mensaje exitosamente");
        } else {
            SessionHelper::setFlash('danger', 'Error al cambiar el estado');
        }
        
        header('Location: /vetalmacen/public/index.php?url=mastertable');
        exit();
    }
}