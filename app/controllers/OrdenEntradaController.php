<?php
/**
 * OrdenEntradaController
 * Maneja CRUD de órdenes de entrada
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/../models/OrdenEntrada.php';
require_once __DIR__ . '/../models/DetalleOrdenEntrada.php';
require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../models/Sucursal.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';
require_once __DIR__ . '/../../helpers/ValidationHelper.php';

class OrdenEntradaController {
    
    /**
     * Listar órdenes de entrada
     */
    public function index() {
        AuthHelper::requireAuth();
        
        $ordenModel = new OrdenEntrada();
        $ordenes = $ordenModel->getAll();
        
        require_once __DIR__ . '/../views/ordenes_entrada/index.php';
    }

    /**
     * Mostrar formulario de creación
     */
    public function crear() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        $proveedorModel = new Proveedor();
        $sucursalModel = new Sucursal();
        $productoModel = new Producto();
        
        $proveedores = $proveedorModel->getAll();
        $sucursales = $sucursalModel->getAllActive();
        $productos = $productoModel->getAll();
        
        require_once __DIR__ . '/../views/ordenes_entrada/crear.php';
    }

    /**
     * Guardar orden de entrada
     */
    public function guardar() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=ordenes_entrada');
            exit();
        }

        $proveedorId = $_POST['proveedor_id'] ?? '';
        $sucursalId = $_POST['sucursal_id'] ?? '';
        $observacion = $_POST['observacion'] ?? '';
        $productos = $_POST['productos'] ?? [];
        $cantidades = $_POST['cantidades'] ?? [];
        $precios = $_POST['precios'] ?? [];

        // Validaciones
        $errores = [];

        if (!ValidationHelper::positiveInteger($proveedorId)) {
            $errores[] = 'Debe seleccionar un proveedor válido';
        }

        if (!ValidationHelper::positiveInteger($sucursalId)) {
            $errores[] = 'Debe seleccionar una sucursal válida';
        }

        if (empty($productos) || count($productos) === 0) {
            $errores[] = 'Debe agregar al menos un producto';
        }

        if (!empty($errores)) {
            SessionHelper::setFlash('danger', implode('<br>', $errores));
            header('Location: /vetalmacen/public/index.php?url=ordenes_entrada/crear');
            exit();
        }

        // Crear orden
        $user = SessionHelper::getUser();
        $ordenModel = new OrdenEntrada();
        $ordenModel->ProveedorId = $proveedorId;
        $ordenModel->UsuarioId = $user['id'];
        $ordenModel->SucursalId = $sucursalId;
        $ordenModel->Estado = 'Pendiente';
        $ordenModel->Total = 0;
        $ordenModel->Observacion = $observacion;

        $ordenId = $ordenModel->create();

        if (!$ordenId) {
            SessionHelper::setFlash('danger', 'Error al crear la orden de entrada');
            header('Location: /vetalmacen/public/index.php?url=ordenes_entrada/crear');
            exit();
        }

        // Crear detalles
        $detalleModel = new DetalleOrdenEntrada();
        $totalOrden = 0;

        foreach ($productos as $index => $productoId) {
            $cantidad = $cantidades[$index] ?? 0;
            $precio = $precios[$index] ?? 0;

            if ($cantidad > 0 && $precio >= 0) {
                $detalleModel->OrdenEntradaId = $ordenId;
                $detalleModel->ProductoId = $productoId;
                $detalleModel->Cantidad = $cantidad;
                $detalleModel->PrecioUnitario = $precio;
                
                if ($detalleModel->create()) {
                    $totalOrden += ($cantidad * $precio);
                }
            }
        }

        // Actualizar total de la orden
        $ordenModel->Id = $ordenId;
        $ordenModel->updateTotal($totalOrden);

        SessionHelper::setFlash('success', 'Orden de entrada creada exitosamente');
        header('Location: /vetalmacen/public/index.php?url=ordenes_entrada/detalle/' . $ordenId);
        exit();
    }

    /**
     * Ver detalle de orden
     */
    public function detalle($id) {
        AuthHelper::requireAuth();
        
        $ordenModel = new OrdenEntrada();
        $orden = $ordenModel->getById($id);
        
        if (!$orden) {
            SessionHelper::setFlash('danger', 'Orden no encontrada');
            header('Location: /vetalmacen/public/index.php?url=ordenes_entrada');
            exit();
        }

        $detalleModel = new DetalleOrdenEntrada();
        $detalles = $detalleModel->getByOrdenEntrada($id);
        
        require_once __DIR__ . '/../views/ordenes_entrada/detalle.php';
    }

    /**
     * Cambiar estado de la orden
     */
    public function cambiarEstado() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=ordenes_entrada');
            exit();
        }

        $ordenId = $_POST['orden_id'] ?? '';
        $nuevoEstado = $_POST['estado'] ?? '';

        if (!in_array($nuevoEstado, ['Pendiente', 'Recibido', 'Cancelado'])) {
            SessionHelper::setFlash('danger', 'Estado inválido');
            header('Location: /vetalmacen/public/index.php?url=ordenes_entrada/detalle/' . $ordenId);
            exit();
        }

        $ordenModel = new OrdenEntrada();
        $ordenModel->Id = $ordenId;

        if ($ordenModel->updateEstado($nuevoEstado)) {
            SessionHelper::setFlash('success', 'Estado actualizado a: ' . $nuevoEstado);
        } else {
            SessionHelper::setFlash('danger', 'Error al actualizar el estado');
        }

        header('Location: /vetalmacen/public/index.php?url=ordenes_entrada/detalle/' . $ordenId);
        exit();
    }

    /**
     * Eliminar orden
     */
    public function eliminar($id) {
        AuthHelper::requireRole('Administrador');
        
        $ordenModel = new OrdenEntrada();
        $ordenModel->Id = $id;
        
        if ($ordenModel->delete()) {
            SessionHelper::setFlash('success', 'Orden eliminada exitosamente');
        } else {
            SessionHelper::setFlash('danger', 'Solo se pueden eliminar órdenes Pendientes o Canceladas');
        }
        
        header('Location: /vetalmacen/public/index.php?url=ordenes_entrada');
        exit();
    }
}