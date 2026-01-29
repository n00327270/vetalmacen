<?php
$pageTitle = 'Proveedores';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Proveedores</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-truck"></i> Proveedores</h2>
        </div>
        <div class="col-auto">
            <?php if (AuthHelper::hasAnyRole(['Administrador', 'Almacenero'])): ?>
            <a href="/vetalmacen/public/index.php?url=proveedores/crear" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Proveedor
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Filtro búsqueda -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Buscar proveedor...">
                    </div>
                </div>
            </div>

            <!-- Tabla de proveedores -->
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="proveedoresTable">
                    <thead class="table-light">
                        <tr>
                            <th>Razón Social</th>
                            <th>RUC</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($proveedores)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay proveedores registrados</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($proveedores as $proveedor): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($proveedor['RazonSocial']); ?></strong></td>
                                <td><?php echo htmlspecialchars($proveedor['RUC']); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['NombreContacto']); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['Telefono']); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['Email']); ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/vetalmacen/public/index.php?url=proveedores/detalle/<?php echo $proveedor['Id']; ?>" 
                                           class="btn btn-outline-info" title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (AuthHelper::hasAnyRole(['Administrador', 'Almacenero'])): ?>
                                        <a href="/vetalmacen/public/index.php?url=proveedores/editar/<?php echo $proveedor['Id']; ?>" 
                                           class="btn btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (AuthHelper::isAdmin()): ?>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="eliminarProveedor(<?php echo $proveedor['Id']; ?>, '<?php echo htmlspecialchars($proveedor['RazonSocial']); ?>')"
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
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#proveedoresTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

function eliminarProveedor(id, nombre) {
    if (confirm(`¿Está seguro de eliminar el proveedor "${nombre}"?`)) {
        window.location.href = `/vetalmacen/public/index.php?url=proveedores/eliminar/${id}`;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>