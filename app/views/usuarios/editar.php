<?php
$pageTitle = 'Editar Usuario';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=usuarios">Usuarios</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-pencil"></i> Editar Usuario</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="/vetalmacen/public/index.php?url=usuarios/actualizar" id="formUsuario">
                        <input type="hidden" name="id" value="<?php echo $usuario['Id']; ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($usuario['Username']); ?>" required autofocus>
                            <div class="form-text">Mínimo 4 caracteres</div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Deja los campos de contraseña vacíos si no deseas cambiarla.
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva Contraseña (opcional)</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Mínimo 6 caracteres. Dejar vacío para mantener la actual.</div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                        </div>

                        <div class="mb-3">
                            <label for="rol_id" class="form-label">Rol <span class="text-danger">*</span></label>
                            <select class="form-select" id="rol_id" name="rol_id" required>
                                <option value="">Seleccione un rol</option>
                                <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol['Id']; ?>" 
                                        <?php echo ($usuario['RolId'] == $rol['Id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rol['Nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="sucursal_id" class="form-label">Sucursal</label>
                            <select class="form-select" id="sucursal_id" name="sucursal_id">
                                <option value="">Ninguna (Solo para Administrador)</option>
                                <?php foreach ($sucursales as $sucursal): ?>
                                <option value="<?php echo $sucursal['Id']; ?>"
                                        <?php echo ($usuario['SucursalId'] == $sucursal['Id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sucursal['Sede']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Los administradores pueden no tener sucursal asignada</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Información adicional</label>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Fecha de registro:</strong></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($usuario['CreatedAt'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Rol actual:</strong></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo htmlspecialchars($usuario['RolNombre']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Sucursal actual:</strong></td>
                                    <td><?php echo htmlspecialchars($usuario['SucursalNombre'] ?? 'Sin asignar'); ?></td>
                                </tr>
                            </table>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Actualizar Usuario
                            </button>
                            <a href="/vetalmacen/public/index.php?url=usuarios" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-exclamation-triangle"></i> Advertencias
                    </h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-arrow-right text-warning"></i> Si cambias el rol, las permisos cambiarán inmediatamente
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-arrow-right text-warning"></i> No puedes editar tu propio usuario desde aquí
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-arrow-right text-warning"></i> Deja la contraseña vacía si no deseas cambiarla
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-arrow-right text-warning"></i> Al cambiar la sucursal, el usuario solo verá datos de esa sucursal
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3 bg-info bg-opacity-10">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-info-circle"></i> Roles del Sistema
                    </h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>Administrador:</strong> Acceso completo
                        </li>
                        <li class="mb-2">
                            <strong>Almacenero:</strong> Gestión de inventario
                        </li>
                        <li class="mb-2">
                            <strong>Logística:</strong> Órdenes de salida
                        </li>
                        <li class="mb-2">
                            <strong>Sucursal:</strong> Solo su sucursal
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formUsuario').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    
    // Solo validar si se ingresó una nueva contraseña
    if (password !== '' || passwordConfirm !== '') {
        if (password !== passwordConfirm) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            return false;
        }
        
        if (password.length > 0 && password.length < 6) {
            e.preventDefault();
            alert('La contraseña debe tener al menos 6 caracteres');
            return false;
        }
    }
    
    const username = document.getElementById('username').value;
    if (username.length < 4) {
        e.preventDefault();
        alert('El nombre de usuario debe tener al menos 4 caracteres');
        return false;
    }
});

// Prevenir que el usuario se edite a sí mismo
<?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $usuario['Id']): ?>
document.getElementById('formUsuario').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('No puedes editar tu propio usuario. Usa la opción "Mi Perfil" en el menú superior.');
    return false;
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>