<?php
$pageTitle = 'Reporte de Movimientos';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=reportes">Reportes</a></li>
            <li class="breadcrumb-item active">Movimientos</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-arrow-left-right"></i> Reporte de Movimientos</h2>
        </div>
        <div class="col-auto">
            <a href="/vetalmacen/public/index.php?url=reportes/exportarMovimientosExcel<?php echo !empty($_GET) ? '&' . http_build_query($_GET) : ''; ?>" 
               class="btn btn-success" target="_blank">
                <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="/vetalmacen/public/index.php">
                <input type="hidden" name="url" value="reportes/movimientos">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                               value="<?php echo $_GET['fecha_inicio'] ?? date('Y-m-01'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                               value="<?php echo $_GET['fecha_fin'] ?? date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="sucursal_id" class="form-label">Sucursal</label>
                        <select class="form-select" id="sucursal_id" name="sucursal_id">
                            <option value="">Todas las sucursales</option>
                            <?php foreach ($sucursales as $s): ?>
                            <option value="<?php echo $s['Id']; ?>" <?php echo (isset($_GET['sucursal_id']) && $_GET['sucursal_id'] == $s['Id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['Sede']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="tipo_movimiento" class="form-label">Tipo de Movimiento</label>
                        <select class="form-select" id="tipo_movimiento" name="tipo_movimiento">
                            <option value="">Todos</option>
                            <option value="entrada" <?php echo (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'entrada') ? 'selected' : ''; ?>>Entradas</option>
                            <option value="salida" <?php echo (isset($_GET['tipo_movimiento']) && $_GET['tipo_movimiento'] == 'salida') ? 'selected' : ''; ?>>Salidas</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                        <a href="/vetalmacen/public/index.php?url=reportes/movimientos" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Totales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Entradas</h6>
                    <h3 class="text-success"><?php echo number_format($totales['total_entradas']); ?></h3>
                    <small class="text-muted"><?php echo number_format($totales['cantidad_entradas']); ?> unidades</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Salidas</h6>
                    <h3 class="text-danger"><?php echo number_format($totales['total_salidas']); ?></h3>
                    <small class="text-muted"><?php echo number_format($totales['cantidad_salidas']); ?> unidades</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Valor Entradas</h6>
                    <h3 class="text-success">S/ <?php echo number_format($totales['valor_entradas'], 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Valor Salidas</h6>
                    <h3 class="text-danger">S/ <?php echo number_format($totales['valor_salidas'], 2); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Orden</th>
                            <th>Sucursal</th>
                            <th>Producto</th>
                            <th>Referencia</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">P. Unitario</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($movimientos)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay movimientos en el período seleccionado</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($movimientos as $mov): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($mov['Fecha'])); ?></td>
                                <td>
                                    <?php if ($mov['TipoMovimiento'] === 'Entrada'): ?>
                                        <span class="badge bg-success"><i class="bi bi-arrow-down-circle"></i> Entrada</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-arrow-up-circle"></i> <?php echo $mov['TipoMovimiento']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted">#<?php echo $mov['OrdenId']; ?></small></td>
                                <td><?php echo htmlspecialchars($mov['Sucursal']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($mov['ProductoNombre']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($mov['ProductoCodigo']); ?></small>
                                </td>
                                <td><small><?php echo htmlspecialchars($mov['Referencia']); ?></small></td>
                                <td class="text-end"><?php echo number_format($mov['Cantidad']); ?></td>
                                <td class="text-end">S/ <?php echo number_format($mov['PrecioUnitario'], 2); ?></td>
                                <td class="text-end"><strong>S/ <?php echo number_format($mov['SubTotal'], 2); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>