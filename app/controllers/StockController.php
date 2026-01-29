<?php
/**
 * StockController
 * Maneja visualización y gestión de stock
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/../models/StockSucursal.php';
require_once __DIR__ . '/../models/Sucursal.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';

class StockController {
    
    /**
     * Ver stock general o por sucursal
     */
    public function index() {
        AuthHelper::requireAuth();
        
        $sucursalModel = new Sucursal();
        $sucursales = $sucursalModel->getAllActive();
        
        $stockModel = new StockSucursal();
        $sucursalId = $_GET['sucursal_id'] ?? null;
        
        if ($sucursalId) {
            $stock = $stockModel->getBySucursal($sucursalId);
        } else {
            $stock = $stockModel->getAll();
        }
        
        require_once __DIR__ . '/../views/stock/index.php';
    }

    /**
     * Ver productos con stock bajo
     */
    public function stockBajo() {
        AuthHelper::requireAuth();
        
        $limite = $_GET['limite'] ?? 10;
        $sucursalId = $_GET['sucursal_id'] ?? null;
        
        $stockModel = new StockSucursal();
        $stockBajo = $stockModel->getStockBajo($limite, $sucursalId);
        
        $sucursalModel = new Sucursal();
        $sucursales = $sucursalModel->getAllActive();
        
        require_once __DIR__ . '/../views/stock/stock_bajo.php';
    }

    /**
     * Obtener stock de un producto por AJAX
     */
    public function getStockProducto() {
        AuthHelper::requireAuth();
        
        $productoId = $_GET['producto_id'] ?? '';
        $sucursalId = $_GET['sucursal_id'] ?? '';
        
        if (empty($productoId) || empty($sucursalId)) {
            echo json_encode(['stock' => 0]);
            exit();
        }

        $stockModel = new StockSucursal();
        $stock = $stockModel->getStock($productoId, $sucursalId);
        
        header('Content-Type: application/json');
        echo json_encode(['stock' => $stock]);
        exit();
    }

    /**
     * Vista de transferencias entre sucursales
     */
    public function transferencias() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        $sucursalModel = new Sucursal();
        $sucursales = $sucursalModel->getAllActive();
        
        require_once __DIR__ . '/../views/stock/transferencias.php';
    }

    /**
     * Buscar productos para transferencia (AJAX)
     */
    public function buscarProductosParaTransferencia() {
        AuthHelper::requireAuth();
        
        $sucursalId = $_GET['sucursal_id'] ?? '';
        $search = $_GET['search'] ?? '';
        
        if (empty($sucursalId) || empty($search)) {
            echo json_encode([]);
            exit();
        }
        
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "SELECT 
            p.Id,
            p.Codigo,
            p.Nombre,
            ss.Stock
        FROM producto p
        INNER JOIN stock_sucursal ss ON p.Id = ss.ProductoId
        WHERE ss.SucursalId = :sucursal_id
        AND ss.Stock > 0
        AND (p.Codigo LIKE :search OR p.Nombre LIKE :search)
        ORDER BY p.Nombre ASC
        LIMIT 20";
        
        $stmt = $conn->prepare($query);
        $searchParam = '%' . $search . '%';
        $stmt->bindParam(':sucursal_id', $sucursalId);
        $stmt->bindParam(':search', $searchParam);
        $stmt->execute();
        
        $productos = $stmt->fetchAll();
        
        header('Content-Type: application/json');
        echo json_encode($productos);
        exit();
    }

    /**
     * Procesar transferencia entre sucursales
     */
    public function procesarTransferencia() {
        AuthHelper::requireAnyRole(['Administrador', 'Almacenero']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vetalmacen/public/index.php?url=stock/transferencias');
            exit();
        }
        
        $sucursalOrigen = $_POST['sucursal_origen'] ?? '';
        $sucursalDestino = $_POST['sucursal_destino'] ?? '';
        $productos = $_POST['productos'] ?? [];
        $observacion = $_POST['observacion'] ?? '';
        
        // Validaciones
        if (empty($sucursalOrigen) || empty($sucursalDestino)) {
            SessionHelper::setFlash('danger', 'Debe seleccionar sucursal de origen y destino');
            header('Location: /vetalmacen/public/index.php?url=stock/transferencias');
            exit();
        }
        
        if ($sucursalOrigen === $sucursalDestino) {
            SessionHelper::setFlash('danger', 'La sucursal de origen y destino no pueden ser la misma');
            header('Location: /vetalmacen/public/index.php?url=stock/transferencias');
            exit();
        }
        
        if (empty($productos)) {
            SessionHelper::setFlash('danger', 'Debe agregar al menos un producto');
            header('Location: /vetalmacen/public/index.php?url=stock/transferencias');
            exit();
        }
        
        // Iniciar transacción
        $database = new Database();
        $conn = $database->getConnection();
        
        try {
            $conn->beginTransaction();
            
            $user = SessionHelper::getUser();
            $usuarioId = $user['id'];
            
            // 1. Crear orden de SALIDA desde sucursal origen
            $ordenSalidaModel = new OrdenSalida();
            $ordenSalidaModel->UsuarioId = $usuarioId;
            $ordenSalidaModel->SucursalId = $sucursalOrigen;
            $ordenSalidaModel->TipoSalida = 'Transferencia';
            $ordenSalidaModel->Estado = 'Procesado';
            $ordenSalidaModel->Total = 0;
            $ordenSalidaModel->Observacion = 'Transferencia a sucursal destino. ' . $observacion;
            
            $ordenSalidaId = $ordenSalidaModel->create();
            
            if (!$ordenSalidaId) {
                throw new Exception('Error al crear orden de salida');
            }
            
            // 2. Crear orden de ENTRADA en sucursal destino
            $ordenEntradaModel = new OrdenEntrada();
            $ordenEntradaModel->ProveedorId = 1; // Asumiendo que existe un proveedor "Transferencia Interna" con ID 1
            $ordenEntradaModel->UsuarioId = $usuarioId;
            $ordenEntradaModel->SucursalId = $sucursalDestino;
            $ordenEntradaModel->Estado = 'Recibido';
            $ordenEntradaModel->Total = 0;
            $ordenEntradaModel->Observacion = 'Transferencia desde sucursal origen. ' . $observacion;
            
            $ordenEntradaId = $ordenEntradaModel->create();
            
            if (!$ordenEntradaId) {
                throw new Exception('Error al crear orden de entrada');
            }
            
            $totalTransferencia = 0;
            
            // 3. Procesar cada producto
            foreach ($productos as $prod) {
                $productoId = $prod['id'];
                $cantidad = $prod['cantidad'];
                
                // Obtener precio del producto
                $productoModel = new Producto();
                $producto = $productoModel->getById($productoId);
                $precio = $producto['Precio'];
                $subtotal = $cantidad * $precio;
                $totalTransferencia += $subtotal;
                
                // Verificar stock disponible
                $stockModel = new StockSucursal();
                $stockActual = $stockModel->getStock($productoId, $sucursalOrigen);
                
                if ($stockActual < $cantidad) {
                    throw new Exception("Stock insuficiente para producto ID {$productoId}");
                }
                
                // Crear detalle de salida
                $detalleSalidaModel = new DetalleOrdenSalida();
                $detalleSalidaModel->OrdenSalidaId = $ordenSalidaId;
                $detalleSalidaModel->ProductoId = $productoId;
                $detalleSalidaModel->Cantidad = $cantidad;
                $detalleSalidaModel->PrecioUnitario = $precio;
                $detalleSalidaModel->SubTotal = $subtotal;
                
                if (!$detalleSalidaModel->create()) {
                    throw new Exception("Error al crear detalle de salida para producto ID {$productoId}");
                }
                
                // Crear detalle de entrada
                $detalleEntradaModel = new DetalleOrdenEntrada();
                $detalleEntradaModel->OrdenEntradaId = $ordenEntradaId;
                $detalleEntradaModel->ProductoId = $productoId;
                $detalleEntradaModel->Cantidad = $cantidad;
                $detalleEntradaModel->PrecioUnitario = $precio;
                $detalleEntradaModel->SubTotal = $subtotal;
                
                if (!$detalleEntradaModel->create()) {
                    throw new Exception("Error al crear detalle de entrada para producto ID {$productoId}");
                }
            }
            
            // 4. Actualizar totales de las órdenes
            $stmtUpdateSalida = $conn->prepare("UPDATE ordensalida SET Total = :total WHERE Id = :id");
            $stmtUpdateSalida->execute([':total' => $totalTransferencia, ':id' => $ordenSalidaId]);
            
            $stmtUpdateEntrada = $conn->prepare("UPDATE ordenentrada SET Total = :total WHERE Id = :id");
            $stmtUpdateEntrada->execute([':total' => $totalTransferencia, ':id' => $ordenEntradaId]);
            
            // Confirmar transacción
            $conn->commit();
            
            SessionHelper::setFlash('success', 'Transferencia procesada exitosamente. Salida #' . $ordenSalidaId . ' - Entrada #' . $ordenEntradaId);
            header('Location: /vetalmacen/public/index.php?url=stock');
            
        } catch (Exception $e) {
            $conn->rollBack();
            SessionHelper::setFlash('danger', 'Error al procesar transferencia: ' . $e->getMessage());
            header('Location: /vetalmacen/public/index.php?url=stock/transferencias');
        }
        
        exit();
    }
}