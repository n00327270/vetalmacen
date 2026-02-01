<?php
$pageTitle = 'Reporte de Stock';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=reportes">Reportes</a></li>
            <li class="breadcrumb-item active">Stock</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-boxes"></i> Reporte de Stock</h2>
        </div>
        <div class="col-auto">
            <a href="/vetalmacen/public/index.php?url=reportes/exportarStockExcel<?php echo !empty($_GET) ? '&' . http_build_query($_GET) : ''; ?>" 
               class="btn btn-success" target="_blank">
                <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="/vetalmacen/public/index.php">
                <input type="hidden" name="url" value="reportes/stock">
                <div class="row g-3">
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
                        <label for="categoria_id" class="form-label">Categoría</label>
                        <select class="form-select" id="categoria_id" name="categoria_id">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $c): ?>
                            <option value="<?php echo $c['Id']; ?>" <?php echo (isset($_GET['categoria_id']) && $_GET['categoria_id'] == $c['Id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['Nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                        <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" 
                               value="<?php echo $_GET['stock_minimo'] ?? ''; ?>" placeholder="Ej: 10">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="solo_stock_bajo" name="solo_stock_bajo" 
                                   <?php echo isset($_GET['solo_stock_bajo']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="solo_stock_bajo">
                                Solo stock bajo (≤10)
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                        <a href="/vetalmacen/public/index.php?url=reportes/stock" class="btn btn-secondary">
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
                    <h6 class="text-muted">Total Productos</h6>
                    <h3 class="text-primary"><?php echo number_format($totales['total_productos']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Total Unidades</h6>
                    <h3 class="text-info"><?php echo number_format($totales['total_unidades']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Stock Bajo</h6>
                    <h3 class="text-warning"><?php echo number_format($totales['productos_stock_bajo']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted">Sin Stock</h6>
                    <h3 class="text-danger"><?php echo number_format($totales['productos_sin_stock']); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <h6>Valor Total del Inventario</h6>
                    <h2 class="mb-0">S/ <?php echo number_format($totales['valor_total'], 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Stock -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Imagen</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Marca</th>
                            <th>Categoría</th>
                            <th>Sucursal</th>
                            <th class="text-end">Stock</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Valor Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stock)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay productos con los filtros seleccionados</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($stock as $item): 
                                $rowClass = '';
                                if ($item['Stock'] == 0) {
                                    $rowClass = 'table-danger';
                                } elseif ($item['Stock'] <= 10) {
                                    $rowClass = 'table-warning';
                                }
                            ?>
                            <tr class="<?php echo $rowClass; ?>">
                                <td>
                                    <img src="<?php echo $item['ImagenUrl'] ?? '/vetalmacen/public/images/placeholder.png'; ?>" 
                                         alt="<?php echo htmlspecialchars($item['ProductoNombre']); ?>"
                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td><?php echo htmlspecialchars($item['Codigo']); ?></td>
                                <td><strong><?php echo htmlspecialchars($item['ProductoNombre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['Marca']); ?></td>
                                <td>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($item['CategoriaNombre']); ?></span><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($item['SubCategoriaNombre']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($item['SucursalNombre']); ?></td>
                                <td class="text-end">
                                    <?php
                                    $badgeClass = 'bg-success';
                                    if ($item['Stock'] == 0) {
                                        $badgeClass = 'bg-danger';
                                    } elseif ($item['Stock'] <= 10) {
                                        $badgeClass = 'bg-warning text-dark';
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo number_format($item['Stock']); ?></span>
                                </td>
                                <td class="text-end">S/ <?php echo number_format($item['Precio'], 2); ?></td>
                                <td class="text-end"><strong>S/ <?php echo number_format($item['ValorStock'], 2); ?></strong></td>
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