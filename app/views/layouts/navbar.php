<?php
require_once __DIR__ . '/../../../helpers/PermisoHelper.php';
require_once __DIR__ . '/../../models/Modulo.php';

function renderNavbar() {
    $menuUsuario = PermisoHelper::getMenuDelUsuario();
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
                
                <?php foreach ($menuUsuario as $seccion): ?>
                    
                    <?php if ($seccion['Nivel'] == 1): ?>
                        
                        <?php if (empty($seccion['modulos'])): ?>
                            
                            <!-- ============================================ -->
                            <!-- Sección SIMPLE (sin hijos) - ej: Dashboard  -->
                            <!-- ============================================ -->
                            <li class="nav-item simple-link">
                                <a class="nav-link" href="/vetalmacen/public/index.php?url=<?php echo $seccion['Ruta']; ?>">
                                    <i class="<?php echo $seccion['Icono']; ?>"></i>
                                    <?php echo htmlspecialchars($seccion['Nombre']); ?>
                                </a>
                            </li>
                        
                        <?php else: ?>
                            
                            <!-- ============================================ -->
                            <!-- Sección con MEGA MENU (solo nivel 2)         -->
                            <!-- ============================================ -->
                            <li class="nav-item dropdown mega-dropdown position-relative">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="<?php echo $seccion['Icono']; ?>"></i>
                                    <?php echo htmlspecialchars($seccion['Nombre']); ?>
                                </a>
                                
                                <!-- MEGA MENU -->
                                <div class="mega-menu dropdown-menu">
                                    <div class="mega-menu-grid">
                                        
                                        <?php foreach ($seccion['modulos'] as $modulo): ?>
                                            
                                            <!-- Tarjeta de Módulo (Nivel 2) - sin acciones -->
                                            <div class="mega-menu-module">
                                                
                                                <!-- Icono y Título del Módulo -->
                                                <a href="/vetalmacen/public/index.php?url=<?php echo $modulo['Ruta']; ?>" 
                                                   class="text-decoration-none">
                                                    <i class="<?php echo $modulo['Icono']; ?> mega-menu-module-icon"></i>
                                                    <span class="mega-menu-module-title">
                                                        <?php echo htmlspecialchars($modulo['Nombre']); ?>
                                                    </span>
                                                </a>
                                                
                                                <!-- Descripción del Módulo -->
                                                <?php if (!empty($modulo['Descripcion'])): ?>
                                                    <span class="mega-menu-module-description">
                                                        <?php echo htmlspecialchars($modulo['Descripcion']); ?>
                                                    </span>
                                                <?php endif; ?>
                                                
                                            </div>
                                            
                                        <?php endforeach; ?>
                                        
                                    </div>
                                </div>
                                
                            </li>
                            
                        <?php endif; ?>
                        
                    <?php endif; ?>
                    
                <?php endforeach; ?>
                
            </ul>
            
            <!-- Usuario logueado -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <?php echo htmlspecialchars($currentUser['username']); ?>
                        
                        <!-- Badge dinámico según tipo de usuario -->
                        <?php if (isset($currentUser['es_super']) && $currentUser['es_super'] == 1): ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-star-fill"></i> SuperUsuario
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">
                                <?php echo htmlspecialchars($currentUser['rol_nombre']); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if (PermisoHelper::tienePermiso('usuarios/perfil')): ?>
                        <li>
                            <a class="dropdown-item" href="/vetalmacen/public/index.php?url=usuarios/perfil">
                                <i class="bi bi-person"></i> Mi Perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item text-danger" href="/vetalmacen/public/index.php?url=auth/logout">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php
} // fin renderNavbar()

renderNavbar();
?>