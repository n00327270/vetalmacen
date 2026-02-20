<?php
$pageTitle = 'Asignar Permisos - ' . $rol['Nombre'];
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=permisos">Gestión de Permisos</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($rol['Nombre']); ?></li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col">
            <h2>
                <i class="bi bi-shield-check"></i>
                Permisos: <strong><?php echo htmlspecialchars($rol['Nombre']); ?></strong>
            </h2>
        </div>
        <div class="col-auto d-flex gap-2 align-items-center">
            <span class="text-muted">
                <span id="contadorSeleccionados" class="fw-bold text-primary">
                    <?php echo count($permisosActuales); ?>
                </span>
                módulos seleccionados
            </span>
            <a href="/vetalmacen/public/index.php?url=permisos" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Cancelar
            </a>
            <button type="submit" form="formPermisos" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Guardar Cambios
            </button>
        </div>
    </div>

    <!-- Acciones rápidas globales -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 d-flex gap-3 align-items-center">
            <span class="text-muted fw-bold">Acciones globales:</span>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="seleccionarTodo()">
                <i class="bi bi-check2-all"></i> Seleccionar todo
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deseleccionarTodo()">
                <i class="bi bi-x-square"></i> Deseleccionar todo
            </button>
        </div>
    </div>

    <form method="POST"
          action="/vetalmacen/public/index.php?url=permisos/guardar"
          id="formPermisos">

        <input type="hidden" name="rol_id" value="<?php echo $rol['Id']; ?>">

        <?php foreach ($arbol as $seccion): ?>
        <!-- ======================== SECCIÓN (Nivel 1) ======================== -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-light border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <!-- Checkbox sección -->
                    <div class="form-check mb-0">
                        <input class="form-check-input chk-seccion" type="checkbox"
                               id="seccion_<?php echo $seccion['Id']; ?>"
                               data-seccion="<?php echo $seccion['Id']; ?>"
                               name="permisos[]"
                               value="<?php echo $seccion['Id']; ?>"
                               <?php echo in_array($seccion['Id'], $permisosActuales) ? 'checked' : ''; ?>
                               onchange="actualizarContador()">
                    </div>
                    <!-- Label sección -->
                    <label class="form-check-label d-flex align-items-center gap-2 mb-0 fw-bold fs-6"
                           for="seccion_<?php echo $seccion['Id']; ?>"
                           style="cursor:pointer;">
                        <?php if (!empty($seccion['Icono'])): ?>
                            <i class="bi <?php echo htmlspecialchars($seccion['Icono']); ?> text-primary"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($seccion['Nombre']); ?>
                        <span class="badge bg-primary ms-1">Sección</span>
                    </label>
                    <!-- Botones seleccionar/deseleccionar sección -->
                    <div class="ms-auto d-flex gap-2">
                        <button type="button"
                                class="btn btn-sm btn-outline-success"
                                onclick="seleccionarSeccion(<?php echo $seccion['Id']; ?>)">
                            <i class="bi bi-check2-all"></i> Todos
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                onclick="deseleccionarSeccion(<?php echo $seccion['Id']; ?>)">
                            <i class="bi bi-x-square"></i> Ninguno
                        </button>
                        <!-- Colapsar -->
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseSeccion<?php echo $seccion['Id']; ?>">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="collapseSeccion<?php echo $seccion['Id']; ?>" class="collapse show">
                <div class="card-body p-0">

                    <?php if (empty($seccion['modulos'])): ?>
                        <p class="text-muted text-center py-3 mb-0">
                            <i class="bi bi-inbox"></i> Esta sección no tiene módulos.
                        </p>
                    <?php else: ?>

                        <?php foreach ($seccion['modulos'] as $modulo): ?>
                        <!-- ======================== MÓDULO (Nivel 2) ======================== -->
                        <div class="border-bottom" data-seccion="<?php echo $seccion['Id']; ?>">
                            <div class="px-4 py-2 bg-white">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted">└─</span>
                                            <!-- Checkbox módulo -->
                                            <div class="form-check mb-0">
                                                <input class="form-check-input chk-modulo"
                                                       type="checkbox"
                                                       id="modulo_<?php echo $modulo['Id']; ?>"
                                                       data-seccion="<?php echo $seccion['Id']; ?>"
                                                       data-modulo="<?php echo $modulo['Id']; ?>"
                                                       name="permisos[]"
                                                       value="<?php echo $modulo['Id']; ?>"
                                                       <?php echo in_array($modulo['Id'], $permisosActuales) ? 'checked' : ''; ?>
                                                       onchange="actualizarContador()">
                                            </div>
                                            <?php if (!empty($modulo['Icono'])): ?>
                                                <i class="bi <?php echo htmlspecialchars($modulo['Icono']); ?> text-success"></i>
                                            <?php endif; ?>
                                            <label class="form-check-label fw-bold mb-0"
                                                   for="modulo_<?php echo $modulo['Id']; ?>"
                                                   style="cursor:pointer;">
                                                <?php echo htmlspecialchars($modulo['Nombre']); ?>
                                            </label>
                                            <span class="badge bg-success">Módulo</span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-success"
                                                onclick="seleccionarModulo(<?php echo $modulo['Id']; ?>)">
                                            <i class="bi bi-check2-all"></i> Todos
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="deseleccionarModulo(<?php echo $modulo['Id']; ?>)">
                                            <i class="bi bi-x-square"></i> Ninguno
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- ======================== ACCIONES (Nivel 3) ======================== -->
                            <?php if (!empty($modulo['acciones'])): ?>
                            <div class="px-5 py-2 bg-light">
                                <div class="row g-2">
                                    <?php foreach ($modulo['acciones'] as $accion): ?>
                                    <div class="col-md-4 col-lg-3">
                                        <div class="form-check">
                                            <input class="form-check-input chk-accion"
                                                   type="checkbox"
                                                   id="accion_<?php echo $accion['Id']; ?>"
                                                   data-seccion="<?php echo $seccion['Id']; ?>"
                                                   data-modulo="<?php echo $modulo['Id']; ?>"
                                                   name="permisos[]"
                                                   value="<?php echo $accion['Id']; ?>"
                                                   <?php echo in_array($accion['Id'], $permisosActuales) ? 'checked' : ''; ?>
                                                   onchange="actualizarContador()">
                                            <label class="form-check-label small"
                                                   for="accion_<?php echo $accion['Id']; ?>"
                                                   style="cursor:pointer;">
                                                <i class="bi bi-chevron-right text-muted"></i>
                                                <?php echo htmlspecialchars($accion['Nombre']); ?>
                                                <?php if (!empty($accion['Ruta'])): ?>
                                                    <br>
                                                    <code class="text-muted" style="font-size:0.7rem;">
                                                        <?php echo htmlspecialchars($accion['Ruta']); ?>
                                                    </code>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div>
                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Botón guardar al final -->
        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="/vetalmacen/public/index.php?url=permisos" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-check-circle"></i> Guardar Cambios
            </button>
        </div>

    </form>
