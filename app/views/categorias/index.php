<?php
$pageTitle = 'Categorías';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Categorías</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-tags"></i> Categorías</h2>
        </div>
        <div class="col-auto">
            <?php if (AuthHelper::hasAnyRole(['Administrador', 'Almacenero'])): ?>
            <a href="/vetalmacen/public/index.php?url=categorias/crear" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nueva Categoría
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <?php if (empty($categorias)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay categorías registradas.
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($categorias as $categoria): ?>
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-tag-fill text-primary"></i>
                            <?php echo htmlspecialchars($categoria['Nombre']); ?>
                        </h5>
                        <p class="card-text text-muted">
                            <?php echo htmlspecialchars($categoria['Descripcion'] ?? 'Sin descripción'); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-secondary">
                                <?php echo $categoria['SubcategoriaCount']; ?> subcategorías
                            </span>
                            <div class="btn-group btn-group-sm">
                                <?php if (AuthHelper::hasAnyRole(['Administrador', 'Almacenero'])): ?>
                                <a href="/vetalmacen/public/index.php?url=categorias/editar/<?php echo $categoria['Id']; ?>" 
                                   class="btn btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (AuthHelper::isAdmin()): ?>
                                <button type="button" 
                                        class="btn btn-outline-danger" 
                                        onclick="eliminarCategoria(<?php echo $categoria['Id']; ?>, '<?php echo htmlspecialchars($categoria['Nombre']); ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function eliminarCategoria(id, nombre) {
    if (confirm(`¿Está seguro de eliminar la categoría "${nombre}"?\n\nEsta acción eliminará también todas sus subcategorías asociadas.`)) {
        window.location.href = `/vetalmacen/public/index.php?url=categorias/eliminar/${id}`;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>