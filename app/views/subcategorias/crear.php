<?php
$pageTitle = 'Crear Subcategoría';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=subcategorias">Subcategorías</a></li>
            <li class="breadcrumb-item active">Crear</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-plus-circle"></i> Crear Subcategoría</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="/vetalmacen/public/index.php?url=subcategorias/guardar">
                        <div class="mb-3">
                            <label for="categoria_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select class="form-select" id="categoria_id" name="categoria_id" required autofocus>
                                <option value="">Seleccione una categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['Id']; ?>">
                                    <?php echo htmlspecialchars($categoria['Nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                            <div class="form-text">Ejemplo: Alimento para perros adultos, Juguetes para gatos, etc.</div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Descripción opcional de la subcategoría"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                            <a href="/vetalmacen/public/index.php?url=subcategorias" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel informativo -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle text-info"></i> Información</h5>
                    <p class="card-text">
                        Las subcategorías permiten organizar mejor los productos dentro de cada categoría principal.
                    </p>
                    <hr>
                    <h6>Ejemplos:</h6>
                    <ul class="mb-0">
                        <li><strong>Categoría:</strong> Perro → <strong>Subcategorías:</strong> Alimentos, Juguetes, Accesorios</li>
                        <li><strong>Categoría:</strong> Gato → <strong>Subcategorías:</strong> Alimentos, Arena sanitaria, Rascadores</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>