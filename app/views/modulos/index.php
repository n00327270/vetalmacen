<?php
$pageTitle = 'Gestión de Módulos';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
require_once __DIR__ . '/../../../helpers/PermisoHelper.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Gestión de Módulos</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-puzzle"></i> Gestión de Módulos</h2>
            <p class="text-muted">Administra las secciones, módulos y acciones del sistema</p>
        </div>
        <div class="col-auto">
            <a href="/vetalmacen/public/index.php?url=modulos/crear&nivel=1"
               class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nueva Sección
            </a>
        </div>
    </div>

    <!-- Leyenda de niveles -->
    <div class="row mb-3">
        <div class="col">
            <div class="d-flex gap-3">
                <span class="badge bg-primary fs-6 px-3 py-2">
                    <i class="bi bi-grid-1x2"></i> Nivel 1: Sección
                </span>
                <span class="badge bg-success fs-6 px-3 py-2">
                    <i class="bi bi-folder"></i> Nivel 2: Módulo
                </span>
                <span class="badge bg-secondary fs-6 px-3 py-2">
                    <i class="bi bi-lightning"></i> Nivel 3: Acción
                </span>
            </div>
        </div>
    </div>

    <?php if (empty($arbol)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No hay módulos registrados.
        </div>
    <?php else: ?>

        <?php foreach ($arbol as $seccion): ?>
        <!-- ======================== NIVEL 1: SECCIÓN ======================== -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header <?php echo $seccion['Activo'] ? 'bg-primary' : 'bg-secondary'; ?> text-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">
                            <?php if (!empty($seccion['Icono'])): ?>
                                <i class="bi <?php echo htmlspecialchars($seccion['Icono']); ?>"></i>
                            <?php endif; ?>
                            <span class="badge bg-light text-dark me-2">N1</span>
                            <?php echo htmlspecialchars($seccion['Nombre']); ?>
                            <small class="ms-2 opacity-75">
                                ID: <?php echo $seccion['Id']; ?> | Orden: <?php echo $seccion['Orden']; ?>
                            </small>
                            <?php if (!$seccion['Activo']): ?>
                                <span class="badge bg-danger ms-2">Inactivo</span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="col-auto d-flex gap-1">
                        <!-- Agregar módulo hijo -->
                        <a href="/vetalmacen/public/index.php?url=modulos/crear&nivel=2&padre_id=<?php echo $seccion['Id']; ?>"
                           class="btn btn-sm btn-light" title="Agregar módulo">
                            <i class="bi bi-plus-circle"></i>
                        </a>
                        <!-- Editar sección -->
                        <a href="/vetalmacen/public/index.php?url=modulos/editar/<?php echo $seccion['Id']; ?>"
                           class="btn btn-sm btn-light" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <!-- Toggle estado -->
                        <a href="/vetalmacen/public/index.php?url=modulos/toggleEstado/<?php echo $seccion['Id']; ?>"
                           class="btn btn-sm btn-light"
                           onclick="return confirm('¿Cambiar estado de la sección \'<?php echo htmlspecialchars($seccion['Nombre']); ?>\'?')"
                           title="<?php echo $seccion['Activo'] ? 'Desactivar' : 'Activar'; ?>">
                            <i class="bi bi-toggle-<?php echo $seccion['Activo'] ? 'on' : 'off'; ?>"></i>
                        </a>
                        <!-- Eliminar (solo si no tiene hijos) -->
                        <?php if (empty($seccion['modulos'])): ?>
                        <a href="/vetalmacen/public/index.php?url=modulos/eliminar/<?php echo $seccion['Id']; ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Eliminar la sección \'<?php echo htmlspecialchars($seccion['Nombre']); ?>\'?')"
                           title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </a>
                        <?php endif; ?>
                        <!-- Colapsar -->
                        <button class="btn btn-sm btn-light"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#seccion<?php echo $seccion['Id']; ?>">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="seccion<?php echo $seccion['Id']; ?>" class="collapse show">
                <div class="card-body p-0">

                    <?php if (empty($seccion['modulos'])): ?>
                        <p class="text-muted text-center py-3 mb-0">
                            <i class="bi bi-inbox"></i> No hay módulos en esta sección.
                            <a href="/vetalmacen/public/index.php?url=modulos/crear&nivel=2&padre_id=<?php echo $seccion['Id']; ?>">
                                Agregar módulo
                            </a>
                        </p>
                    <?php else: ?>

                        <?php foreach ($seccion['modulos'] as $modulo): ?>
                        <!-- ======================== NIVEL 2: MÓDULO ======================== -->
                        <div class="border-bottom">
                            <div class="px-4 py-2 <?php echo !$modulo['Activo'] ? 'bg-light' : ''; ?>">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted">└─</span>
                                            <?php if (!empty($modulo['Icono'])): ?>
                                                <i class="bi <?php echo htmlspecialchars($modulo['Icono']); ?> text-success"></i>
                                            <?php endif; ?>
                                            <span class="badge bg-success me-1">N2</span>
                                            <strong><?php echo htmlspecialchars($modulo['Nombre']); ?></strong>
                                            <?php if (!empty($modulo['Ruta'])): ?>
                                                <code class="small text-muted"><?php echo htmlspecialchars($modulo['Ruta']); ?></code>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                ID: <?php echo $modulo['Id']; ?> | Orden: <?php echo $modulo['Orden']; ?>
                                            </small>
                                            <?php if (!$modulo['Activo']): ?>
                                                <span class="badge bg-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto d-flex gap-1">
                                        <!-- Agregar acción -->
                                        <a href="/vetalmacen/public/index.php?url=modulos/crear&nivel=3&padre_id=<?php echo $modulo['Id']; ?>"
                                           class="btn btn-sm btn-outline-secondary" title="Agregar acción">
                                            <i class="bi bi-plus-circle"></i>
                                        </a>
                                        <!-- Editar módulo -->
                                        <a href="/vetalmacen/public/index.php?url=modulos/editar/<?php echo $modulo['Id']; ?>"
                                           class="btn btn-sm btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <!-- Toggle estado -->
                                        <a href="/vetalmacen/public/index.php?url=modulos/toggleEstado/<?php echo $modulo['Id']; ?>"
                                           class="btn btn-sm btn-outline-secondary"
                                           onclick="return confirm('¿Cambiar estado de \'<?php echo htmlspecialchars($modulo['Nombre']); ?>\'?')"
                                           title="<?php echo $modulo['Activo'] ? 'Desactivar' : 'Activar'; ?>">
                                            <i class="bi bi-toggle-<?php echo $modulo['Activo'] ? 'on' : 'off'; ?>"></i>
                                        </a>
                                        <?php if (empty($modulo['acciones'])): ?>
                                        <a href="/vetalmacen/public/index.php?url=modulos/eliminar/<?php echo $modulo['Id']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('¿Eliminar el módulo \'<?php echo htmlspecialchars($modulo['Nombre']); ?>\'?')"
                                           title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- ======================== NIVEL 3: ACCIONES ======================== -->
                            <?php if (!empty($modulo['acciones'])): ?>
                            <div class="bg-light">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-5">Acción</th>
                                            <th>Ruta</th>
                                            <th>ID</th>
                                            <th>Orden</th>
                                            <th>Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($modulo['acciones'] as $accion): ?>
                                        <tr class="<?php echo !$accion['Activo'] ? 'table-secondary' : ''; ?>">
                                            <td class="ps-5">
                                                <span class="text-muted me-2">└──</span>
                                                <span class="badge bg-secondary me-1">N3</span>
                                                <?php echo htmlspecialchars($accion['Nombre']); ?>
                                            </td>
                                            <td>
                                                <code class="small"><?php echo htmlspecialchars($accion['Ruta'] ?? '—'); ?></code>
                                            </td>
                                            <td><small class="text-muted"><?php echo $accion['Id']; ?></small></td>
                                            <td><small><?php echo $accion['Orden']; ?></small></td>
                                            <td>
                                                <?php if ($accion['Activo']): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="/vetalmacen/public/index.php?url=modulos/editar/<?php echo $accion['Id']; ?>"
                                                       class="btn btn-outline-warning" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="/vetalmacen/public/index.php?url=modulos/toggleEstado/<?php echo $accion['Id']; ?>"
                                                       class="btn btn-outline-secondary"
                                                       onclick="return confirm('¿Cambiar estado de \'<?php echo htmlspecialchars($accion['Nombre']); ?>\'?')"
                                                       title="<?php echo $accion['Activo'] ? 'Desactivar' : 'Activar'; ?>">
                                                        <i class="bi bi-toggle-<?php echo $accion['Activo'] ? 'on' : 'off'; ?>"></i>
                                                    </a>
                                                    <a href="/vetalmacen/public/index.php?url=modulos/eliminar/<?php echo $accion['Id']; ?>"
                                                       class="btn btn-outline-danger"
                                                       onclick="return confirm('¿Eliminar la acción \'<?php echo htmlspecialchars($accion['Nombre']); ?>\'?')"
                                                       title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>

                        </div>
                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
