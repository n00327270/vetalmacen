<?php
$pageTitle = 'Productos';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Productos</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-box-seam"></i> Productos</h2>
        </div>
        <div class="col-auto">
            <?php if (AuthHelper::hasAnyRole(['Administrador', 'Almacenero'])): ?>
            <a href="/vetalmacen/public/index.php?url=productos/crear" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Producto
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Buscar producto...">
                    </div>
                </div>
            </div>

            <!-- Tabla de productos -->
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="productosTable">
                    <thead class="table-light">
                        <tr>
                            <th>Imagen</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Marca</th>
                            <th>Categoría</th>
                            <th>Subcategoría</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay productos registrados</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $producto['ImagenUrl'] ?? '/vetalmacen/public/images/placeholder.png'; ?>" 
                                         alt="<?php echo htmlspecialchars($producto['Nombre']); ?>"
                                         class="img-thumbnail"
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                </td>
                                <td><?php echo htmlspecialchars($producto['Codigo']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($producto['Nombre']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($producto['Marca']); ?></td>
                                <td><?php echo htmlspecialchars($producto['CategoriaNombre']); ?></td>
                                <td><?php echo htmlspecialchars($producto['SubCategoriaNombre']); ?></td>
                                <td class="text-end">S/ <?php echo number_format($producto['Precio'], 2); ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/vetalmacen/public/index.php?url=productos/detalle/<?php echo $producto['Id']; ?>" 
                                           class="btn btn-outline-info" title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (AuthHelper::hasAnyRole(['Administrador', 'Almacenero'])): ?>
                                        <a href="/vetalmacen/public/index.php?url=productos/editar/<?php echo $producto['Id']; ?>" 
                                           class="btn btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (AuthHelper::isAdmin()): ?>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="eliminarProducto(<?php echo $producto['Id']; ?>, '<?php echo htmlspecialchars($producto['Nombre']); ?>')"
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
    const rows = document.querySelectorAll('#productosTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Función para eliminar producto
function eliminarProducto(id, nombre) {
    if (confirm(`¿Está seguro de eliminar el producto "${nombre}"?\n\nEsta acción no se puede deshacer y también eliminará la imagen de Azure.`)) {
        window.location.href = `/vetalmacen/public/index.php?url=productos/eliminar/${id}`;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>