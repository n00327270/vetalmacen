<?php
/**
 * OrdenSalidaController
 * Maneja CRUD de órdenes de salida
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/../models/OrdenSalida.php';
require_once __DIR__ . '/../models/DetalleOrdenSalida.php';
require_once __DIR__ . '/../models/Sucursal.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/StockSucursal.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';
require_once __DIR__ . '/../../helpers/ValidationHelper.php';

class OrdenSalidaController {
    
    /**
     * Listar órdenes de salida
     */
    public function index() {
        AuthHelper::requireAuth();
        
        $ordenModel = new OrdenSalida();
        $ordenes = $ordenModel->getAll();
        
        require_once __DIR__ . '/../views/ordenes_salida/index.php';
    }

    /**
     * Mostrar formulario de creación
     */
    public function crear() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero', 'Logistica']);
        
        $sucursalModel = new Sucursal();
        $productoModel = new Producto();
        
        $sucursales = $sucursalModel->getAllActive();
        $productos = $productoModel->getAll();
        
        require_once __DIR__ . '/../views/ordenes_salida/crear.php';
    }

    /**
     * Guardar orden de salida
     */
    public function guardar() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero', 'Logistica']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=ordenes_salida');
            exit();
        }

        $sucursalId = $_POST['sucursal_id'] ?? '';
        $tipoSalida = $_POST['tipo_salida'] ?? 'Venta';
        $observacion = $_POST['observacion'] ?? '';
        $productos = $_POST['productos'] ?? [];
        $cantidades = $_POST['cantidades'] ?? [];
        $precios = $_POST['precios'] ?? [];

        // Validaciones
        $errores = [];

        if (!ValidationHelper::positiveInteger($sucursalId)) {
            $errores[] = 'Debe seleccionar una sucursal válida';
        }

        if (!in_array($tipoSalida, ['Venta', 'Transferencia', 'SalidaInterna'])) {
            $errores[] = 'Tipo de salida inválido';
        }

        if (empty($productos) || count($productos) === 0) {
            $errores[] = 'Debe agregar al menos un producto';
        }

        // Validar stock disponible
        $stockModel = new StockSucursal();
        foreach ($productos as $index => $productoId) {
            $cantidad = $cantidades[$index] ?? 0;
            $stockDisponible = $stockModel->getStock($productoId, $sucursalId);
            
            if ($cantidad > $stockDisponible) {
                $productoModel = new Producto();
                $producto = $productoModel->getById($productoId);
                $errores[] = "Stock insuficiente para {$producto['Nombre']}. Disponible: {$stockDisponible}";
            }
        }

        if (!empty($errores)) {
            SessionHelper::setFlash('danger', implode('<br>', $errores));
            header('Location: /vetalmacen/public/index.php?url=ordenes_salida/crear');
            exit();
        }

        // Crear orden
        $user = SessionHelper::getUser();
        $ordenModel = new OrdenSalida();
        $ordenModel->UsuarioId = $user['id'];
        $ordenModel->SucursalId = $sucursalId;
        $ordenModel->TipoSalida = $tipoSalida;
        $ordenModel->Estado = 'Pendiente';
        $ordenModel->Total = 0;
        $ordenModel->Observacion = $observacion;

        $ordenId = $ordenModel->create();

        if (!$ordenId) {
            SessionHelper::setFlash('danger', 'Error al crear la orden de salida');
            header('Location: /vetalmacen/public/index.php?url=ordenes_salida/crear');
            exit();
        }

        // Crear detalles
        $detalleModel = new DetalleOrdenSalida();
        $totalOrden = 0;

        foreach ($productos as $index => $productoId) {
            $cantidad = $cantidades[$index] ?? 0;
            $precio = $precios[$index] ?? 0;

            if ($cantidad > 0 && $precio >= 0) {
                $detalleModel->OrdenSalidaId = $ordenId;
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

        SessionHelper::setFlash('success', 'Orden de salida creada exitosamente');
        header('Location: /vetalmacen/public/index.php?url=ordenes_salida/detalle/' . $ordenId);
        exit();
    }

    /**
     * Ver detalle de orden
     */
    public function detalle($id) {
        AuthHelper::requireAuth();
        
        $ordenModel = new OrdenSalida();
        $orden = $ordenModel->getById($id);
        
        if (!$orden) {
            SessionHelper::setFlash('danger', 'Orden no encontrada');
            header('Location: /vetalmacen/public/index.php?url=ordenes_salida');
            exit();
        }

        $detalleModel = new DetalleOrdenSalida();
        $detalles = $detalleModel->getByOrdenSalida($id);
        
        require_once __DIR__ . '/../views/ordenes_salida/detalle.php';
    }

    /**
     * Cambiar estado de la orden
     */
    public function cambiarEstado() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero', 'Logistica']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=ordenes_salida');
            exit();
        }

        $ordenId = $_POST['orden_id'] ?? '';
        $nuevoEstado = $_POST['estado'] ?? '';

        if (!in_array($nuevoEstado, ['Pendiente', 'Procesado', 'Cancelado'])) {
            SessionHelper::setFlash('danger', 'Estado inválido');
            header('Location: /vetalmacen/public/index.php?url=ordenes_salida/detalle/' . $ordenId);
            exit();
        }

        $ordenModel = new OrdenSalida();
        $ordenModel->Id = $ordenId;

        if ($ordenModel->updateEstado($nuevoEstado)) {
            SessionHelper::setFlash('success', 'Estado actualizado a: ' . $nuevoEstado);
        } else {
            SessionHelper::setFlash('danger', 'Error al actualizar el estado');
        }

        header('Location: /vetalmacen/public/index.php?url=ordenes_salida/detalle/' . $ordenId);
        exit();
    }

    /**
     * Eliminar orden
     */
    public function eliminar($id) {
        AuthHelper::requireRole('Administrador');
        
        $ordenModel = new OrdenSalida();
        $ordenModel->Id = $id;
        
        if ($ordenModel->delete()) {
            SessionHelper::setFlash('success', 'Orden eliminada exitosamente');
        } else {
            SessionHelper::setFlash('danger', 'Solo se pueden eliminar órdenes Pendientes o Canceladas');
        }
        
        header('Location: /vetalmacen/public/index.php?url=ordenes_salida');
        exit();
    }
}