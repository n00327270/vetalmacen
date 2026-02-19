<?php
$pageTitle = 'Crear Módulo';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=modulos">Gestión de Módulos</a></li>
            <li class="breadcrumb-item active">Crear</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-plus-circle"></i> Crear Nuevo Módulo</h2>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <form method="POST" action="/vetalmacen/public/index.php?url=modulos/guardar" id="formCrear">

                        <!-- NIVEL -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Nivel <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="nivel" id="nivel1" value="1"
                                           <?php echo $nivelSugerido == 1 ? 'checked' : ''; ?>
                                           onchange="cambiarNivel(1)">
                                    <label class="form-check-label" for="nivel1">
                                        <span class="badge bg-primary">Nivel 1</span> Sección
                                        <small class="text-muted d-block">Inventario, Reportes, Configuración...</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="nivel" id="nivel2" value="2"
                                           <?php echo $nivelSugerido == 2 ? 'checked' : ''; ?>
                                           onchange="cambiarNivel(2)">
                                    <label class="form-check-label" for="nivel2">
                                        <span class="badge bg-success">Nivel 2</span> Módulo
                                        <small class="text-muted d-block">Productos, Categorías, Stock...</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="nivel" id="nivel3" value="3"
                                           <?php echo $nivelSugerido == 3 ? 'checked' : ''; ?>
                                           onchange="cambiarNivel(3)">
                                    <label class="form-check-label" for="nivel3">
                                        <span class="badge bg-secondary">Nivel 3</span> Acción
                                        <small class="text-muted d-block">Ver Listado, Crear, Editar...</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- PADRE (Nivel 2 y 3) -->
                        <div class="mb-3" id="campoPadre">
                            <label for="id_padre" class="form-label fw-bold">
                                Pertenece a <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="id_padre" name="id_padre">
                                <option value="">-- Seleccionar padre --</option>

                                <!-- Opciones nivel 2: padres son secciones -->
                                <optgroup label="Secciones (para Módulo Nivel 2)" id="opcionesSecciones">
                                    <?php foreach ($secciones as $s): ?>
                                    <option value="<?php echo $s['Id']; ?>"
                                            data-nivel="1"
                                            <?php echo ($padreSugerido == $s['Id'] && $nivelSugerido == 2) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['Nombre']); ?> (ID: <?php echo $s['Id']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>

                                <!-- Opciones nivel 3: padres son módulos -->
                                <optgroup label="Módulos (para Acción Nivel 3)" id="opcionesModulos">
                                    <?php foreach ($nivel2 as $m): ?>
                                    <option value="<?php echo $m['Id']; ?>"
                                            data-nivel="2"
                                            <?php echo ($padreSugerido == $m['Id'] && $nivelSugerido == 3) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($m['Nombre']); ?> (ID: <?php echo $m['Id']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>

                        <!-- ID MANUAL -->
                        <div class="mb-3">
                            <label for="id" class="form-label fw-bold">
                                ID <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="id" name="id"
                                   placeholder="Ej: 5006, 500601..."
                                   required>
                            <div class="form-text" id="ayudaId">
                                <i class="bi bi-info-circle"></i>
                                Secciones: 10, 20, 30... | Módulos: 1001, 2001... | Acciones: 100101, 200102...
                            </div>
                        </div>

                        <!-- NOMBRE -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-bold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   placeholder="Ej: Productos, Ver Listado, Clientes..."
                                   maxlength="100" required>
                        </div>

                        <!-- DESCRIPCIÓN -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label fw-bold">Descripción</label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion"
                                   placeholder="Ej: Administrar catálogo de productos"
                                   maxlength="255">
                            <div class="form-text">Se muestra como subtítulo en el mega menú (solo Nivel 2)</div>
                        </div>

                        <!-- RUTA -->
                        <div class="mb-3" id="campoRuta">
                            <label for="ruta" class="form-label fw-bold">
                                Ruta <span class="text-danger" id="asteriscoRuta">*</span>
                            </label>
                            <input type="text" class="form-control" id="ruta" name="ruta"
                                   placeholder="Ej: productos, productos/crear, reportes/stock">
                            <div class="form-text" id="ayudaRuta">
                                Nivel 2: Solo el nombre (ej: <code>productos</code>) |
                                Nivel 3: Ruta completa (ej: <code>productos/crear</code>)
                            </div>
                        </div>

                        <!-- ICONO -->
                        <div class="mb-3" id="campoIcono">
                            <label for="icono" class="form-label fw-bold">Icono Bootstrap</label>
                            <div class="input-group">
                                <span class="input-group-text" id="previewIcono">
                                    <i class="bi bi-question-circle" id="iconoPreview"></i>
                                </span>
                                <input type="text" class="form-control" id="icono" name="icono"
                                       placeholder="Ej: bi-box, bi-tags, bi-people"
                                       oninput="actualizarIcono(this.value)">
                                <a href="https://icons.getbootstrap.com/" target="_blank" class="btn btn-outline-secondary">
                                    <i class="bi bi-search"></i> Ver iconos
                                </a>
                            </div>
                            <div class="form-text">Solo para Nivel 1 y 2. Copia el nombre del icono desde Bootstrap Icons.</div>
                        </div>

                        <!-- ORDEN -->
                        <div class="mb-4">
                            <label for="orden" class="form-label fw-bold">Orden de aparición</label>
                            <input type="number" class="form-control" id="orden" name="orden"
                                   value="0" min="0" max="999">
                            <div class="form-text">Menor número aparece primero en el menú.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Crear Módulo
                            </button>
                            <a href="/vetalmacen/public/index.php?url=modulos" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Nivel sugerido desde URL
const nivelInicial = <?php echo (int)$nivelSugerido; ?>;

document.addEventListener('DOMContentLoaded', function() {
    cambiarNivel(nivelInicial);
});

function cambiarNivel(nivel) {
    const campoPadre  = document.getElementById('campoPadre');
    const campoIcono  = document.getElementById('campoIcono');
    const asterisco   = document.getElementById('asteriscoRuta');
    const ayudaRuta   = document.getElementById('ayudaRuta');
    const inputRuta   = document.getElementById('ruta');
    const inputPadre  = document.getElementById('id_padre');
    const opSecciones = document.getElementById('opcionesSecciones');
    const opModulos   = document.getElementById('opcionesModulos');
    const ayudaId     = document.getElementById('ayudaId');

    if (nivel == 1) {
        // Sección: sin padre, sin ruta obligatoria
        campoPadre.style.display = 'none';
        inputPadre.required      = false;
        asterisco.style.display  = 'none';
        inputRuta.placeholder    = 'Opcional para secciones';
        ayudaRuta.innerHTML      = 'Las secciones generalmente no tienen ruta';
        campoIcono.style.display = 'block';
        ayudaId.innerHTML        = '<i class="bi bi-info-circle"></i> Secciones: 10, 20, 30, 40, 50, 60...';

        // Ocultar optgroups
        opSecciones.style.display = 'none';
        opModulos.style.display   = 'none';

    } else if (nivel == 2) {
        // Módulo: padre = sección
        campoPadre.style.display  = 'block';
        inputPadre.required       = true;
        asterisco.style.display   = 'inline';
        inputRuta.placeholder     = 'Ej: productos, categorias, proveedores';
        ayudaRuta.innerHTML       = 'Solo el nombre sin "/" (ej: <code>productos</code>)';
        campoIcono.style.display  = 'block';
        ayudaId.innerHTML         = '<i class="bi bi-info-circle"></i> Módulos: 1001, 2001, 3001... o 5005, 5006...';

        // Mostrar solo secciones como padre
        opSecciones.style.display = '';
        opModulos.style.display   = 'none';

    } else if (nivel == 3) {
        // Acción: padre = módulo
        campoPadre.style.display  = 'block';
        inputPadre.required       = true;
        asterisco.style.display   = 'inline';
        inputRuta.placeholder     = 'Ej: productos/crear, reportes/stock';
        ayudaRuta.innerHTML       = 'Ruta completa (ej: <code>productos/crear</code>)';
        campoIcono.style.display  = 'none'; // Acciones no tienen icono
        ayudaId.innerHTML         = '<i class="bi bi-info-circle"></i> Acciones: 100101, 200102... (ID del módulo padre + 2 dígitos)';

        // Mostrar solo módulos como padre
        opSecciones.style.display = 'none';
        opModulos.style.display   = '';
    }
}

function actualizarIcono(valor) {
    const preview = document.getElementById('iconoPreview');
    preview.className = valor ? 'bi ' + valor : 'bi bi-question-circle';
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>