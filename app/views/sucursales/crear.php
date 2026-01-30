<?php
$pageTitle = 'Crear Sucursal';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=sucursales">Sucursales</a></li>
            <li class="breadcrumb-item active">Crear</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-plus-circle"></i> Crear Sucursal</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="/vetalmacen/public/index.php?url=sucursales/guardar">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sede" class="form-label">Sede <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sede" name="sede" required autofocus placeholder="Ej: Los Olivos">
                                <div class="form-text">Nombre de la sede o localidad</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" placeholder="+51 987 654 321">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required placeholder="Av. Example 123, Distrito">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="sucursal@vetalmacen.com">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="horario_entrega" class="form-label">Horario de Entrega</label>
                                <input type="text" class="form-control" id="horario_entrega" name="horario_entrega" placeholder="L-V 08:00-18:00, S 08:00-13:00">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                                <label class="form-check-label" for="activo">
                                    <strong>Sucursal activa</strong>
                                    <div class="form-text">Las sucursales activas aparecerán disponibles para operaciones del sistema</div>
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Sucursal
                            </button>
                            <a href="/vetalmacen/public/index.php?url=sucursales" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel informativo -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle text-info"></i> Información</h5>
                    <p class="card-text">
                        Las sucursales representan los diferentes puntos de distribución o almacenes de tu veterinaria.
                    </p>
                    <hr>
                    <h6><i class="bi bi-check-circle text-success"></i> Funcionalidades:</h6>
                    <ul class="small">
                        <li>Control de stock independiente por sucursal</li>
                        <li>Gestión de órdenes de entrada y salida</li>
                        <li>Transferencias entre sucursales</li>
                        <li>Reportes específicos por ubicación</li>
                    </ul>
                    <div class="alert alert-warning mb-0" role="alert">
                        <small>
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Importante:</strong> Una vez creada, la sucursal iniciará con stock en 0 para todos los productos.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>