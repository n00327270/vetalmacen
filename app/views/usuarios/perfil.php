<?php
$pageTitle = 'Mi Perfil';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Mi Perfil</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-person-circle"></i> Mi Perfil</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Información Personal</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Usuario:</th>
                            <td><strong><?php echo htmlspecialchars($usuario['Username']); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Rol:</th>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars($usuario['RolNombre']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Sucursal:</th>
                            <td><?php echo htmlspecialchars($usuario['SucursalNombre'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Miembro desde:</th>
                            <td><?php echo date('d/m/Y', strtotime($usuario['CreatedAt'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Cambiar Contraseña</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/vetalmacen/public/index.php?url=auth/cambiarPassword" id="formPassword">
                        <div class="mb-3">
                            <label for="password_actual" class="form-label">Contraseña Actual <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                        </div>

                        <div class="mb-3">
                            <label for="password_nuevo" class="form-label">Nueva Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_nuevo" name="password_nuevo" required>
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-key"></i> Cambiar Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formPassword').addEventListener('submit', function(e) {
    const passwordNuevo = document.getElementById('password_nuevo').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    
    if (passwordNuevo !== passwordConfirm) {
        e.preventDefault();
        alert('Las contraseñas nuevas no coinciden');
        return false;
    }
    
    if (passwordNuevo.length < 6) {
        e.preventDefault();
        alert('La nueva contraseña debe tener al menos 6 caracteres');
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>