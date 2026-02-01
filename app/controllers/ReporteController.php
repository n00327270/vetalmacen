<?php
require_once __DIR__ . '/../models/OrdenEntrada.php';
require_once __DIR__ . '/../models/OrdenSalida.php';
require_once __DIR__ . '/../models/DetalleOrdenEntrada.php';
require_once __DIR__ . '/../models/DetalleOrdenSalida.php';
require_once __DIR__ . '/../models/StockSucursal.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Sucursal.php';
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';

class ReporteController {
    
    /**
     * Vista principal de reportes (dashboard de reportes)
     */
    public function index() {
        AuthHelper::requireAuth();
        require_once __DIR__ . '/../views/reportes/index.php';
    }

    // ========================================
    // REPORTES DE MOVIMIENTOS
    // ========================================

    /**
     * Reporte de movimientos (entradas y salidas)
     */
    public function movimientos() {
        AuthHelper::requireAuth();
        
        // Obtener parámetros de filtro
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes actual
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d'); // Hoy
        $sucursalId = $_GET['sucursal_id'] ?? '';
        $tipoMovimiento = $_GET['tipo_movimiento'] ?? ''; // 'entrada' o 'salida'
        
        // Obtener sucursales para el filtro
        $sucursalModel = new Sucursal();
        $sucursales = $sucursalModel->getAllActive();
        
        // Construir consulta unificada de movimientos
        $movimientos = $this->getMovimientos($fechaInicio, $fechaFin, $sucursalId, $tipoMovimiento);
        
        // Calcular totales
        $totales = $this->calcularTotalesMovimientos($movimientos);
        
        require_once __DIR__ . '/../views/reportes/movimientos.php';
    }

    /**
     * Obtener movimientos combinados (entradas y salidas)
     */
    private function getMovimientos($fechaInicio, $fechaFin, $sucursalId = '', $tipoMovimiento = '') {
        $database = new Database();
        $conn = $database->getConnection();
        
        $movimientos = [];
        
        // Obtener ENTRADAS si no se filtra por salidas
        if ($tipoMovimiento !== 'salida') {
            $queryEntradas = "SELECT 
                'Entrada' as TipoMovimiento,
                oe.Id as OrdenId,
                oe.Fecha,
                oe.SucursalId,
                s.Sede as Sucursal,
                p.RazonSocial as Referencia,
                u.Username as Usuario,
                doe.ProductoId,
                prod.Codigo as ProductoCodigo,
                prod.Nombre as ProductoNombre,
                doe.Cantidad,
                doe.PrecioUnitario,
                doe.SubTotal,
                oe.Estado
            FROM ordenentrada oe
            INNER JOIN detalleordenentrada doe ON oe.Id = doe.OrdenEntradaId
            INNER JOIN sucursal s ON oe.SucursalId = s.Id
            INNER JOIN proveedor p ON oe.ProveedorId = p.Id
            INNER JOIN usuario u ON oe.UsuarioId = u.Id
            INNER JOIN producto prod ON doe.ProductoId = prod.Id
            WHERE DATE(oe.Fecha) BETWEEN :fecha_inicio AND :fecha_fin";
            
            if ($sucursalId) {
                $queryEntradas .= " AND oe.SucursalId = :sucursal_id";
            }
            
            $queryEntradas .= " ORDER BY oe.Fecha DESC, oe.Id DESC";
            
            $stmt = $conn->prepare($queryEntradas);
            $stmt->bindParam(':fecha_inicio', $fechaInicio);
            $stmt->bindParam(':fecha_fin', $fechaFin);
            if ($sucursalId) {
                $stmt->bindParam(':sucursal_id', $sucursalId);
            }
            $stmt->execute();
            $entradas = $stmt->fetchAll();
            
            $movimientos = array_merge($movimientos, $entradas);
        }
        
        // Obtener SALIDAS si no se filtra por entradas
        if ($tipoMovimiento !== 'entrada') {
            $querySalidas = "SELECT 
                CONCAT('Salida - ', os.TipoSalida) as TipoMovimiento,
                os.Id as OrdenId,
                os.Fecha,
                os.SucursalId,
                s.Sede as Sucursal,
                os.TipoSalida as Referencia,
                u.Username as Usuario,
                dos.ProductoId,
                prod.Codigo as ProductoCodigo,
                prod.Nombre as ProductoNombre,
                dos.Cantidad,
                dos.PrecioUnitario,
                dos.SubTotal,
                os.Estado
            FROM ordensalida os
            INNER JOIN detalleordensalida dos ON os.Id = dos.OrdenSalidaId
            INNER JOIN usuario u ON os.UsuarioId = u.Id
            LEFT JOIN sucursal s ON os.SucursalId = s.Id
            INNER JOIN producto prod ON dos.ProductoId = prod.Id
            WHERE DATE(os.Fecha) BETWEEN :fecha_inicio AND :fecha_fin";
            
            if ($sucursalId) {
                $querySalidas .= " AND os.SucursalId = :sucursal_id";
            }
            
            $querySalidas .= " ORDER BY os.Fecha DESC, os.Id DESC";
            
            $stmt = $conn->prepare($querySalidas);
            $stmt->bindParam(':fecha_inicio', $fechaInicio);
            $stmt->bindParam(':fecha_fin', $fechaFin);
            if ($sucursalId) {
                $stmt->bindParam(':sucursal_id', $sucursalId);
            }
            $stmt->execute();
            $salidas = $stmt->fetchAll();
            
            $movimientos = array_merge($movimientos, $salidas);
        }
        
        // Ordenar por fecha descendente
        usort($movimientos, function($a, $b) {
            return strtotime($b['Fecha']) - strtotime($a['Fecha']);
        });
        
        return $movimientos;
    }

