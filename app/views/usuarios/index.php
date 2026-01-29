<?php
$pageTitle = 'Usuarios';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Usuarios</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-people"></i> Usuarios del Sistema</h2>
        </div>
        <div class="col-auto">
            <a href="/vetalmacen/public/index.php?url=usuarios/crear" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Usuario
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Username</th>
                            <th>Rol</th>
                            <th>Sucursal</th>
                            <th>Fecha Registro</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No hay usuarios registrados
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($usuario['Username']); ?></strong></td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo htmlspecialchars($usuario['RolNombre']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($usuario['SucursalNombre'] ?? 'N/A'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($usuario['CreatedAt'])); ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/vetalmacen/public/index.php?url=usuarios/editar/<?php echo $usuario['Id']; ?>" 
                                           class="btn btn-outline-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($usuario['Id'] != SessionHelper::getUser()['id']): ?>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="eliminarUsuario(<?php echo $usuario['Id']; ?>, '<?php echo htmlspecialchars($usuario['Username']); ?>')">
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
function eliminarUsuario(id, username) {
    if (confirm(`¿Está seguro de eliminar el usuario "${username}"?`)) {
        window.location.href = `/vetalmacen/public/index.php?url=usuarios/eliminar/${id}`;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>