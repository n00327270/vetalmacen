<?php
$pageTitle = 'Estadísticas';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=reportes">Reportes</a></li>
            <li class="breadcrumb-item active">Estadísticas</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-pie-chart"></i> Estadísticas del Sistema</h2>
            <p class="text-muted">Vista ejecutiva del estado del negocio</p>
        </div>
    </div>

    <!-- KPIs Principales -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam text-primary" style="font-size: 2rem;"></i>
                    <h6 class="text-muted mt-2 mb-1">Productos</h6>
                    <h3 class="mb-0"><?php echo number_format($stats['total_productos']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-building text-info" style="font-size: 2rem;"></i>
                    <h6 class="text-muted mt-2 mb-1">Sucursales</h6>
                    <h3 class="mb-0"><?php echo number_format($stats['total_sucursales']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-currency-dollar text-success" style="font-size: 2rem;"></i>
                    <h6 class="text-muted mt-2 mb-1">Inventario</h6>
                    <h4 class="mb-0">S/ <?php echo number_format($stats['valor_inventario'], 0); ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-cash-coin text-success" style="font-size: 2rem;"></i>
                    <h6 class="text-muted mt-2 mb-1">Ventas del Mes</h6>
                    <h4 class="mb-0">S/ <?php echo number_format($stats['ventas_mes'], 0); ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm bg-warning">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle text-white" style="font-size: 2rem;"></i>
                    <h6 class="text-white mt-2 mb-1">Stock Bajo</h6>
                    <h3 class="mb-0 text-white"><?php echo number_format($stats['productos_stock_bajo']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm bg-danger">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle text-white" style="font-size: 2rem;"></i>
                    <h6 class="text-white mt-2 mb-1">Sin Stock</h6>
                    <h3 class="mb-0 text-white"><?php echo number_format($stats['productos_sin_stock']); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-graph-up"></i> Tendencia de Ventas (Últimos 6 Meses)</h5>
                    <canvas id="ventasChart" height="80"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-boxes"></i> Stock por Sucursal</h5>
                    <canvas id="stockChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos con Stock Crítico -->
    <?php if ($stats['productos_stock_bajo'] > 0 || $stats['productos_sin_stock'] > 0): ?>
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Atención: Productos con Stock Crítico</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Productos que requieren atención inmediata por bajo o nulo inventario</p>
                    <a href="/vetalmacen/public/index.php?url=reportes/stock?solo_stock_bajo=1" class="btn btn-warning">
                        <i class="bi bi-eye"></i> Ver Reporte Completo de Stock Bajo
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Gráfico de Ventas
const ventasData = {
    labels: [
        <?php foreach ($ventasPorMes as $v): ?>
        '<?php echo date('M Y', strtotime($v['mes'] . '-01')); ?>',
        <?php endforeach; ?>
    ],
    datasets: [{
        label: 'Ventas (S/)',
        data: [
            <?php foreach ($ventasPorMes as $v): ?>
            <?php echo $v['total']; ?>,
            <?php endforeach; ?>
        ],
        borderColor: 'rgb(75, 192, 192)',
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        tension: 0.4,
        fill: true
    }]
};

const ventasConfig = {
    type: 'line',
    data: ventasData,
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'S/ ' + context.parsed.y.toLocaleString('es-PE', {minimumFractionDigits: 2});
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'S/ ' + value.toLocaleString('es-PE');
                    }
                }
            }
        }
    }
};

const ventasChart = new Chart(
    document.getElementById('ventasChart'),
    ventasConfig
);

// Gráfico de Stock por Sucursal
const stockData = {
    labels: [
        <?php foreach ($stockPorSucursal as $s): ?>
        '<?php echo htmlspecialchars($s['Sede']); ?>',
        <?php endforeach; ?>
    ],
    datasets: [{
        label: 'Unidades',
        data: [
            <?php foreach ($stockPorSucursal as $s): ?>
            <?php echo $s['total_unidades']; ?>,
            <?php endforeach; ?>
        ],
        backgroundColor: [
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)'
        ],
        borderWidth: 0
    }]
};

const stockConfig = {
    type: 'doughnut',
    data: stockData,
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.parsed.toLocaleString('es-PE') + ' unidades';
                    }
                }
            }
        }
    }
};

const stockChart = new Chart(
    document.getElementById('stockChart'),
    stockConfig
);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>