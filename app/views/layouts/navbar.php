<?php
$currentUser = SessionHelper::getUser();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="/vetalmacen/public/index.php?url=dashboard">
            <i class="bi bi-heart-pulse-fill me-2"></i>
            <strong>VetAlmacén</strong>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/vetalmacen/public/index.php?url=dashboard">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-box-seam"></i> Inventario
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/vetalmacen/public/index.php?url=productos">Productos</a></li>
                        <li><a class="dropdown-item" href="/vetalmacen/public/index.php?url=categorias">Categorías</a></li>
                        <li><a class="dropdown-item" href="/vetalmacen/public/index.php?url=subcategorias">Subcategorías</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/vetalmacen/public/index.php?url=stock">Ver Stock</a></li>
                    </ul>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-arrow-left-right"></i> Movimientos
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/vetalmacen/public/index.php?url=ordenes_entrada">Órdenes de Entrada</a></li>
                        <li><a class="dropdown-item" href="/vetalmacen/public/index.php?url=ordenes_salida">Órdenes de Salida</a></li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="/vetalmacen/public/index.php?url=proveedores">
                        <i class="bi bi-truck"></i> Proveedores
                    </a>
                </li>
                
                <?php if (AuthHelper::hasAnyRole(['Administrador'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-gear"></i> Configuración
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/vetalmacen/public/index.php?url=sucursales">Sucursales</a></li>
                        <li><a class="dropdown-item" href="/vetalmacen/public/index.php?url=usuarios">Usuarios</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link" href="/vetalmacen/public/index.php?url=reportes">
                        <i class="bi bi-bar-chart"></i> Reportes
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($currentUser['username']); ?>
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($currentUser['rol_nombre']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/vetalmacen/public/index.php?url=usuarios/perfil">
                            <i class="bi bi-person"></i> Mi Perfil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/vetalmacen/public/index.php?url=auth/logout">
                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>