<?php
$pageTitle = 'Inventario Valorizado';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=reportes">Reportes</a></li>
            <li class="breadcrumb-item active">Inventario Valorizado</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-currency-dollar"></i> Inventario Valorizado</h2>
            <p class="text-muted">Valuación del inventario actual</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="/vetalmacen/public/index.php">
                <input type="hidden" name="url" value="reportes/inventarioValorizado">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="sucursal_id" class="form-label">Filtrar por Sucursal</label>
                        <select class="form-select" id="sucursal_id" name="sucursal_id">
                            <option value="">Todas las sucursales</option>
                            <?php foreach ($sucursales as $s): ?>
                            <option value="<?php echo $s['Id']; ?>" <?php echo (isset($_GET['sucursal_id']) && $_GET['sucursal_id'] == $s['Id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['Sede']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">&nbsp;</label><br>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                        <a href="/vetalmacen/public/index.php?url=reportes/inventarioValorizado" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Valor Total del Inventario</h6>
                    <h1 class="display-4 mb-0">S/ <?php echo number_format($totalValor, 2); ?></h1>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <h6>Total de Unidades</h6>
                    <h1 class="display-4 mb-0"><?php echo number_format($totalUnidades); ?></h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Inventario Valorizado -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Categoría</th>
                            <th>Subcategoría</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Marca</th>
                            <th>Sucursal</th>
                            <th class="text-end">Stock</th>
                            <th class="text-end">Precio Unit.</th>
                            <th class="text-end">Valor Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventario)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay productos en inventario</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php 
                            $categoriaActual = '';
                            $subtotalCategoria = 0;
                            foreach ($inventario as $index => $item): 
                                // Verificar cambio de categoría
                                if ($categoriaActual !== '' && $categoriaActual !== $item['Categoria']) {
                                    // Mostrar subtotal de categoría anterior
                                    ?>
                                    <tr class="table-secondary">
                                        <td colspan="8" class="text-end"><strong>Subtotal <?php echo htmlspecialchars($categoriaActual); ?>:</strong></td>
                                        <td class="text-end"><strong>S/ <?php echo number_format($subtotalCategoria, 2); ?></strong></td>
                                    </tr>
                                    <?php
                                    $subtotalCategoria = 0;
                                }
                                $categoriaActual = $item['Categoria'];
                                $subtotalCategoria += $item['ValorStock'];
                            ?>
                            <tr>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($item['Categoria']); ?></span></td>
                                <td><small><?php echo htmlspecialchars($item['Subcategoria']); ?></small></td>
                                <td><?php echo htmlspecialchars($item['Codigo']); ?></td>
                                <td><strong><?php echo htmlspecialchars($item['Producto']); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['Marca']); ?></td>
                                <td><?php echo htmlspecialchars($item['Sucursal']); ?></td>
                                <td class="text-end"><?php echo number_format($item['Stock']); ?></td>
                                <td class="text-end">S/ <?php echo number_format($item['Precio'], 2); ?></td>
                                <td class="text-end"><strong>S/ <?php echo number_format($item['ValorStock'], 2); ?></strong></td>
                            </tr>
                            <?php 
                                // Si es el último elemento, mostrar subtotal de la última categoría
                                if ($index === count($inventario) - 1) {
                                    ?>
                                    <tr class="table-secondary">
                                        <td colspan="8" class="text-end"><strong>Subtotal <?php echo htmlspecialchars($categoriaActual); ?>:</strong></td>
                                        <td class="text-end"><strong>S/ <?php echo number_format($subtotalCategoria, 2); ?></strong></td>
                                    </tr>
                                    <?php
                                }
                            endforeach; 
                            ?>
                            <tr class="table-success">
                                <td colspan="8" class="text-end"><strong class="fs-5">VALOR TOTAL DEL INVENTARIO:</strong></td>
                                <td class="text-end"><strong class="fs-4 text-success">S/ <?php echo number_format($totalValor, 2); ?></strong></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="alert alert-info mt-4" role="alert">
        <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Información</h6>
        <p class="mb-0">
            Este reporte muestra el valor monetario del inventario actual calculado como: 
            <strong>Stock × Precio Unitario</strong>. Los subtotales se agrupan por categoría de producto.
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>