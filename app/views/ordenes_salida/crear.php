<?php
$pageTitle = 'Crear Orden de Salida';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=ordenes_salida">Órdenes de Salida</a></li>
            <li class="breadcrumb-item active">Crear</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-plus-circle"></i> Crear Orden de Salida</h2>
        </div>
    </div>

    <form method="POST" action="/vetalmacen/public/index.php?url=ordenes_salida/guardar" id="formOrdenSalida">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Información de la Orden</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sucursal_id" class="form-label">Sucursal Origen <span class="text-danger">*</span></label>
                                <select class="form-select" id="sucursal_id" name="sucursal_id" required onchange="actualizarStockDisponible()">
                                    <option value="">Seleccione una sucursal</option>
                                    <?php foreach ($sucursales as $sucursal): ?>
                                    <option value="<?php echo $sucursal['Id']; ?>">
                                        <?php echo htmlspecialchars($sucursal['Sede']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tipo_salida" class="form-label">Tipo de Salida <span class="text-danger">*</span></label>
                                <select class="form-select" id="tipo_salida" name="tipo_salida" required>
                                    <option value="Venta">Venta</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="SalidaInterna">Salida Interna</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="observacion" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observacion" name="observacion" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Productos -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Productos</h5>
                            <button type="button" class="btn btn-sm btn-primary" onclick="agregarProducto()">
                                <i class="bi bi-plus-circle"></i> Agregar Producto
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle" id="productosTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30%">Producto</th>
                                        <th width="15%">Stock Disp.</th>
                                        <th width="15%">Cantidad</th>
                                        <th width="15%">Precio Unit.</th>
                                        <th width="20%" class="text-end">Subtotal</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="productosBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            Seleccione primero una sucursal, luego agregue productos
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="4" class="text-end">TOTAL:</th>
                                        <th class="text-end"><span id="totalGeneral">S/ 0.00</span></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-exclamation-triangle"></i> Importante
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i> Seleccione la sucursal de origen
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i> Seleccione el tipo de salida
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i> Verifique el stock disponible
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success"></i> El stock se descontará automáticamente
                            </li>
                        </ul>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-save"></i> Guardar Orden
                            </button>
                            <a href="/vetalmacen/public/index.php?url=ordenes_salida" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let productos = <?php echo json_encode($productos); ?>;
let contadorFilas = 0;
let stockCache = {};

function agregarProducto() {
    const sucursalId = document.getElementById('sucursal_id').value;
    
    if (!sucursalId) {
        alert('Por favor seleccione primero una sucursal');
        return;
    }
    
    contadorFilas++;
    const tbody = document.getElementById('productosBody');
    
    // Eliminar mensaje si existe
    const emptyRow = tbody.querySelector('td[colspan="6"]');
    if (emptyRow) {
        emptyRow.parentElement.remove();
    }
    
    const row = document.createElement('tr');
    row.id = `fila-${contadorFilas}`;
    row.innerHTML = `
        <td>
            <select class="form-select form-select-sm" name="productos[]" required onchange="actualizarStockYPrecio(this, ${contadorFilas})">
                <option value="">Seleccione...</option>
                ${productos.map(p => `<option value="${p.Id}" data-precio="${p.Precio}">${p.Nombre} - ${p.Codigo}</option>`).join('')}
            </select>
        </td>
        <td>
            <span class="badge bg-secondary" id="stock-disponible-${contadorFilas}">-</span>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" name="cantidades[]" 
                   id="cantidad-${contadorFilas}" min="1" value="1" required onchange="validarStock(${contadorFilas})">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm" name="precios[]" 
                   id="precio-${contadorFilas}" step="0.01" min="0" value="0" required onchange="calcularSubtotal(${contadorFilas})">
        </td>
        <td class="text-end">
            <strong id="subtotal-${contadorFilas}">S/ 0.00</strong>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarFila(${contadorFilas})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
}

async function actualizarStockYPrecio(select, filaId) {
    const option = select.options[select.selectedIndex];
    const productoId = option.value;
    const precio = option.getAttribute('data-precio') || 0;
    const sucursalId = document.getElementById('sucursal_id').value;
    
    document.getElementById(`precio-${filaId}`).value = precio;
    
    if (!productoId || !sucursalId) return;
    
    // Obtener stock disponible
    try {
        const response = await fetch(`/vetalmacen/public/index.php?url=stock/getStockProducto&producto_id=${productoId}&sucursal_id=${sucursalId}`);
        const data = await response.json();
        const stock = data.stock || 0;
        
        stockCache[filaId] = stock;
        
        const stockBadge = document.getElementById(`stock-disponible-${filaId}`);
        stockBadge.textContent = stock;
        stockBadge.className = stock > 10 ? 'badge bg-success' : (stock > 0 ? 'badge bg-warning' : 'badge bg-danger');
        
        document.getElementById(`cantidad-${filaId}`).max = stock;
        
        calcularSubtotal(filaId);
    } catch (error) {
        console.error('Error:', error);
    }
}

function validarStock(filaId) {
    const cantidad = parseInt(document.getElementById(`cantidad-${filaId}`).value) || 0;
    const stockDisponible = stockCache[filaId] || 0;
    
    if (cantidad > stockDisponible) {
        alert(`Stock insuficiente. Disponible: ${stockDisponible}`);
        document.getElementById(`cantidad-${filaId}`).value = stockDisponible;
    }
    
    calcularSubtotal(filaId);
}

function calcularSubtotal(filaId) {
    const fila = document.getElementById(`fila-${filaId}`);
    if (!fila) return;
    
    const cantidad = parseFloat(fila.querySelector('input[name="cantidades[]"]').value) || 0;
    const precio = parseFloat(fila.querySelector('input[name="precios[]"]').value) || 0;
    const subtotal = cantidad * precio;
    
    document.getElementById(`subtotal-${filaId}`).textContent = `S/ ${subtotal.toFixed(2)}`;
    calcularTotal();
}

function calcularTotal() {
    let total = 0;
    document.querySelectorAll('[id^="subtotal-"]').forEach(elemento => {
        const valor = parseFloat(elemento.textContent.replace('S/ ', '')) || 0;
        total += valor;
    });
    document.getElementById('totalGeneral').textContent = `S/ ${total.toFixed(2)}`;
}

function eliminarFila(filaId) {
    const fila = document.getElementById(`fila-${filaId}`);
    fila.remove();
    delete stockCache[filaId];
    calcularTotal();
    
    const tbody = document.getElementById('productosBody');
    if (tbody.children.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    Seleccione primero una sucursal, luego agregue productos
                </td>
            </tr>
        `;
    }
}

function actualizarStockDisponible() {
    // Limpiar tabla al cambiar sucursal
    const tbody = document.getElementById('productosBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-muted">
                Seleccione primero una sucursal, luego agregue productos
            </td>
        </tr>
    `;
    stockCache = {};
    contadorFilas = 0;
    calcularTotal();
}

// Validación del formulario
document.getElementById('formOrdenSalida').addEventListener('submit', function(e) {
    const tbody = document.getElementById('productosBody');
    const hasProducts = tbody.querySelector('select[name="productos[]"]') !== null;
    
    if (!hasProducts) {
        e.preventDefault();
        alert('Debe agregar al menos un producto a la orden');
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>