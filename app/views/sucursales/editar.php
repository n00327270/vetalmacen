<?php
$pageTitle = 'Editar Sucursal';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=sucursales">Sucursales</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-pencil"></i> Editar Sucursal</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="/vetalmacen/public/index.php?url=sucursales/actualizar">
                        <input type="hidden" name="id" value="<?php echo $sucursal['Id']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sede" class="form-label">Sede <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sede" name="sede" 
                                       value="<?php echo htmlspecialchars($sucursal['Sede']); ?>" required autofocus>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($sucursal['Telefono'] ?? ''); ?>" placeholder="+51 987 654 321">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                   value="<?php echo htmlspecialchars($sucursal['Direccion']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($sucursal['Email'] ?? ''); ?>" placeholder="sucursal@vetalmacen.com">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="horario_entrega" class="form-label">Horario de Entrega</label>
                                <input type="text" class="form-control" id="horario_entrega" name="horario_entrega" 
                                       value="<?php echo htmlspecialchars($sucursal['HorarioEntrega'] ?? ''); ?>" placeholder="L-V 08:00-18:00">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                       <?php echo $sucursal['Activo'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="activo">
                                    <strong>Sucursal activa</strong>
                                    <div class="form-text">
                                        <?php if ($sucursal['Activo']): ?>
                                            Esta sucursal está activa y disponible para operaciones
                                        <?php else: ?>
                                            <span class="text-danger">Esta sucursal está inactiva. Marca la casilla para activarla</span>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Actualizar
                            </button>
                            <a href="/vetalmacen/public/index.php?url=sucursales" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel informativo -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle text-warning"></i> Información</h5>
                    <p class="card-text">
                        Al editar una sucursal, se actualizará su información en todo el sistema.
                    </p>
                    <hr>
                    <h6><i class="bi bi-exclamation-triangle text-warning"></i> Consideraciones:</h6>
                    <ul class="small mb-3">
                        <li>El stock de productos no se verá afectado</li>
                        <li>Las órdenes asociadas mantendrán el nombre anterior</li>
                        <li>Desactivar una sucursal la ocultará de nuevas operaciones</li>
                    </ul>

                    <?php if (!$sucursal['Activo']): ?>
                    <div class="alert alert-danger mb-0" role="alert">
                        <small>
                            <i class="bi bi-x-circle"></i>
                            <strong>Sucursal Inactiva:</strong> Esta sucursal no está disponible para nuevas operaciones. Para activarla, marca la casilla "Sucursal activa".
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estadísticas de la sucursal -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-bar-chart text-primary"></i> Datos de la Sucursal</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Creada el:</small>
                        <small><strong><?php echo date('d/m/Y', strtotime($sucursal['CreatedAt'])); ?></strong></small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Última actualización:</small>
                        <small><strong><?php echo date('d/m/Y H:i', strtotime($sucursal['UpdatedAt'])); ?></strong></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>