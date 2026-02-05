<?php
$pageTitle = 'Stock por Sucursal';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Stock</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-boxes"></i> Stock por Sucursal</h2>
        </div>
        <div class="col-auto">
            <?php if (AuthHelper::hasAnyRole(['Administrador', 'Almacenero'])): ?>
            <a href="/vetalmacen/public/index.php?url=stock/transferencias" class="btn btn-warning me-2">
                <i class="bi bi-arrow-left-right"></i> Transferencias
            </a>
            <?php endif; ?>
            <a href="/vetalmacen/public/index.php?url=reportes/stock" class="btn btn-info">
                <i class="bi bi-file-earmark-text"></i> Ver Reporte
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="sucursalFilter" class="form-label">Filtrar por Sucursal</label>
                    <select class="form-select" id="sucursalFilter" onchange="filtrarSucursal()">
                        <option value="">Todas las sucursales</option>
                        <?php foreach ($sucursales as $sucursal): ?>
                        <option value="<?php echo $sucursal['Id']; ?>" 
                                <?php echo (isset($_GET['sucursal_id']) && $_GET['sucursal_id'] == $sucursal['Id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sucursal['Sede']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="searchStock" class="form-label">Buscar Producto</label>
                    <input type="text" class="form-control" id="searchStock" placeholder="Buscar...">
                </div>
            </div>

            <!-- Tabla de stock -->
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="stockTable">
                    <thead class="table-light">
                        <tr>
                            <th>Imagen</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Marca</th>
                            <th>Categoría</th>
                            <th>Sucursal</th>
                            <th class="text-end">Stock</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Valor Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stock)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">No hay stock registrado</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php 
                            $totalValor = 0;
                            foreach ($stock as $item): 
                                $valorTotal = $item['Stock'] * $item['Precio'];
                                $totalValor += $valorTotal;
                                
                                $stockClass = '';
                                if ($item['Stock'] <= 0) {
                                    $stockClass = 'table-danger';
                                } elseif ($item['Stock'] <= 10) {
                                    $stockClass = 'table-warning';
                                }
                            ?>
                            <tr class="<?php echo $stockClass; ?>">
                                <td>
                                    <?php if (!empty($item['BlobName'])): ?>
                                        <i class="bi bi-image-fill text-success" 
                                           data-bs-toggle="tooltip" 
                                           title="Tiene imagen"></i>
                                    <?php else: ?>
                                        <i class="bi bi-image text-muted" 
                                           data-bs-toggle="tooltip" 
                                           title="Sin imagen"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['Codigo']); ?></td>
                                <td>
                                    <a href="/vetalmacen/public/index.php?url=productos/detalle/<?php echo $item['ProductoId']; ?>">
                                        <strong><?php echo htmlspecialchars($item['ProductoNombre']); ?></strong>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($item['Marca']); ?></td>
                                <td><?php echo htmlspecialchars($item['CategoriaNombre']); ?></td>
                                <td><?php echo htmlspecialchars($item['SucursalNombre']); ?></td>
                                <td class="text-end">
                                    <?php
                                    $badgeClass = 'bg-success';
                                    if ($item['Stock'] <= 0) {
                                        $badgeClass = 'bg-danger';
                                    } elseif ($item['Stock'] <= 10) {
                                        $badgeClass = 'bg-warning';
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo number_format($item['Stock']); ?>
                                    </span>
                                </td>
                                <td class="text-end">S/ <?php echo number_format($item['Precio'], 2); ?></td>
                                <td class="text-end"><strong>S/ <?php echo number_format($valorTotal, 2); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-light">
                                <td colspan="8" class="text-end"><strong>VALOR TOTAL EN STOCK:</strong></td>
                                <td class="text-end"><strong class="text-success fs-5">S/ <?php echo number_format($totalValor, 2); ?></strong></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filtrarSucursal() {
    const sucursalId = document.getElementById('sucursalFilter').value;
    if (sucursalId) {
        window.location.href = `/vetalmacen/public/index.php?url=stock&sucursal_id=${sucursalId}`;
    } else {
        window.location.href = '/vetalmacen/public/index.php?url=stock';
    }
}

document.getElementById('searchStock').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#stockTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>