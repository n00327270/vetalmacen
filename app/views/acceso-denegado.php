<?php
$pageTitle = 'Acceso Denegado';
require_once __DIR__ . '/layouts/header.php';
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg text-center">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="bi bi-shield-x text-danger" style="font-size: 5rem;"></i>
                    </div>
                    
                    <h1 class="display-4 text-danger">403</h1>
                    <h3 class="mb-3">Acceso Denegado</h3>
                    
                    <p class="text-muted mb-4">
                        No tienes permisos para acceder a esta sección del sistema.
                    </p>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i>
                        Si crees que esto es un error, contacta al administrador del sistema.
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="javascript:history.back()" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver Atrás
                        </a>
                        <a href="/vetalmacen/public/index.php?url=dashboard" class="btn btn-primary">
                            <i class="bi bi-house"></i> Ir al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>