</div>

<script>
// ── Actualizar contador de seleccionados ───────────────────────────────────
function actualizarContador() {
    const total = document.querySelectorAll('input[name="permisos[]"]:checked').length;
    document.getElementById('contadorSeleccionados').textContent = total;
}

// ── Seleccionar / deseleccionar TODO ──────────────────────────────────────
function seleccionarTodo() {
    document.querySelectorAll('input[name="permisos[]"]').forEach(c => c.checked = true);
    actualizarContador();
}

function deseleccionarTodo() {
    document.querySelectorAll('input[name="permisos[]"]').forEach(c => c.checked = false);
    actualizarContador();
}

// ── Seleccionar / deseleccionar una SECCIÓN y todos sus hijos ─────────────
function seleccionarSeccion(seccionId) {
    document.querySelectorAll(`input[data-seccion="${seccionId}"]`).forEach(c => c.checked = true);
    // marcar también el checkbox de la sección misma
    const chkSeccion = document.getElementById('seccion_' + seccionId);
    if (chkSeccion) chkSeccion.checked = true;
    actualizarContador();
}

function deseleccionarSeccion(seccionId) {
    document.querySelectorAll(`input[data-seccion="${seccionId}"]`).forEach(c => c.checked = false);
    const chkSeccion = document.getElementById('seccion_' + seccionId);
    if (chkSeccion) chkSeccion.checked = false;
    actualizarContador();
}

// ── Seleccionar / deseleccionar un MÓDULO y todas sus acciones ────────────
function seleccionarModulo(moduloId) {
    document.querySelectorAll(`input[data-modulo="${moduloId}"]`).forEach(c => c.checked = true);
    const chkModulo = document.getElementById('modulo_' + moduloId);
    if (chkModulo) chkModulo.checked = true;
    actualizarContador();
}

function deseleccionarModulo(moduloId) {
    document.querySelectorAll(`input[data-modulo="${moduloId}"]`).forEach(c => c.checked = false);
    const chkModulo = document.getElementById('modulo_' + moduloId);
    if (chkModulo) chkModulo.checked = false;
    actualizarContador();
}

// ── Confirmar antes de guardar ────────────────────────────────────────────
document.getElementById('formPermisos').addEventListener('submit', function(e) {
    const total = document.querySelectorAll('input[name="permisos[]"]:checked').length;
    const rolNombre = '<?php echo htmlspecialchars($rol['Nombre']); ?>';
    if (!confirm(`¿Guardar ${total} permisos para el rol "${rolNombre}"?`)) {
        e.preventDefault();
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>