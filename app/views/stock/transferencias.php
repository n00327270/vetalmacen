<?php
$pageTitle = 'Transferencias entre Sucursales';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=stock">Stock</a></li>
            <li class="breadcrumb-item active">Transferencias</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-arrow-left-right"></i> Transferencias entre Sucursales</h2>
            <p class="text-muted">Transfiere productos de una sucursal a otra</p>
        </div>
    </div>

    <!-- Formulario de Transferencia -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="/vetalmacen/public/index.php?url=stock/procesarTransferencia" id="formTransferencia">
                <!-- Paso 1: Seleccionar Sucursales -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="sucursal_origen" class="form-label">
                            <i class="bi bi-box-arrow-right text-danger"></i> Sucursal Origen <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="sucursal_origen" name="sucursal_origen" required>
                            <option value="">Seleccione sucursal de origen</option>
                            <?php foreach ($sucursales as $s): ?>
                            <option value="<?php echo $s['Id']; ?>"><?php echo htmlspecialchars($s['Sede']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="sucursal_destino" class="form-label">
                            <i class="bi bi-box-arrow-in-right text-success"></i> Sucursal Destino <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="sucursal_destino" name="sucursal_destino" required>
                            <option value="">Seleccione sucursal de destino</option>
                            <?php foreach ($sucursales as $s): ?>
                            <option value="<?php echo $s['Id']; ?>"><?php echo htmlspecialchars($s['Sede']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle"></i> <strong>Instrucciones:</strong> 
                    Primero selecciona las sucursales de origen y destino, luego agrega los productos que deseas transferir.
                </div>

                <hr>

                <!-- Paso 2: Buscar y Agregar Productos -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="producto_search" class="form-label">Buscar Producto</label>
                        <input type="text" class="form-control" id="producto_search" 
                               placeholder="Buscar por código o nombre..." disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-primary w-100" id="btnBuscarProducto" disabled>
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </div>

                <!-- Resultados de Búsqueda -->
                <div id="busquedaResultados" class="mb-4" style="display: none;">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Resultados de Búsqueda</h6>
                            <div id="listaProductos"></div>
                        </div>
                    </div>
                </div>

                <!-- Productos Seleccionados para Transferencia -->
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-cart"></i> Productos a Transferir</h5>
                        <div class="table-responsive">
                            <table class="table table-sm" id="tablaTransferencia">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Stock Origen</th>
                                        <th>Cantidad</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="productosTransferencia">
                                    <tr id="emptyMessage">
                                        <td colspan="4" class="text-center text-muted py-3">
                                            <i class="bi bi-inbox"></i> No hay productos agregados
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="mb-3">
                    <label for="observacion" class="form-label">Observaciones</label>
                    <textarea class="form-control" id="observacion" name="observacion" rows="3" 
                              placeholder="Motivo de la transferencia, notas adicionales..."></textarea>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success" id="btnGuardarTransferencia" disabled>
                        <i class="bi bi-check-circle"></i> Procesar Transferencia
                    </button>
                    <button type="reset" class="btn btn-secondary" onclick="limpiarFormulario()">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let productosSeleccionados = [];
let stockDisponible = {};

// Habilitar búsqueda cuando se seleccionan ambas sucursales
document.getElementById('sucursal_origen').addEventListener('change', validarSucursales);
document.getElementById('sucursal_destino').addEventListener('change', validarSucursales);

function validarSucursales() {
    const origen = document.getElementById('sucursal_origen').value;
    const destino = document.getElementById('sucursal_destino').value;
    
    if (origen && destino) {
        if (origen === destino) {
            alert('La sucursal de origen y destino no pueden ser la misma');
            document.getElementById('sucursal_destino').value = '';
            return;
        }
        document.getElementById('producto_search').disabled = false;
        document.getElementById('btnBuscarProducto').disabled = false;
    } else {
        document.getElementById('producto_search').disabled = true;
        document.getElementById('btnBuscarProducto').disabled = true;
    }
}

// Buscar productos
document.getElementById('btnBuscarProducto').addEventListener('click', function() {
    const search = document.getElementById('producto_search').value;
    const sucursalOrigen = document.getElementById('sucursal_origen').value;
    
    if (search.length < 2) {
        alert('Ingrese al menos 2 caracteres para buscar');
        return;
    }

    // Llamada AJAX para buscar productos con stock en sucursal origen
    fetch(`/vetalmacen/public/index.php?url=stock/buscarProductosParaTransferencia&sucursal_id=${sucursalOrigen}&search=${encodeURIComponent(search)}`)
        .then(response => response.json())
        .then(data => {
            mostrarResultados(data);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al buscar productos');
        });
});

function mostrarResultados(productos) {
    const container = document.getElementById('listaProductos');
    container.innerHTML = '';
    
    if (productos.length === 0) {
        container.innerHTML = '<p class="text-muted">No se encontraron productos con stock disponible</p>';
    } else {
        productos.forEach(prod => {
            const div = document.createElement('div');
            div.className = 'border-bottom py-2 d-flex justify-content-between align-items-center';
            div.innerHTML = `
                <div>
                    <strong>${prod.Nombre}</strong> <span class="badge bg-secondary">${prod.Codigo}</span><br>
                    <small class="text-muted">Stock disponible: ${prod.Stock} unidades</small>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="agregarProducto(${prod.Id}, '${prod.Nombre}', '${prod.Codigo}', ${prod.Stock})">
                    <i class="bi bi-plus-circle"></i> Agregar
                </button>
            `;
            container.appendChild(div);
        });
    }
    
    document.getElementById('busquedaResultados').style.display = 'block';
}

function agregarProducto(id, nombre, codigo, stock) {
    // Verificar si ya está agregado
    if (productosSeleccionados.find(p => p.id === id)) {
        alert('Este producto ya está en la lista');
        return;
    }
    
    productosSeleccionados.push({
        id: id,
        nombre: nombre,
        codigo: codigo,
        stock: stock,
        cantidad: 1
    });
    
    stockDisponible[id] = stock;
    actualizarTabla();
    
    document.getElementById('producto_search').value = '';
    document.getElementById('busquedaResultados').style.display = 'none';
}

function actualizarTabla() {
    const tbody = document.getElementById('productosTransferencia');
    tbody.innerHTML = '';
    
    if (productosSeleccionados.length === 0) {
        tbody.innerHTML = `
            <tr id="emptyMessage">
                <td colspan="4" class="text-center text-muted py-3">
                    <i class="bi bi-inbox"></i> No hay productos agregados
                </td>
            </tr>
        `;
        document.getElementById('btnGuardarTransferencia').disabled = true;
    } else {
        productosSeleccionados.forEach((prod, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <strong>${prod.nombre}</strong><br>
                    <small class="text-muted">${prod.codigo}</small>
                    <input type="hidden" name="productos[${index}][id]" value="${prod.id}">
                    <input type="hidden" name="productos[${index}][cantidad]" value="${prod.cantidad}">
                </td>
                <td><span class="badge bg-info">${prod.stock} unidades</span></td>
                <td>
                    <input type="number" class="form-control form-control-sm" style="width: 100px;" 
                           value="${prod.cantidad}" min="1" max="${prod.stock}"
                           onchange="actualizarCantidad(${index}, this.value)" required>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
        document.getElementById('btnGuardarTransferencia').disabled = false;
    }
}

function actualizarCantidad(index, cantidad) {
    cantidad = parseInt(cantidad);
    const prod = productosSeleccionados[index];
    
    if (cantidad > prod.stock) {
        alert(`La cantidad no puede ser mayor al stock disponible (${prod.stock})`);
        cantidad = prod.stock;
    }
    
    if (cantidad < 1) {
        cantidad = 1;
    }
    
    productosSeleccionados[index].cantidad = cantidad;
    
    // Actualizar el input hidden
    document.querySelector(`input[name="productos[${index}][cantidad]"]`).value = cantidad;
}

function eliminarProducto(index) {
    productosSeleccionados.splice(index, 1);
    actualizarTabla();
}

function limpiarFormulario() {
    productosSeleccionados = [];
    stockDisponible = {};
    actualizarTabla();
    document.getElementById('busquedaResultados').style.display = 'none';
    document.getElementById('producto_search').disabled = true;
    document.getElementById('btnBuscarProducto').disabled = true;
}

// Validación antes de enviar
document.getElementById('formTransferencia').addEventListener('submit', function(e) {
    if (productosSeleccionados.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un producto para transferir');
        return false;
    }
    
    if (!confirm('¿Está seguro de procesar esta transferencia? Esta acción no se puede deshacer.')) {
        e.preventDefault();
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>