    /**
     * Calcular totales de movimientos
     */
    private function calcularTotalesMovimientos($movimientos) {
        $totales = [
            'total_entradas' => 0,
            'total_salidas' => 0,
            'cantidad_entradas' => 0,
            'cantidad_salidas' => 0,
            'valor_entradas' => 0,
            'valor_salidas' => 0
        ];
        
        foreach ($movimientos as $mov) {
            if ($mov['TipoMovimiento'] === 'Entrada') {
                $totales['cantidad_entradas'] += $mov['Cantidad'];
                $totales['valor_entradas'] += $mov['SubTotal'];
                $totales['total_entradas']++;
            } else {
                $totales['cantidad_salidas'] += $mov['Cantidad'];
                $totales['valor_salidas'] += $mov['SubTotal'];
                $totales['total_salidas']++;
            }
        }
        
        return $totales;
    }

    // ========================================
    // REPORTES DE STOCK
    // ========================================

    /**
     * Reporte de stock por sucursal
     */
    public function stock() {
        AuthHelper::requireAuth();
        
        // Obtener parámetros de filtro
        $sucursalId = $_GET['sucursal_id'] ?? '';
        $categoriaId = $_GET['categoria_id'] ?? '';
        $stockMinimo = $_GET['stock_minimo'] ?? '';
        $soloStockBajo = isset($_GET['solo_stock_bajo']) ? true : false;
        
        // Obtener datos para filtros
        $sucursalModel = new Sucursal();
        $sucursales = $sucursalModel->getAllActive();
        
        $database = new Database();
        $conn = $database->getConnection();
        
        // Obtener categorías
        $queryCategorias = "SELECT * FROM categoria ORDER BY Nombre ASC";
        $stmt = $conn->prepare($queryCategorias);
        $stmt->execute();
        $categorias = $stmt->fetchAll();
        
        // Construir consulta de stock
        $stock = $this->getStockConFiltros($sucursalId, $categoriaId, $stockMinimo, $soloStockBajo);
        
        // Calcular totales
        $totales = $this->calcularTotalesStock($stock);
        
        require_once __DIR__ . '/../views/reportes/stock.php';
    }

