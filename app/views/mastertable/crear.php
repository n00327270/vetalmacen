<?php
$pageTitle = 'Crear Registro - MasterTable';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=mastertable">MasterTable</a></li>
            <li class="breadcrumb-item active">Crear</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-plus-circle"></i> Crear Nuevo Registro en MasterTable</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="/vetalmacen/public/index.php?url=mastertable/guardar">
                        
                        <!-- Tipo de registro -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Tipo de Registro</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipoPadre" 
                                       value="padre" <?php echo empty($parentIdPreselect) ? 'checked' : ''; ?>
                                       onchange="toggleParentSelect()">
                                <label class="form-check-label" for="tipoPadre">
                                    <i class="bi bi-folder"></i> Catálogo Padre (sin padre)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipoHijo" 
                                       value="hijo" <?php echo !empty($parentIdPreselect) ? 'checked' : ''; ?>
                                       onchange="toggleParentSelect()">
                                <label class="form-check-label" for="tipoHijo">
                                    <i class="bi bi-file-earmark"></i> Registro Hijo (pertenece a un catálogo)
                                </label>
                            </div>
                        </div>

                        <!-- Select padre (solo si es hijo) -->
                        <div class="mb-3" id="parentSelectContainer" style="display: <?php echo !empty($parentIdPreselect) ? 'block' : 'none'; ?>;">
                            <label for="parent_id" class="form-label">
                                Catálogo Padre <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">Seleccione un catálogo padre</option>
                                <?php foreach ($padres as $p): ?>
                                <option value="<?php echo $p['IdMasterTable']; ?>"
                                        <?php echo (isset($parentIdPreselect) && $parentIdPreselect == $p['IdMasterTable']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['Name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
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
                                   required 
                                   autofocus
                                   placeholder="Ej: Sociedad Anónima Cerrada">
                            <div class="form-text">Nombre descriptivo del registro</div>
                        </div>

                        <div class="mb-3">
                            <label for="value" class="form-label">Valor</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="value" 
                                   name="value"
                                   placeholder="Ej: S.A.C.">
                            <div class="form-text">Valor corto o sigla (opcional)</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Descripción detallada..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="order" class="form-label">Orden</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="order" 
                                   name="order" 
                                   value="0"
                                   min="0">
                            <div class="form-text">Orden de visualización (menor = primero)</div>
                        </div>

                        <!-- Campos adicionales -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Campos Adicionales (Opcional)</h6>
                                
                                <div class="mb-2">
                                    <label for="additional_one" class="form-label">Additional One</label>
                                    <input type="text" class="form-control" id="additional_one" name="additional_one">
                                </div>

                                <div class="mb-2">
                                    <label for="additional_two" class="form-label">Additional Two</label>
                                    <input type="text" class="form-control" id="additional_two" name="additional_two">
                                </div>

                                <div class="mb-0">
                                    <label for="additional_three" class="form-label">Additional Three</label>
                                    <input type="text" class="form-control" id="additional_three" name="additional_three">
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar
                            </button>
                            <a href="/vetalmacen/public/index.php?url=mastertable" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel informativo -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle text-primary"></i> Información</h5>
                    
                    <h6 class="mt-3">Catálogo Padre</h6>
                    <p class="small text-muted">
                        Es la categorí­a principal. Por ejemplo: "Denominación", "Tipos de Pago", etc.
                    </p>

                    <h6 class="mt-3">Registro Hijo</h6>
                    <p class="small text-muted">
                        Pertenece a un catálogo padre. Por ejemplo: "S.A.C." es hijo de "Denominación".
                    </p>

                    <h6 class="mt-3">Campos Adicionales</h6>
                    <p class="small text-muted mb-0">
                        Campos flexibles para almacenar información extra segíºn el tipo de catálogo.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleParentSelect() {
    const tipoHijo = document.getElementById('tipoHijo').checked;
    const container = document.getElementById('parentSelectContainer');
    const select = document.getElementById('parent_id');
    
    if (tipoHijo) {
        container.style.display = 'block';
        select.required = true;
    } else {
        container.style.display = 'none';
        select.required = false;
        select.value = '';
    }
}

// Ejecutar al cargar
document.addEventListener('DOMContentLoaded', toggleParentSelect);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>