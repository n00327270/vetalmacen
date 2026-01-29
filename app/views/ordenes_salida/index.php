<?php
$pageTitle = 'Órdenes de Salida';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Órdenes de Salida</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-arrow-up-circle"></i> Órdenes de Salida</h2>
        </div>
        <div class="col-auto">
            <?php if (AuthHelper::hasAnyRole(['Administrador', 'Almacenero', 'Logistica'])): ?>
            <a href="/vetalmacen/public/index.php?url=ordenes_salida/crear" class="btn btn-info">
                <i class="bi bi-plus-circle"></i> Nueva Orden de Salida
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" id="searchOrden" placeholder="Buscar...">
                </div>
            </div>

            <!-- Tabla de órdenes -->
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="ordenesTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Sucursal</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ordenes)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay órdenes de salida registradas</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($ordenes as $orden): ?>
                            <tr>
                                <td>
                                    <a href="/vetalmacen/public/index.php?url=ordenes_salida/detalle/<?php echo $orden['Id']; ?>">
                                        <strong>#<?php echo $orden['Id']; ?></strong>
                                    </a>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($orden['Fecha'])); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo $orden['TipoSalida']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($orden['SucursalNombre'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($orden['UsuarioNombre']); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = match($orden['Estado']) {
                                        'Pendiente' => 'bg-warning text-dark',
                                        'Procesado' => 'bg-success',
                                        'Cancelado' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo $orden['Estado']; ?>
                                    </span>
                                </td>
                                <td class="text-end">S/ <?php echo number_format($orden['Total'], 2); ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/vetalmacen/public/index.php?url=ordenes_salida/detalle/<?php echo $orden['Id']; ?>" 
                                           class="btn btn-outline-info" title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (AuthHelper::isAdmin() && in_array($orden['Estado'], ['Pendiente', 'Cancelado'])): ?>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="eliminarOrden(<?php echo $orden['Id']; ?>)"
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
document.getElementById('searchOrden').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#ordenesTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

function eliminarOrden(id) {
    if (confirm('¿Está seguro de eliminar esta orden de salida?')) {
        window.location.href = `/vetalmacen/public/index.php?url=ordenes_salida/eliminar/${id}`;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>