<?php
$pageTitle = 'Subcategorías';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Subcategorías</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-bookmarks"></i> Subcategorías</h2>
        </div>
        <div class="col-auto">
            <?php if (AuthHelper::hasAnyRole(['Administrador', 'Almacenero'])): ?>
            <a href="/vetalmacen/public/index.php?url=subcategorias/crear" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nueva Subcategoría
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Filtro de búsqueda -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Buscar subcategoría...">
                    </div>
                </div>
            </div>

            <!-- Tabla de subcategorías -->
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="subcategoriasTable">
                    <thead class="table-light">
                        <tr>
                            <th>Categoría</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subcategorias)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay subcategorías registradas</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($subcategorias as $subcategoria): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary">
                                        <i class="bi bi-tag-fill"></i>
                                        <?php echo htmlspecialchars($subcategoria['CategoriaNombre']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($subcategoria['Nombre']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($subcategoria['Descripcion'] ?? 'Sin descripción'); ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <?php if (AuthHelper::hasAnyRole(['Administrador', 'Almacenero'])): ?>
                                        <a href="/vetalmacen/public/index.php?url=subcategorias/editar/<?php echo $subcategoria['Id']; ?>" 
                                           class="btn btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (AuthHelper::isAdmin()): ?>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="eliminarSubcategoria(<?php echo $subcategoria['Id']; ?>, '<?php echo htmlspecialchars($subcategoria['Nombre']); ?>')"
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Búsqueda en tiempo real
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#subcategoriasTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Función para eliminar subcategoría
function eliminarSubcategoria(id, nombre) {
    if (confirm(`¿Está seguro de eliminar la subcategoría "${nombre}"?\n\nEsta acción puede afectar productos asociados.`)) {
        window.location.href = `/vetalmacen/public/index.php?url=subcategorias/eliminar/${id}`;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>