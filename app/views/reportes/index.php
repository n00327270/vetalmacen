<?php
$pageTitle = 'Reportes';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Reportes</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-bar-chart"></i> Reportes del Sistema</h2>
            <p class="text-muted">Selecciona el tipo de reporte que deseas consultar</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Reporte de Movimientos -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-shadow">
                <div class="card-body text-center p-4">
                    <div class="bg-info bg-opacity-10 p-4 rounded-circle d-inline-block mb-3">
                        <i class="bi bi-arrow-left-right text-info" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Movimientos</h5>
                    <p class="card-text text-muted">Historial de entradas y salidas de productos</p>
                    <a href="/vetalmacen/public/index.php?url=reportes/movimientos" class="btn btn-info">
                        <i class="bi bi-eye"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <!-- Reporte de Stock -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-shadow">
                <div class="card-body text-center p-4">
                    <div class="bg-primary bg-opacity-10 p-4 rounded-circle d-inline-block mb-3">
                        <i class="bi bi-boxes text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Stock Actual</h5>
                    <p class="card-text text-muted">Inventario disponible por sucursal</p>
                    <a href="/vetalmacen/public/index.php?url=reportes/stock" class="btn btn-primary">
                        <i class="bi bi-eye"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <!-- Inventario Valorizado -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-shadow">
                <div class="card-body text-center p-4">
                    <div class="bg-warning bg-opacity-10 p-4 rounded-circle d-inline-block mb-3">
                        <i class="bi bi-currency-dollar text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Inventario Valorizado</h5>
                    <p class="card-text text-muted">Valor monetario del inventario</p>
                    <a href="/vetalmacen/public/index.php?url=reportes/inventarioValorizado" class="btn btn-warning">
                        <i class="bi bi-eye"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 hover-shadow">
                <div class="card-body text-center p-4">
                    <div class="bg-success bg-opacity-10 p-4 rounded-circle d-inline-block mb-3">
                        <i class="bi bi-pie-chart text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Estadísticas</h5>
                    <p class="card-text text-muted">Dashboard con gráficos y KPIs</p>
                    <a href="/vetalmacen/public/index.php?url=reportes/estadisticas" class="btn btn-success">
                        <i class="bi bi-eye"></i> Ver Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="row mt-5">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle text-primary"></i> Información de los Reportes</h5>
                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <h6><i class="bi bi-arrow-left-right text-info"></i> Movimientos</h6>
                            <p class="text-muted small mb-0">
                                Auditoría completa de entradas y salidas con filtros por fecha, sucursal y tipo de movimiento. 
                                Incluye exportación a Excel para análisis detallado.
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><i class="bi bi-boxes text-primary"></i> Stock Actual</h6>
                            <p class="text-muted small mb-0">
                                Inventario en tiempo real con indicadores visuales de stock bajo. 
                                Permite filtrar por sucursal y categoría. Exportable a Excel.
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><i class="bi bi-currency-dollar text-warning"></i> Inventario Valorizado</h6>
                            <p class="text-muted small mb-0">
                                Valor monetario total del inventario calculado por producto y sucursal. 
                                Útil para cierres contables y valuación de activos.
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><i class="bi bi-pie-chart text-success"></i> Estadísticas</h6>
                            <p class="text-muted small mb-0">
                                Vista ejecutiva del negocio con KPIs principales, gráficos de tendencias y alertas de stock crítico. 
                                Ideal para chequeo rápido diario.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tips rápidos -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="alert alert-light border" role="alert">
                <h6 class="alert-heading"><i class="bi bi-lightbulb text-warning"></i> Tips Rápidos</h6>
                <ul class="mb-0 small">
                    <li><strong>Exportar datos:</strong> Los reportes de Movimientos y Stock incluyen botón de exportación a Excel</li>
                    <li><strong>Filtros:</strong> Usa los filtros en cada reporte para obtener información específica</li>
                    <li><strong>Estadísticas:</strong> Consulta el dashboard de estadísticas cada mañana para tener una vista general del negocio</li>
                    <li><strong>Stock Crítico:</strong> Revisa regularmente los productos con stock bajo para evitar quiebres de inventario</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
}

.card-body .btn {
    transition: all 0.2s ease;
}

.card-body .btn:hover {
    transform: scale(1.05);
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>