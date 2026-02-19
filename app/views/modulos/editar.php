<?php
$pageTitle = 'Editar Módulo';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

$nivelLabels = [1 => 'Sección', 2 => 'Módulo', 3 => 'Acción'];
$nivelBadges = [1 => 'bg-primary', 2 => 'bg-success', 3 => 'bg-secondary'];
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=modulos">Gestión de Módulos</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-pencil"></i> Editar Módulo</h2>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">

            <!-- Info del módulo -->
            <div class="alert alert-info d-flex gap-3 align-items-center mb-3">
                <span class="badge <?php echo $nivelBadges[$modulo['Nivel']]; ?> fs-6">
                    Nivel <?php echo $modulo['Nivel']; ?>: <?php echo $nivelLabels[$modulo['Nivel']]; ?>
                </span>
                <span>ID: <strong><?php echo $modulo['Id']; ?></strong></span>
                <span class="ms-auto">
                    Estado: 
                    <span class="badge <?php echo $modulo['Activo'] ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $modulo['Activo'] ? 'Activo' : 'Inactivo'; ?>
                    </span>
                </span>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <form method="POST" action="/vetalmacen/public/index.php?url=modulos/actualizar">
                        <input type="hidden" name="id"    value="<?php echo $modulo['Id']; ?>">
                        <input type="hidden" name="nivel" value="<?php echo $modulo['Nivel']; ?>">

                        <!-- PADRE (solo si no es sección) -->
                        <?php if ($modulo['Nivel'] > 1): ?>
                        <div class="mb-3">
                            <label for="id_padre" class="form-label fw-bold">
                                Pertenece a <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="id_padre" name="id_padre" required>
                                <option value="">-- Seleccionar padre --</option>

                                <?php if ($modulo['Nivel'] == 2): ?>
                                    <!-- Padre de módulo = sección -->
                                    <?php foreach ($secciones as $s): ?>
                                    <option value="<?php echo $s['Id']; ?>"
                                            <?php echo $modulo['IdPadre'] == $s['Id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['Nombre']); ?> (ID: <?php echo $s['Id']; ?>)
                                    </option>
                                    <?php endforeach; ?>

                                <?php else: ?>
                                    <!-- Padre de acción = módulo -->
                                    <?php foreach ($nivel2 as $m): ?>
                                    <option value="<?php echo $m['Id']; ?>"
                                            <?php echo $modulo['IdPadre'] == $m['Id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($m['Nombre']); ?> (ID: <?php echo $m['Id']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <?php else: ?>
                            <input type="hidden" name="id_padre" value="">
                        <?php endif; ?>

                        <!-- NOMBRE -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-bold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   value="<?php echo htmlspecialchars($modulo['Nombre']); ?>"
                                   maxlength="100" required>
                        </div>

                        <!-- DESCRIPCIÓN -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label fw-bold">Descripción</label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion"
                                   value="<?php echo htmlspecialchars($modulo['Descripcion'] ?? ''); ?>"
                                   maxlength="255"
                                   placeholder="Subtítulo en el mega menú">
                        </div>

                        <!-- RUTA -->
                        <div class="mb-3">
                            <label for="ruta" class="form-label fw-bold">
                                Ruta
                                <?php if ($modulo['Nivel'] != 1): ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>
                            <input type="text" class="form-control" id="ruta" name="ruta"
                                   value="<?php echo htmlspecialchars($modulo['Ruta'] ?? ''); ?>"
                                   <?php if ($modulo['Nivel'] == 3): ?>
                                       placeholder="Ej: productos/crear, reportes/stock"
                                   <?php elseif ($modulo['Nivel'] == 2): ?>
                                       placeholder="Ej: productos, categorias"
                                   <?php else: ?>
                                       placeholder="Opcional para secciones"
                                   <?php endif; ?>>
                            <?php if ($modulo['Nivel'] == 3): ?>
                                <div class="form-text">
                                    ⚠️ Cambiar la ruta puede romper los permisos asignados.
                                    Actualiza también el controlador correspondiente.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- ICONO (solo niveles 1 y 2) -->
                        <?php if ($modulo['Nivel'] < 3): ?>
                        <div class="mb-3">
                            <label for="icono" class="form-label fw-bold">Icono Bootstrap</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi <?php echo htmlspecialchars($modulo['Icono'] ?? 'bi-question-circle'); ?>"
                                       id="iconoPreview"></i>
                                </span>
                                <input type="text" class="form-control" id="icono" name="icono"
                                       value="<?php echo htmlspecialchars($modulo['Icono'] ?? ''); ?>"
                                       placeholder="Ej: bi-box, bi-tags, bi-people"
                                       oninput="actualizarIcono(this.value)">
                                <a href="https://icons.getbootstrap.com/" target="_blank"
                                   class="btn btn-outline-secondary">
                                    <i class="bi bi-search"></i> Ver iconos
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                            <input type="hidden" name="icono" value="">
                        <?php endif; ?>

                        <!-- ORDEN -->
                        <div class="mb-4">
                            <label for="orden" class="form-label fw-bold">Orden de aparición</label>
                            <input type="number" class="form-control" id="orden" name="orden"
                                   value="<?php echo $modulo['Orden']; ?>"
                                   min="0" max="999">
                            <div class="form-text">Menor número aparece primero en el menú.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                            <a href="/vetalmacen/public/index.php?url=modulos" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <!-- Acceso rápido al toggle desde editar -->
                            <a href="/vetalmacen/public/index.php?url=modulos/toggleEstado/<?php echo $modulo['Id']; ?>"
                               class="btn btn-outline-<?php echo $modulo['Activo'] ? 'warning' : 'success'; ?> ms-auto"
                               onclick="return confirm('¿<?php echo $modulo['Activo'] ? 'Desactivar' : 'Activar'; ?> este módulo?')">
                                <i class="bi bi-toggle-<?php echo $modulo['Activo'] ? 'on' : 'off'; ?>"></i>
                                <?php echo $modulo['Activo'] ? 'Desactivar' : 'Activar'; ?>
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function actualizarIcono(valor) {
    const preview = document.getElementById('iconoPreview');
    if (preview) {
        preview.className = valor ? 'bi ' + valor : 'bi bi-question-circle';
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>