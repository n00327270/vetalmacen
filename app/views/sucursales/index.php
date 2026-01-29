<?php
$pageTitle = 'Sucursales';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Sucursales</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-building"></i> Sucursales</h2>
        </div>
        <div class="col-auto">
            <?php if (AuthHelper::isAdmin()): ?>
            <a href="/vetalmacen/public/index.php?url=sucursales/crear" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nueva Sucursal
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
                        <input type="text" class="form-control" id="searchInput" placeholder="Buscar sucursal...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="estadoFilter" onchange="filtrarPorEstado()">
                        <option value="">Todas las sucursales</option>
                        <option value="activo">Solo activas</option>
                        <option value="inactivo">Solo inactivas</option>
                    </select>
                </div>
            </div>

            <!-- Tabla de sucursales -->
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="sucursalesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Sede</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Horario Entrega</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sucursales)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay sucursales registradas</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($sucursales as $sucursal): ?>
                            <tr data-estado="<?php echo $sucursal['Activo'] ? 'activo' : 'inactivo'; ?>">
                                <td>
                                    <strong><i class="bi bi-geo-alt-fill text-primary"></i> <?php echo htmlspecialchars($sucursal['Sede']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($sucursal['Direccion']); ?></td>
                                <td>
                                    <?php if (!empty($sucursal['Telefono'])): ?>
                                        <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($sucursal['Telefono']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($sucursal['Email'])): ?>
                                        <i class="bi bi-envelope"></i> <a href="mailto:<?php echo htmlspecialchars($sucursal['Email']); ?>"><?php echo htmlspecialchars($sucursal['Email']); ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($sucursal['HorarioEntrega'])): ?>
                                        <i class="bi bi-clock"></i> <?php echo htmlspecialchars($sucursal['HorarioEntrega']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($sucursal['Activo']): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Activa
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> Inactiva
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <?php if (AuthHelper::isAdmin()): ?>
                                        <a href="/vetalmacen/public/index.php?url=sucursales/editar/<?php echo $sucursal['Id']; ?>" 
                                           class="btn btn-outline-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($sucursal['Activo']): ?>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="desactivarSucursal(<?php echo $sucursal['Id']; ?>, '<?php echo htmlspecialchars($sucursal['Sede']); ?>')"
                                                title="Desactivar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php else: ?>
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="bi bi-lock"></i>
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
    const rows = document.querySelectorAll('#sucursalesTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (!row.querySelector('td[colspan]')) {
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        }
    });
});

// Filtrar por estado
function filtrarPorEstado() {
    const filtro = document.getElementById('estadoFilter').value;
    const rows = document.querySelectorAll('#sucursalesTable tbody tr[data-estado]');
    
    rows.forEach(row => {
        if (filtro === '') {
            row.style.display = '';
        } else {
            row.style.display = row.dataset.estado === filtro ? '' : 'none';
        }
    });
}

// Función para desactivar sucursal
function desactivarSucursal(id, nombre) {
    if (confirm(`¿Está seguro de desactivar la sucursal "${nombre}"?\n\nEsta sucursal dejará de estar disponible para nuevas operaciones.`)) {
        window.location.href = `/vetalmacen/public/index.php?url=sucursales/eliminar/${id}`;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>