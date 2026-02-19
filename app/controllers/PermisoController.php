<?php
require_once __DIR__ . '/../models/Modulo.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';
require_once __DIR__ . '/../../helpers/PermisoHelper.php';

class PermisoController {

    private $conn;

    public function __construct() {
        $database    = new Database();
        $this->conn  = $database->getConnection();
    }

    /**
     * Lista de roles disponibles para gestionar permisos
     */
    public function index() {
        PermisoHelper::requireSuperUsuario();
        AuthHelper::requireAuth();

        // Obtener roles excluyendo SuperUsuario (no tiene sentido gestionarlos)
        $query = "SELECT r.Id, r.Nombre,
                         COUNT(p.ModuloId) as total_permisos
                  FROM rol r
                  LEFT JOIN permiso p ON r.Id = p.RolId
                  GROUP BY r.Id, r.Nombre
                  ORDER BY r.Id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $roles = $stmt->fetchAll();

        // Total de módulos activos (para calcular porcentaje)
        $queryTotal = "SELECT COUNT(*) as total FROM modulo WHERE Activo = 1";
        $stmt       = $this->conn->prepare($queryTotal);
        $stmt->execute();
        $totalModulos = $stmt->fetch()['total'];

        require_once __DIR__ . '/../views/permisos/index.php';
    }

    /**
     * Vista de asignación de permisos para un rol
     */
    public function asignar($rolId) {
        PermisoHelper::requireSuperUsuario();
        AuthHelper::requireAuth();

        // Verificar que el rol existe
        $rol = $this->getRolById($rolId);
        if (!$rol) {
            SessionHelper::setFlash('danger', 'Rol no encontrado');
            header('Location: /vetalmacen/public/index.php?url=permisos');
            exit();
        }

        // Obtener árbol completo de módulos activos
        $moduloModel = new Modulo();
        $arbol       = $moduloModel->getArbolCompleto();

        // Obtener IDs de módulos que ya tiene este rol
        $query = "SELECT ModuloId FROM permiso WHERE RolId = :rol_id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':rol_id', $rolId);
        $stmt->execute();
        $permisosActuales = array_column($stmt->fetchAll(), 'ModuloId');

        require_once __DIR__ . '/../views/permisos/asignar.php';
    }

    /**
     * Guardar permisos de un rol (POST)
     * Estrategia: DELETE todos + INSERT los marcados
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
        if (!$rolId || !$this->getRolById($rolId)) {
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

        try {
            $this->conn->beginTransaction();

            // 1. Eliminar todos los permisos actuales del rol
            $queryDelete = "DELETE FROM permiso WHERE RolId = :rol_id";
            $stmt        = $this->conn->prepare($queryDelete);
            $stmt->bindParam(':rol_id', $rolId);
            $stmt->execute();

            // 2. Insertar los nuevos permisos en batch
            if (!empty($permisos)) {
                $placeholders = implode(',', array_fill(0, count($permisos), '(?, ?)'));
                $queryInsert  = "INSERT INTO permiso (RolId, ModuloId) VALUES " . $placeholders;
                $stmt         = $this->conn->prepare($queryInsert);

                $valores = [];
                foreach ($permisos as $moduloId) {
                    $valores[] = (int)$rolId;
                    $valores[] = (int)$moduloId;
                }

                $stmt->execute($valores);
            }

            $this->conn->commit();

            $rol = $this->getRolById($rolId);
            SessionHelper::setFlash('success', 'Permisos del rol "' . $rol['Nombre'] . '" actualizados correctamente (' . count($permisos) . ' permisos asignados)');

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('Error guardando permisos: ' . $e->getMessage());
            SessionHelper::setFlash('danger', 'Error al guardar los permisos. Intenta nuevamente.');
        }

        header('Location: /vetalmacen/public/index.php?url=permisos');
        exit();
    }

    /**
     * Obtener rol por ID
     */
    private function getRolById($rolId) {
        $query = "SELECT * FROM rol WHERE Id = :id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $rolId);
        $stmt->execute();
        return $stmt->fetch();
    }
}