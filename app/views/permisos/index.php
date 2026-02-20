<?php
$pageTitle = 'Gestión de Permisos';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Gestión de Permisos</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-shield-lock"></i> Gestión de Permisos</h2>
            <p class="text-muted">Asigna qué módulos y acciones puede ver cada rol</p>
        </div>
    </div>

    <!-- Info SuperUsuario -->
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div>
            El <strong>SuperUsuario</strong> tiene acceso total al sistema de forma permanente y no requiere configuración de permisos.
            Los cambios aquí afectan únicamente a los roles regulares.
        </div>
    </div>

    <!-- Tarjetas de roles -->
    <div class="row g-4">
        <?php foreach ($roles as $rol): ?>
        <?php
            $porcentaje = $totalModulos > 0
                ? round(($rol['total_permisos'] / $totalModulos) * 100)
                : 0;

            // Color de la barra según porcentaje
            $colorBarra = 'bg-success';
            if ($porcentaje < 30) {
                $colorBarra = 'bg-danger';
            } elseif ($porcentaje < 60) {
                $colorBarra = 'bg-warning';
            }

            // Icono por nombre de rol
            $iconoRol = 'bi-person';
            if (stripos($rol['Nombre'], 'admin') !== false) $iconoRol = 'bi-person-gear';
            if (stripos($rol['Nombre'], 'almacen') !== false) $iconoRol = 'bi-box-seam';
            if (stripos($rol['Nombre'], 'logistica') !== false || stripos($rol['Nombre'], 'logística') !== false) $iconoRol = 'bi-truck';
        ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi <?php echo $iconoRol; ?> text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?php echo htmlspecialchars($rol['Nombre']); ?></h5>
                            <small class="text-muted">ID: <?php echo $rol['Id']; ?></small>
                        </div>
                    </div>

                    <!-- Barra de progreso de permisos -->
                    <div class="mb-1 d-flex justify-content-between">
                        <small class="text-muted">Permisos asignados</small>
                        <small class="fw-bold"><?php echo $rol['total_permisos']; ?> / <?php echo $totalModulos; ?></small>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar <?php echo $colorBarra; ?>"
                             role="progressbar"
                             style="width: <?php echo $porcentaje; ?>%"
                             aria-valuenow="<?php echo $porcentaje; ?>"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                    <div class="text-center mb-3">
                        <span class="badge <?php echo $colorBarra; ?> fs-6">
                            <?php echo $porcentaje; ?>% del sistema
                        </span>
                    </div>

                    <a href="/vetalmacen/public/index.php?url=permisos/asignar/<?php echo $rol['Id']; ?>"
                       class="btn btn-primary w-100">
                        <i class="bi bi-shield-check"></i> Asignar Permisos
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>