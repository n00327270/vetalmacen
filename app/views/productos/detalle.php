<?php
$pageTitle = 'Detalle del Producto';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=productos">Productos</a></li>
            <li class="breadcrumb-item active">Detalle</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-box-seam"></i> Detalle del Producto</h2>
        </div>
        <div class="col-auto">
            <?php if (AuthHelper::hasAnyRole(['Administrador', 'Almacenero'])): ?>
            <a href="/vetalmacen/public/index.php?url=productos/editar/<?php echo $producto['Id']; ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <?php endif; ?>
            <a href="/vetalmacen/public/index.php?url=productos" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información del producto -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <!-- validacion general del front para la url temporal donde te sale si tiene imagen (if) o no(else) (para no sobrecargar la bd) -->
                    <?php if (!empty($producto['ImagenUrlTemp'])): ?>
                        <img src="<?php echo $producto['ImagenUrlTemp']; ?>" 
                             alt="<?php echo htmlspecialchars($producto['Nombre']); ?>" 
                             class="img-fluid rounded mb-3"
                             style="max-height: 300px;">
                        
                        <div class="text-muted small">
                            <i class="bi bi-cloud-check"></i> Imagen en Azure Blob Storage
                            <br><small class="text-warning">URL temporal</small>
                        </div>
                    <?php else: ?>
                        <img src="/vetalmacen/public/images/placeholder.png" 
                             alt="Sin imagen" 
                             class="img-fluid rounded mb-3"
                             style="max-height: 300px;">
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Información General</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Código:</th>
                            <td><strong><?php echo htmlspecialchars($producto['Codigo']); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Marca:</th>
                            <td><?php echo htmlspecialchars($producto['Marca']); ?></td>
                        </tr>
                        <tr>
                            <th>Categoría:</th>
                            <td><?php echo htmlspecialchars($producto['CategoriaNombre']); ?></td>
                        </tr>
                        <tr>
                            <th>Subcategoría:</th>
                            <td><?php echo htmlspecialchars($producto['SubCategoriaNombre']); ?></td>
                        </tr>
                        <tr>
                            <th>Precio:</th>
                            <td class="text-success"><strong>S/ <?php echo number_format($producto['Precio'], 2); ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detalles y stock -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><?php echo htmlspecialchars($producto['Nombre']); ?></h5>
                </div>
                <div class="card-body">
                    <h6 class="text-muted">Descripción</h6>
                    <p><?php echo nl2br(htmlspecialchars($producto['Descripcion'])); ?></p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-building"></i> Stock por Sucursal
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($stockPorSucursal)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No hay stock registrado para este producto.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sucursal</th>
                                        <th>Dirección</th>
                                        <th class="text-end">Stock Disponible</th>
                                        <th class="text-end">Valor en Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalStock = 0;
                                    $totalValor = 0;
                                    foreach ($stockPorSucursal as $stock): 
                                        $totalStock += $stock['Stock'];
                                        $valorStock = $stock['Stock'] * $producto['Precio'];
                                        $totalValor += $valorStock;
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($stock['SucursalNombre']); ?></strong></td>
                                        <td class="text-muted"><?php echo htmlspecialchars($stock['Direccion']); ?></td>
                                        <td class="text-end">
                                            <?php
                                            $badgeClass = 'bg-success';
                                            if ($stock['Stock'] <= 0) {
                                                $badgeClass = 'bg-danger';
                                            } elseif ($stock['Stock'] <= 10) {
                                                $badgeClass = 'bg-warning';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?> fs-6">
                                                <?php echo number_format($stock['Stock']); ?> unidades
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            S/ <?php echo number_format($valorStock, 2); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th class="text-end">
                                            <span class="badge bg-primary fs-6">
                                                <?php echo number_format($totalStock); ?> unidades
                                            </span>
                                        </th>
                                        <th class="text-end">
                                            <strong>S/ <?php echo number_format($totalValor, 2); ?></strong>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>