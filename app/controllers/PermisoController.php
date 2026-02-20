<?php
require_once __DIR__ . '/../models/Modulo.php';
require_once __DIR__ . '/../models/Permiso.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';
require_once __DIR__ . '/../../helpers/PermisoHelper.php';

class PermisoController {

    private $permisoModel;
    private $moduloModel;

    public function __construct() {
        $this->permisoModel = new Permiso();
        $this->moduloModel  = new Modulo();
    }

    /**
     * Lista de roles disponibles para gestionar permisos
     */
    public function index() {
        PermisoHelper::requireSuperUsuario();
        AuthHelper::requireAuth();

        $roles        = $this->permisoModel->getRolesConPermisos();
        $totalModulos = $this->permisoModel->getTotalModulosActivos();

        require_once __DIR__ . '/../views/permisos/index.php';
    }

    /**
     * Vista de asignación de permisos para un rol
     */
    public function asignar($rolId) {
        PermisoHelper::requireSuperUsuario();
        AuthHelper::requireAuth();

        // Verificar que el rol existe
        $rol = $this->permisoModel->getRolById($rolId);
        if (!$rol) {
            SessionHelper::setFlash('danger', 'Rol no encontrado');
            header('Location: /vetalmacen/public/index.php?url=permisos');
            exit();
        }

        // Obtener árbol completo de módulos activos
        $arbol = $this->moduloModel->getArbolCompleto();

        // Obtener IDs de módulos que ya tiene este rol
        $permisosActuales = $this->permisoModel->getPermisosByRol($rolId);

        require_once __DIR__ . '/../views/permisos/asignar.php';
    }

    /**
     * Guardar permisos de un rol (POST)
     */
    public function guardar() {
        PermisoHelper::requireSuperUsuario();
        AuthHelper::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=permisos');
            exit();
        }

        $rolId    = $_POST['rol_id']    ?? null;
        $permisos = $_POST['permisos']  ?? [];

        // Validar rol
        if (!$rolId || !$this->permisoModel->getRolById($rolId)) {
            SessionHelper::setFlash('danger', 'Rol no válido');
            header('Location: /vetalmacen/public/index.php?url=permisos');
            exit();
        }

        // Validar que al menos 1 permiso esté seleccionado
        if (empty($permisos)) {
            SessionHelper::setFlash('warning', 'Debes seleccionar al menos un permiso');
            header('Location: /vetalmacen/public/index.php?url=permisos/asignar/' . $rolId);
            exit();
        }

        // Filtrar solo IDs numéricos válidos
        $permisos = array_filter($permisos, 'is_numeric');

        // Actualizar permisos usando el modelo
        if ($this->permisoModel->actualizarPermisos($rolId, $permisos)) {
            $rol = $this->permisoModel->getRolById($rolId);
            SessionHelper::setFlash(
                'success',
                'Permisos del rol "' . $rol['Nombre'] . '" actualizados correctamente (' . count($permisos) . ' permisos asignados)'
            );
        } else {
            SessionHelper::setFlash('danger', 'Error al guardar los permisos. Intenta nuevamente.');
        }

        header('Location: /vetalmacen/public/index.php?url=permisos');
        exit();
    }
}