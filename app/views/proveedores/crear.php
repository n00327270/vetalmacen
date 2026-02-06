<?php
$pageTitle = 'Crear Proveedor';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=proveedores">Proveedores</a></li>
            <li class="breadcrumb-item active">Crear</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-plus-circle"></i> Crear Proveedor</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="/vetalmacen/public/index.php?url=proveedores/guardar" id="formProveedor">
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="razon_social" class="form-label">Razón Social <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="razon_social" name="razon_social" required autofocus>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="denominacion_id" class="form-label">Denominación <span class="text-danger">*</span></label>
                                <select class="form-select" id="denominacion_id" name="denominacion_id" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($denominaciones as $denom): ?>
                                    <option value="<?php echo $denom['IdMasterTable']; ?>">
                                        <?php echo htmlspecialchars($denom['Value']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="ruc" class="form-label">RUC</label>
                                <input type="text" class="form-control" id="ruc" name="ruc" maxlength="11" pattern="[0-9]{11}">
                                <div class="form-text">11 dígitos</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nombre_contacto" class="form-label">Nombre de Contacto</label>
                            <input type="text" class="form-control" id="nombre_contacto" name="nombre_contacto">
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Proveedor
                            </button>
                            <a href="/vetalmacen/public/index.php?url=proveedores" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-info-circle"></i> Información
                    </h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i> La razón social es obligatoria
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i> La denominación es obligatoria
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i> El RUC debe tener 11 dígitos
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i> Los demás campos son opcionales
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>