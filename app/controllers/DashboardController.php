<?php
/**
 * DashboardController
 * Panel principal del sistema
 * Fecha: 2026-01-23
 */

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/OrdenEntrada.php';
require_once __DIR__ . '/../models/OrdenSalida.php';
require_once __DIR__ . '/../models/StockSucursal.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';

class DashboardController {
    
    /**
     * Página principal del dashboard
     */
    public function index() {
        AuthHelper::requireAuth();
        
        $user = SessionHelper::getUser();
        
        // Obtener estadísticas generales
        $productoModel = new Producto();
        $ordenEntradaModel = new OrdenEntrada();
        $ordenSalidaModel = new OrdenSalida();
        $stockModel = new StockSucursal();
        
        // Contar productos
        $totalProductos = count($productoModel->getAll());
        
        // Obtener órdenes recientes
        $ordenesEntradaRecientes = $ordenEntradaModel->getAll();
        $ordenesEntradaRecientes = array_slice($ordenesEntradaRecientes, 0, 5);
        
        $ordenesSalidaRecientes = $ordenSalidaModel->getAll();
        $ordenesSalidaRecientes = array_slice($ordenesSalidaRecientes, 0, 5);
        
        // Productos con stock bajo
        $stockBajo = $stockModel->getStockBajo(10);
        
        // Estadísticas del mes actual
        $mesActual = date('m');
        $añoActual = date('Y');
        
        $totalEntradasMes = $ordenEntradaModel->getTotalPorMes($añoActual, $mesActual);
        $totalVentasMes = $ordenSalidaModel->getTotalVentasPorMes($añoActual, $mesActual);
        
        // Datos específicos por rol
        $data = [
            'totalProductos' => $totalProductos,
            'ordenesEntradaRecientes' => $ordenesEntradaRecientes,
            'ordenesSalidaRecientes' => $ordenesSalidaRecientes,
            'stockBajo' => $stockBajo,
            'totalEntradasMes' => $totalEntradasMes,
            'totalVentasMes' => $totalVentasMes,
            'user' => $user
        ];
        
        // Cargar vista
        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}