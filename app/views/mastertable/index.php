<?php
$pageTitle = 'MasterTable - Catálogos del Sistema';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
require_once __DIR__ . '/../../../helpers/PermisoHelper.php';  // ⭐ AGREGAR
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">MasterTable</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-database"></i> Catálogos del Sistema (MasterTable)</h2>
            <p class="text-muted">Gestión de catálogos configurables jerárquicos</p>
        </div>
        <div class="col-auto">
            <!-- 🔒 PROTECCIÓN -->
            <?php if (PermisoHelper::tienePermiso('mastertable/crear')): ?>
            <a href="/vetalmacen/public/index.php?url=mastertable/crear" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Catálogo Padre
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($arbol)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No hay catálogos creados. 
            <?php if (PermisoHelper::tienePermiso('mastertable/crear')): ?>
                <a href="/vetalmacen/public/index.php?url=mastertable/crear">Crear el primero</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($arbol as $padre): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">
                            <i class="bi bi-folder"></i> 
                            <?php echo $padre['IdMasterTable']; ?> - <?php echo htmlspecialchars($padre['Name']); ?>
                            <span class="badge bg-light text-dark ms-2"><?php echo count($padre['hijos']); ?> hijos</span>
                            <?php if ($padre['States'] == 0): ?>
                                <span class="badge bg-danger ms-2">Inactivo</span>
                            <?php endif; ?>
                        </h5>
                        <?php if (!empty($padre['Description'])): ?>
                        <small><?php echo htmlspecialchars($padre['Description']); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-auto">
                        <!-- 🔒 EDITAR PADRE -->
                        <?php if (PermisoHelper::tienePermiso('mastertable/editar')): ?>
                        <a href="/vetalmacen/public/index.php?url=mastertable/editar/<?php echo $padre['IdMasterTable']; ?>" 
                        class="btn btn-sm btn-light" title="Editar padre">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php endif; ?>
                        
                        <!-- 🔒 TOGGLE ESTADO -->
                        <?php if (PermisoHelper::tienePermiso('mastertable/toggleEstado')): ?>
                        <a href="/vetalmacen/public/index.php?url=mastertable/toggleEstado/<?php echo $padre['IdMasterTable']; ?>" 
                        class="btn btn-sm btn-light" 
                        onclick="return confirm('¿Cambiar estado de este catálogo?')"
                        title="Activar/Desactivar">
                            <i class="bi bi-toggle-<?php echo $padre['States'] == 1 ? 'on' : 'off'; ?>"></i>
                        </a>
                        <?php endif; ?>
                        
                        <button class="btn btn-sm btn-light" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse<?php echo $padre['IdMasterTable']; ?>">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div id="collapse<?php echo $padre['IdMasterTable']; ?>" class="collapse show">
                <div class="card-body">
                    <?php if (empty($padre['hijos'])): ?>
                        <p class="text-muted mb-0">
                            <i class="bi bi-inbox"></i> No hay registros hijos
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Valor</th>
                                        <th>Descripción</th>
                                        <th>Orden</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($padre['hijos'] as $hijo): ?>
                                    <tr class="<?php echo $hijo['States'] == 0 ? 'table-secondary' : ''; ?>">
                                        <td><?php echo $hijo['IdMasterTable']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($hijo['Name']); ?></strong></td>
                                        <td>
                                            <?php if (!empty($hijo['Value'])): ?>
                                                <code><?php echo htmlspecialchars($hijo['Value']); ?></code>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars(substr($hijo['Description'] ?? '', 0, 50)); ?></small>
                                        </td>
                                        <td><?php echo $hijo['Order']; ?></td>
                                        <td>
                                            <?php if ($hijo['States'] == 1): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- 🔒 EDITAR HIJO -->
                                            <?php if (PermisoHelper::tienePermiso('mastertable/editar')): ?>
                                            <a href="/vetalmacen/public/index.php?url=mastertable/editar/<?php echo $hijo['IdMasterTable']; ?>" 
                                            class="btn btn-sm btn-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php endif; ?>
                                            
                                            <!-- 🔒 TOGGLE ESTADO HIJO -->
                                            <?php if (PermisoHelper::tienePermiso('mastertable/toggleEstado')): ?>
                                            <a href="/vetalmacen/public/index.php?url=mastertable/toggleEstado/<?php echo $hijo['IdMasterTable']; ?>" 
                                            class="btn btn-sm btn-<?php echo $hijo['States'] == 1 ? 'secondary' : 'success'; ?>" 
                                            onclick="return confirm('¿Cambiar estado?')"
                                            title="<?php echo $hijo['States'] == 1 ? 'Desactivar' : 'Activar'; ?>">
                                                <i class="bi bi-toggle-<?php echo $hijo['States'] == 1 ? 'on' : 'off'; ?>"></i>
                                            </a>
                                            <?php endif; ?>
                                            
                                            <!-- 🔒 ELIMINAR HIJO -->
                                            <?php if (PermisoHelper::tienePermiso('mastertable/eliminar')): ?>
                                            <a href="/vetalmacen/public/index.php?url=mastertable/eliminar/<?php echo $hijo['IdMasterTable']; ?>" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="return confirm('¿Está seguro de eliminar este registro?')"
                                            title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <!-- 🔒 AGREGAR HIJO -->
                    <?php if (PermisoHelper::tienePermiso('mastertable/crear')): ?>
                    <div class="text-center mt-3">
                        <a href="/vetalmacen/public/index.php?url=mastertable/crear&parent_id=<?php echo $padre['IdMasterTable']; ?>" 
                        class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> Agregar hijo a "<?php echo htmlspecialchars($padre['Name']); ?>"
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>