<?php
$pageTitle = 'Detalle Proveedor';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=proveedores">Proveedores</a></li>
            <li class="breadcrumb-item active">Detalle</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-truck"></i> Detalle del Proveedor</h2>
        </div>
        <div class="col-auto">
            <?php if (AuthHelper::hasAnyRole(['Administrador', 'Almacenero'])): ?>
            <a href="/vetalmacen/public/index.php?url=proveedores/editar/<?php echo $proveedor['Id']; ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <?php endif; ?>
            <a href="/vetalmacen/public/index.php?url=proveedores" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Información del Proveedor</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="35%">Razón Social:</th>
                            <td><strong><?php echo htmlspecialchars($proveedor['RazonSocial']); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Denominación:</th>
                            <td><?php echo htmlspecialchars($proveedor['DenominacionValor']); ?></td>
                        </tr>
                        <tr>
                            <th>RUC:</th>
                            <td><?php echo htmlspecialchars($proveedor['RUC']); ?></td>
                        </tr>
                        <tr>
                            <th>Contacto:</th>
                            <td><?php echo htmlspecialchars($proveedor['NombreContacto']); ?></td>
                        </tr>
                        <tr>
                            <th>Dirección:</th>
                            <td><?php echo htmlspecialchars($proveedor['Direccion']); ?></td>
                        </tr>
                        <tr>
                            <th>Teléfono:</th>
                            <td><?php echo htmlspecialchars($proveedor['Telefono']); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($proveedor['Email']); ?></td>
                        </tr>
                        <tr>
                            <th>Registrado:</th>
                            <td><?php echo date('d/m/Y H:i', strtotime($proveedor['CreatedAt'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>