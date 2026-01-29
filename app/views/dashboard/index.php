<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2>
                <i class="bi bi-speedometer2"></i> Dashboard
            </h2>
            <p class="text-muted">Bienvenido, <?php echo htmlspecialchars($user['username']); ?></p>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Productos</h6>
                            <h3 class="mb-0"><?php echo number_format($totalProductos); ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-box-seam text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Stock Bajo</h6>
                            <h3 class="mb-0 text-warning"><?php echo count($stockBajo); ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Entradas del Mes</h6>
                            <h3 class="mb-0 text-success">S/ <?php echo number_format($totalEntradasMes ?? 0, 2); ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-arrow-down-circle text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Ventas del Mes</h6>
                            <h3 class="mb-0 text-info">S/ <?php echo number_format($totalVentasMes ?? 0, 2); ?></h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-cash-coin text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="row g-4">
        <!-- Productos con stock bajo -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle text-warning"></i> Productos con Stock Bajo
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($stockBajo)): ?>
                        <div class="alert alert-success mb-0">
                            <i class="bi bi-check-circle"></i> ¡Excelente! No hay productos con stock bajo.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Sucursal</th>
                                        <th class="text-end">Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stockBajo as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['ProductoNombre']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($item['Codigo']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['SucursalNombre']); ?></td>
                                        <td class="text-end">
                                            <span class="badge bg-warning"><?php echo $item['Stock']; ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="/vetalmacen/public/index.php?url=stock" class="btn btn-sm btn-outline-primary">
                                Ver todo el stock <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Órdenes recientes -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Órdenes Recientes
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="ordenesTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="entradas-tab" data-bs-toggle="tab" data-bs-target="#entradas" type="button" role="tab" aria-controls="entradas" aria-selected="true">
                                Entradas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="salidas-tab" data-bs-toggle="tab" data-bs-target="#salidas" type="button" role="tab" aria-controls="salidas" aria-selected="false">
                                Salidas
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3" id="ordenesTabContent">
                        <!-- Tab Entradas -->
                        <div class="tab-pane fade show active" id="entradas" role="tabpanel" aria-labelledby="entradas-tab">
                            <?php if (empty($ordenesEntradaRecientes)): ?>
                                <p class="text-muted text-center py-3">No hay órdenes de entrada recientes</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Proveedor</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ordenesEntradaRecientes as $orden): ?>
                                            <tr>
                                                <td>
                                                    <a href="/vetalmacen/public/index.php?url=ordenes_entrada/detalle/<?php echo $orden['Id']; ?>">
                                                        #<?php echo $orden['Id']; ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($orden['ProveedorNombre']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($orden['Fecha'])); ?></td>
                                                <td>
                                                    <?php
                                                    $badgeClass = match($orden['Estado']) {
                                                        'Pendiente' => 'bg-warning',
                                                        'Recibido' => 'bg-success',
                                                        'Cancelado' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $orden['Estado']; ?></span>
                                                </td>
                                                <td class="text-end">S/ <?php echo number_format($orden['Total'], 2); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Tab Salidas -->
                        <div class="tab-pane fade" id="salidas" role="tabpanel" aria-labelledby="salidas-tab">
                            <?php if (empty($ordenesSalidaRecientes)): ?>
                                <p class="text-muted text-center py-3">No hay órdenes de salida recientes</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Tipo</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ordenesSalidaRecientes as $orden): ?>
                                            <tr>
                                                <td>
                                                    <a href="/vetalmacen/public/index.php?url=ordenes_salida/detalle/<?php echo $orden['Id']; ?>">
                                                        #<?php echo $orden['Id']; ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($orden['TipoSalida']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($orden['Fecha'])); ?></td>
                                                <td>
                                                    <?php
                                                    $badgeClass = match($orden['Estado']) {
                                                        'Pendiente' => 'bg-warning',
                                                        'Procesado' => 'bg-success',
                                                        'Cancelado' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $orden['Estado']; ?></span>
                                                </td>
                                                <td class="text-end">S/ <?php echo number_format($orden['Total'], 2); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos rápidos -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning-charge"></i> Accesos Rápidos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="/vetalmacen/public/index.php?url=productos/crear" class="btn btn-outline-primary w-100 p-3">
                                <i class="bi bi-plus-circle d-block mb-2" style="font-size: 2rem;"></i>
                                Nuevo Producto
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/vetalmacen/public/index.php?url=ordenes_entrada/crear" class="btn btn-outline-success w-100 p-3">
                                <i class="bi bi-arrow-down-circle d-block mb-2" style="font-size: 2rem;"></i>
                                Nueva Entrada
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/vetalmacen/public/index.php?url=ordenes_salida/crear" class="btn btn-outline-info w-100 p-3">
                                <i class="bi bi-arrow-up-circle d-block mb-2" style="font-size: 2rem;"></i>
                                Nueva Salida
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/vetalmacen/public/index.php?url=reportes" class="btn btn-outline-secondary w-100 p-3">
                                <i class="bi bi-file-earmark-bar-graph d-block mb-2" style="font-size: 2rem;"></i>
                                Ver Reportes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>