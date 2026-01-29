<?php
$pageTitle = 'Detalle Orden de Entrada';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=ordenes_entrada">Órdenes de Entrada</a></li>
            <li class="breadcrumb-item active">Detalle #<?php echo $orden['Id']; ?></li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-arrow-down-circle"></i> Orden de Entrada #<?php echo $orden['Id']; ?></h2>
        </div>
        <div class="col-auto">
            <a href="/vetalmacen/public/index.php?url=ordenes_entrada" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información de la orden -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Información General</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Estado:</th>
                            <td>
                                <?php
                                $badgeClass = match($orden['Estado']) {
                                    'Pendiente' => 'bg-warning text-dark',
                                    'Recibido' => 'bg-success',
                                    'Cancelado' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo $orden['Estado']; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Fecha:</th>
                            <td><?php echo date('d/m/Y H:i', strtotime($orden['Fecha'])); ?></td>
                        </tr>
                        <tr>
                            <th>Proveedor:</th>
                            <td><strong><?php echo htmlspecialchars($orden['ProveedorNombre']); ?></strong></td>
                        </tr>
                        <tr>
                            <th>RUC:</th>
                            <td><?php echo htmlspecialchars($orden['RUC']); ?></td>
                        </tr>
                        <tr>
                            <th>Sucursal Destino:</th>
                            <td><?php echo htmlspecialchars($orden['SucursalNombre']); ?></td>
                        </tr>
                        <tr>
                            <th>Usuario:</th>
                            <td><?php echo htmlspecialchars($orden['UsuarioNombre']); ?></td>
                        </tr>
                        <tr>
                            <th>Total:</th>
                            <td class="text-success"><strong>S/ <?php echo number_format($orden['Total'], 2); ?></strong></td>
                        </tr>
                    </table>

                    <?php if ($orden['Observacion']): ?>
                    <div class="mt-3">
                        <strong>Observaciones:</strong>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($orden['Observacion'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cambiar estado -->
            <?php if (AuthHelper::hasAnyRole(['Administrador', 'Almacenero'])): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">Cambiar Estado</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="/vetalmacen/public/index.php?url=ordenes_entrada/cambiarEstado">
                        <input type="hidden" name="orden_id" value="<?php echo $orden['Id']; ?>">
                        <div class="mb-3">
                            <select class="form-select" name="estado" required>
                                <option value="Pendiente" <?php echo $orden['Estado'] == 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="Recibido" <?php echo $orden['Estado'] == 'Recibido' ? 'selected' : ''; ?>>Recibido</option>
                                <option value="Cancelado" <?php echo $orden['Estado'] == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-check-circle"></i> Actualizar Estado
                        </button>
                    </form>
                    <div class="alert alert-info mt-3 mb-0">
                        <small>
                            <i class="bi bi-info-circle"></i> 
                            Al marcar como "Recibido", el stock se actualizará automáticamente.
                        </small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Detalles de productos -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Productos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Imagen</th>
                                    <th>Producto</th>
                                    <th>Código</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio Unit.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalles as $detalle): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo $detalle['ImagenUrl'] ?? '/vetalmacen/public/images/placeholder.png'; ?>" 
                                             alt="<?php echo htmlspecialchars($detalle['ProductoNombre']); ?>"
                                             class="img-thumbnail" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($detalle['ProductoNombre']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($detalle['Marca']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($detalle['Codigo']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?php echo number_format($detalle['Cantidad']); ?></span>
                                    </td>
                                    <td class="text-end">S/ <?php echo number_format($detalle['PrecioUnitario'], 2); ?></td>
                                    <td class="text-end"><strong>S/ <?php echo number_format($detalle['SubTotal'], 2); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="5" class="text-end">TOTAL:</th>
                                    <th class="text-end text-success fs-5">S/ <?php echo number_format($orden['Total'], 2); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>