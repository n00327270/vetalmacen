<?php
$pageTitle = 'Editar Registro - MasterTable';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=mastertable">MasterTable</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-pencil"></i> Editar: <?php echo htmlspecialchars($registro['Name']); ?></h2>
            <p class="text-muted">ID: <?php echo $registro['IdMasterTable']; ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="/vetalmacen/public/index.php?url=mastertable/actualizar">
                        <input type="hidden" name="id" value="<?php echo $registro['IdMasterTable']; ?>">
                        
                        <!-- Info tipo -->
                        <div class="alert alert-info">
                            <strong>Tipo:</strong> 
                            <?php if ($registro['IdMasterTableParent']): ?>
                                <i class="bi bi-file-earmark"></i> Registro Hijo de 
                                "<strong><?php echo htmlspecialchars($registro['ParentName']); ?></strong>"
                            <?php else: ?>
                                <i class="bi bi-folder"></i> Catálogo Padre
                            <?php endif; ?>
                        </div>

                        <!-- Campos principales -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="<?php echo htmlspecialchars($registro['Name']); ?>"
                                   required 
                                   autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="value" class="form-label">Valor</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="value" 
                                   name="value"
                                   value="<?php echo htmlspecialchars($registro['Value'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3"><?php echo htmlspecialchars($registro['Description'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="order" class="form-label">Orden</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="order" 
                                   name="order" 
                                   value="<?php echo $registro['Order']; ?>"
                                   min="0">
                        </div>

                        <!-- Campos adicionales -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Campos Adicionales</h6>
                                
                                <div class="mb-2">
                                    <label for="additional_one" class="form-label">Additional One</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="additional_one" 
                                           name="additional_one"
                                           value="<?php echo htmlspecialchars($registro['AdditionalOne'] ?? ''); ?>">
                                </div>

                                <div class="mb-2">
                                    <label for="additional_two" class="form-label">Additional Two</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="additional_two" 
                                           name="additional_two"
                                           value="<?php echo htmlspecialchars($registro['AdditionalTwo'] ?? ''); ?>">
                                </div>

                                <div class="mb-0">
                                    <label for="additional_three" class="form-label">Additional Three</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="additional_three" 
                                           name="additional_three"
                                           value="<?php echo htmlspecialchars($registro['AdditionalThree'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="states" 
                                       name="states" 
                                       <?php echo $registro['States'] == 1 ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="states">
                                    Activo
                                </label>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Actualizar
                            </button>
                            <a href="/vetalmacen/public/index.php?url=mastertable" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel de auditorí­a -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-clock-history text-primary"></i> Auditorí­a</h5>
                    
                    <h6 class="mt-3">Creación</h6>
                    <p class="small mb-0">
                        <strong>Usuario:</strong> <?php echo htmlspecialchars($registro['UserNew']); ?><br>
                        <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($registro['DateNew'])); ?>
                    </p>

                    <h6 class="mt-3">Última Edición</h6>
                    <?php if ($registro['UserEdit']): ?>
                    <p class="small mb-0">
                        <strong>Usuario:</strong> <?php echo htmlspecialchars($registro['UserEdit']); ?><br>
                        <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($registro['DateEdit'])); ?>
                    </p>
                    <?php else: ?>
                    <p class="small text-muted mb-0">Nunca editado</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$registro['IdMasterTableParent']): ?>
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-exclamation-triangle text-warning"></i> Advertencia</h6>
                    <p class="small mb-0">
                        Este es un catálogo padre. Si lo desactivas, 
                        sus registros hijos seguirán visibles pero el catálogo 
                        no aparecerá en listados.
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>