    /**
     * Obtener stock con filtros aplicados
     */
    private function getStockConFiltros($sucursalId = '', $categoriaId = '', $stockMinimo = '', $soloStockBajo = false) {
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "SELECT 
            ss.*,
            p.Codigo,
            p.Nombre as ProductoNombre,
            p.Marca,
            p.Precio,
            p.ImagenUrl,
            s.Sede as SucursalNombre,
            sc.Nombre as SubCategoriaNombre,
            c.Id as CategoriaId,
            c.Nombre as CategoriaNombre,
            (ss.Stock * p.Precio) as ValorStock
        FROM stock_sucursal ss
        INNER JOIN producto p ON ss.ProductoId = p.Id
        INNER JOIN sucursal s ON ss.SucursalId = s.Id
        INNER JOIN subcategoria sc ON p.SubCategoriaId = sc.Id
        INNER JOIN categoria c ON sc.CategoriaId = c.Id
        WHERE 1=1";
        
        $params = [];
        
        if ($sucursalId) {
            $query .= " AND ss.SucursalId = :sucursal_id";
            $params[':sucursal_id'] = $sucursalId;
        }
        
        if ($categoriaId) {
            $query .= " AND c.Id = :categoria_id";
            $params[':categoria_id'] = $categoriaId;
        }
        
        if ($stockMinimo !== '') {
            $query .= " AND ss.Stock <= :stock_minimo";
            $params[':stock_minimo'] = $stockMinimo;
        }
        
        if ($soloStockBajo) {
            $query .= " AND ss.Stock <= 10"; // Definir como stock bajo
        }
        
        $query .= " ORDER BY s.Sede ASC, p.Nombre ASC";
        
        $stmt = $conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Calcular totales de stock
     */
    private function calcularTotalesStock($stock) {
        $totales = [
            'total_productos' => count($stock),
            'total_unidades' => 0,
            'valor_total' => 0,
            'productos_sin_stock' => 0,
            'productos_stock_bajo' => 0
        ];
        
        foreach ($stock as $item) {
            $totales['total_unidades'] += $item['Stock'];
            $totales['valor_total'] += $item['ValorStock'];
            
            if ($item['Stock'] == 0) {
                $totales['productos_sin_stock']++;
            } elseif ($item['Stock'] <= 10) {
                $totales['productos_stock_bajo']++;
            }
        }
        
        return $totales;
    }

    // ========================================
    // INVENTARIO VALORIZADO
    // ========================================

    /**
     * Reporte de inventario valorizado
     */
    public function inventarioValorizado() {
        AuthHelper::requireAuth();
        
        $sucursalId = $_GET['sucursal_id'] ?? '';
        
        $sucursalModel = new Sucursal();
        $sucursales = $sucursalModel->getAllActive();
        
        $database = new Database();
        $conn = $database->getConnection();
        
        // Obtener inventario valorizado
        $query = "SELECT 
            c.Nombre as Categoria,
            sc.Nombre as Subcategoria,
            p.Codigo,
            p.Nombre as Producto,
            p.Marca,
            p.Precio,
            s.Sede as Sucursal,
            ss.Stock,
            (ss.Stock * p.Precio) as ValorStock
        FROM stock_sucursal ss
        INNER JOIN producto p ON ss.ProductoId = p.Id
        INNER JOIN sucursal s ON ss.SucursalId = s.Id
        INNER JOIN subcategoria sc ON p.SubCategoriaId = sc.Id
        INNER JOIN categoria c ON sc.CategoriaId = c.Id";
        
        if ($sucursalId) {
            $query .= " WHERE ss.SucursalId = :sucursal_id";
        }
        
        $query .= " ORDER BY c.Nombre, p.Nombre";
        
        $stmt = $conn->prepare($query);
        if ($sucursalId) {
            $stmt->bindParam(':sucursal_id', $sucursalId);
        }
        $stmt->execute();
        $inventario = $stmt->fetchAll();
        
        // Calcular totales
        $totalValor = 0;
        $totalUnidades = 0;
        foreach ($inventario as $item) {
            $totalValor += $item['ValorStock'];
            $totalUnidades += $item['Stock'];
        }
        
        require_once __DIR__ . '/../views/reportes/inventario_valorizado.php';
    }

    // ========================================
    // ESTADÍSTICAS GENERALES
    // ========================================

    /**
     * Dashboard de estadísticas
     */
    public function estadisticas() {
        AuthHelper::requireAuth();
        
        $database = new Database();
        $conn = $database->getConnection();
        
        // Estadísticas generales
        $stats = [];
        
        // Total de productos
        $stmt = $conn->query("SELECT COUNT(*) as total FROM producto");
        $stats['total_productos'] = $stmt->fetch()['total'];
        
        // Total de sucursales activas
        $stmt = $conn->query("SELECT COUNT(*) as total FROM sucursal WHERE Activo = 1");
        $stats['total_sucursales'] = $stmt->fetch()['total'];
        
        // Valor total del inventario
        $stmt = $conn->query("SELECT SUM(ss.Stock * p.Precio) as valor_total 
                              FROM stock_sucursal ss 
                              INNER JOIN producto p ON ss.ProductoId = p.Id");
        $stats['valor_inventario'] = $stmt->fetch()['valor_total'] ?? 0;
        
        // Ventas del mes actual
        $stmt = $conn->query("SELECT SUM(Total) as total 
                              FROM ordensalida 
                              WHERE TipoSalida = 'Venta' 
                                AND Estado = 'Procesado'
                                AND MONTH(Fecha) = MONTH(CURRENT_DATE())
                                AND YEAR(Fecha) = YEAR(CURRENT_DATE())");
        $stats['ventas_mes'] = $stmt->fetch()['total'] ?? 0;
        
        // Productos con stock bajo (<=10)
        $stmt = $conn->query("SELECT COUNT(*) as total FROM stock_sucursal WHERE Stock <= 10 AND Stock > 0");
        $stats['productos_stock_bajo'] = $stmt->fetch()['total'];
        
        // Productos sin stock
        $stmt = $conn->query("SELECT COUNT(*) as total FROM stock_sucursal WHERE Stock = 0");
        $stats['productos_sin_stock'] = $stmt->fetch()['total'];
        
        // Ventas por mes (últimos 6 meses)
        $query = "SELECT 
            DATE_FORMAT(Fecha, '%Y-%m') as mes,
            SUM(Total) as total
        FROM ordensalida
        WHERE TipoSalida = 'Venta'
          AND Estado = 'Procesado'
          AND Fecha >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(Fecha, '%Y-%m')
        ORDER BY mes ASC";
        $stmt = $conn->query($query);
        $ventasPorMes = $stmt->fetchAll();
        
        // Stock por sucursal
        $query = "SELECT 
            s.Sede,
            SUM(ss.Stock) as total_unidades,
            SUM(ss.Stock * p.Precio) as valor_total
        FROM stock_sucursal ss
        INNER JOIN sucursal s ON ss.SucursalId = s.Id
        INNER JOIN producto p ON ss.ProductoId = p.Id
        WHERE s.Activo = 1
        GROUP BY s.Id
        ORDER BY s.Sede";
        $stmt = $conn->query($query);
        $stockPorSucursal = $stmt->fetchAll();
        
        require_once __DIR__ . '/../views/reportes/estadisticas.php';
    }

    // ========================================
    // EXPORTACIÓN A EXCEL
    // ========================================

    /**
     * Exportar reporte de stock a Excel
     * Requiere: composer require phpoffice/phpspreadsheet
     */
    public function exportarStockExcel() {
        AuthHelper::requireAuth();
        
        // Verificar si PhpSpreadsheet está instalado
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            SessionHelper::setFlash('danger', 'PhpSpreadsheet no está instalado. Ejecuta: composer require phpoffice/phpspreadsheet');
            header('Location: /vetalmacen/public/index.php?url=reportes/stock');
            exit();
        }
        
        $sucursalId = $_GET['sucursal_id'] ?? '';
        $categoriaId = $_GET['categoria_id'] ?? '';
        
        // Obtener datos
        $stock = $this->getStockConFiltros($sucursalId, $categoriaId);
        
        // Crear Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $sheet->setCellValue('A1', 'REPORTE DE STOCK - VETALMACEN');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Fecha de generación
        $sheet->setCellValue('A2', 'Fecha de generación: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:I2');
        
        // Encabezados
        $row = 4;
        $headers = ['Sucursal', 'Categoría', 'Subcategoría', 'Código', 'Producto', 'Marca', 'Stock', 'Precio', 'Valor Total'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4472C4');
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('FFFFFF');
            $col++;
        }
        
        // Datos
        $row = 5;
        $totalValor = 0;
        foreach ($stock as $item) {
            $sheet->setCellValue('A' . $row, $item['SucursalNombre']);
            $sheet->setCellValue('B' . $row, $item['CategoriaNombre']);
            $sheet->setCellValue('C' . $row, $item['SubCategoriaNombre']);
            $sheet->setCellValue('D' . $row, $item['Codigo']);
            $sheet->setCellValue('E' . $row, $item['ProductoNombre']);
            $sheet->setCellValue('F' . $row, $item['Marca']);
            $sheet->setCellValue('G' . $row, $item['Stock']);
            $sheet->setCellValue('H' . $row, $item['Precio']);
            $sheet->setCellValue('I' . $row, $item['ValorStock']);
            
            // Formato de números
            $sheet->getStyle('H' . $row)->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
            $sheet->getStyle('I' . $row)->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
            
            // Color de fondo según stock
            if ($item['Stock'] == 0) {
                $sheet->getStyle('G' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FF0000');
            } elseif ($item['Stock'] <= 10) {
                $sheet->getStyle('G' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFC000');
            }
            
            $totalValor += $item['ValorStock'];
            $row++;
        }
        
        // Totales
        $sheet->setCellValue('H' . $row, 'TOTAL:');
        $sheet->getStyle('H' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('I' . $row, $totalValor);
        $sheet->getStyle('I' . $row)->getFont()->setBold(true);
        $sheet->getStyle('I' . $row)->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
        
        // Ajustar ancho de columnas
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Bordes
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:I' . $row)->applyFromArray($styleArray);
        
        // Descargar
        $filename = 'reporte_stock_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }

    /**
     * Exportar reporte de movimientos a Excel
     */
    public function exportarMovimientosExcel() {
        AuthHelper::requireAuth();
        
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            SessionHelper::setFlash('danger', 'PhpSpreadsheet no está instalado. Ejecuta: composer require phpoffice/phpspreadsheet');
            header('Location: /vetalmacen/public/index.php?url=reportes/movimientos');
            exit();
        }
        
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $sucursalId = $_GET['sucursal_id'] ?? '';
        $tipoMovimiento = $_GET['tipo_movimiento'] ?? '';
        
        // Obtener datos
        $movimientos = $this->getMovimientos($fechaInicio, $fechaFin, $sucursalId, $tipoMovimiento);
        
        // Crear Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Título
        $sheet->setCellValue('A1', 'REPORTE DE MOVIMIENTOS - VETALMACEN');
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Período
        $sheet->setCellValue('A2', 'Período: ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin)));
        $sheet->mergeCells('A2:K2');
        
        // Encabezados
        $row = 4;
        $headers = ['Fecha', 'Tipo', 'Orden', 'Sucursal', 'Referencia', 'Usuario', 'Código', 'Producto', 'Cantidad', 'Precio Unit.', 'Subtotal'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('5B9BD5');
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('FFFFFF');
            $col++;
        }
        
        // Datos
        $row = 5;
        foreach ($movimientos as $mov) {
            $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($mov['Fecha'])));
            $sheet->setCellValue('B' . $row, $mov['TipoMovimiento']);
            $sheet->setCellValue('C' . $row, $mov['OrdenId']);
            $sheet->setCellValue('D' . $row, $mov['Sucursal']);
            $sheet->setCellValue('E' . $row, $mov['Referencia']);
            $sheet->setCellValue('F' . $row, $mov['Usuario']);
            $sheet->setCellValue('G' . $row, $mov['ProductoCodigo']);
            $sheet->setCellValue('H' . $row, $mov['ProductoNombre']);
            $sheet->setCellValue('I' . $row, $mov['Cantidad']);
            $sheet->setCellValue('J' . $row, $mov['PrecioUnitario']);
            $sheet->setCellValue('K' . $row, $mov['SubTotal']);
            
            $sheet->getStyle('J' . $row . ':K' . $row)->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
            
            // Color según tipo
            if ($mov['TipoMovimiento'] === 'Entrada') {
                $sheet->getStyle('B' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('C6EFCE');
            } else {
                $sheet->getStyle('B' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFC7CE');
            }
            
            $row++;
        }
        
        // Ajustar columnas
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Bordes
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:K' . ($row - 1))->applyFromArray($styleArray);
        
        // Descargar
        $filename = 'reporte_movimientos_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}