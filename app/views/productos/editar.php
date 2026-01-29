<?php
$pageTitle = 'Editar Producto';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/vetalmacen/public/index.php?url=productos">Productos</a></li>
            <li class="breadcrumb-item active">Editar Producto</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-pencil"></i> Editar Producto</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="/vetalmacen/public/index.php?url=productos/actualizar" enctype="multipart/form-data" id="formProducto">
                        <input type="hidden" name="id" value="<?php echo $producto['Id']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="codigo" name="codigo" 
                                       value="<?php echo htmlspecialchars($producto['Codigo']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($producto['Nombre']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php echo htmlspecialchars($producto['Descripcion']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="marca" class="form-label">Marca <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="marca" name="marca" 
                                       value="<?php echo htmlspecialchars($producto['Marca']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="precio" class="form-label">Precio (S/) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="precio" name="precio" 
                                       value="<?php echo $producto['Precio']; ?>" step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="categoria_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select" id="categoria_id" required>
                                    <option value="">Seleccione una categoría</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['Id']; ?>" 
                                            <?php echo ($categoria['Id'] == $producto['CategoriaId']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categoria['Nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="subcategoria_id" class="form-label">Subcategoría <span class="text-danger">*</span></label>
                                <select class="form-select" id="subcategoria_id" name="subcategoria_id" required>
                                    <?php foreach ($subcategorias as $subcategoria): ?>
                                    <option value="<?php echo $subcategoria['Id']; ?>" 
                                            <?php echo ($subcategoria['Id'] == $producto['SubCategoriaId']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($subcategoria['Nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Imagen actual -->
                        <?php if ($producto['ImagenUrl']): ?>
                        <div class="mb-3">
                            <label class="form-label">Imagen Actual</label>
                            <div>
                                <img src="<?php echo $producto['ImagenUrl']; ?>" 
                                     alt="<?php echo htmlspecialchars($producto['Nombre']); ?>" 
                                     class="img-thumbnail" 
                                     style="max-width: 300px;">
                                <div class="form-text">
                                    <i class="bi bi-cloud-check"></i> Imagen almacenada en Azure Blob Storage
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="imagen" class="form-label">
                                <?php echo $producto['ImagenUrl'] ? 'Cambiar Imagen' : 'Agregar Imagen'; ?>
                            </label>
                            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/jpeg,image/jpg,image/png">
                            <div class="form-text">
                                <?php if ($producto['ImagenUrl']): ?>
                                <i class="bi bi-info-circle"></i> Si selecciona una nueva imagen, la anterior será eliminada de Azure
                                <br>
                                <?php endif; ?>
                                Formatos: JPG, JPEG, PNG | Tamaño máximo: 5MB
                            </div>
                        </div>

                        <!-- Preview de nueva imagen -->
                        <div class="mb-3" id="imagePreviewContainer" style="display: none;">
                            <label class="form-label">Vista Previa de Nueva Imagen</label>
                            <div>
                                <img id="imagePreview" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px;">
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Actualizar Producto
                            </button>
                            <a href="/vetalmacen/public/index.php?url=productos" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>
                    </form>
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
                            <i class="bi bi-check-circle text-success"></i> Al cambiar la imagen, la anterior se eliminará de Azure
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i> El código debe ser único
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i> Los cambios afectan al stock existente
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Cargar subcategorías dinámicamente al cambiar categoría
document.getElementById('categoria_id').addEventListener('change', function() {
    const categoriaId = this.value;
    const subcategoriaSelect = document.getElementById('subcategoria_id');
    
    if (!categoriaId) {
        subcategoriaSelect.innerHTML = '<option value="">Primero seleccione una categoría</option>';
        subcategoriaSelect.disabled = true;
        return;
    }
    
    fetch(`/vetalmacen/public/index.php?url=productos/getSubcategorias&categoria_id=${categoriaId}`)
        .then(response => response.json())
        .then(data => {
            subcategoriaSelect.innerHTML = '<option value="">Seleccione una subcategoría</option>';
            
            data.forEach(subcategoria => {
                const option = document.createElement('option');
                option.value = subcategoria.Id;
                option.textContent = subcategoria.Nombre;
                subcategoriaSelect.appendChild(option);
            });
            
            subcategoriaSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar las subcategorías');
        });
});

// Preview de nueva imagen
document.getElementById('imagen').addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if (file) {
        if (file.size > 5242880) {
            alert('La imagen no debe superar los 5MB');
            this.value = '';
            document.getElementById('imagePreviewContainer').style.display = 'none';
            return;
        }
        
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Solo se permiten imágenes JPG, JPEG o PNG');
            this.value = '';
            document.getElementById('imagePreviewContainer').style.display = 'none';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreviewContainer').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('imagePreviewContainer').style.display = 'none';
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>