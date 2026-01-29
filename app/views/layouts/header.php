<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'VetAlmacén'; ?> - Sistema de Almacén Veterinaria</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/vetalmacen/public/css/styles.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/vetalmacen/public/images/logo.png">
</head>
<body>
    <?php
    // Mostrar mensajes flash
    $flash = SessionHelper::getFlash();
    if ($flash):
    ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 11; margin-top: 70px;">
        <div class="toast show align-items-center text-white bg-<?php echo $flash['type']; ?> border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <?php echo $flash['message']; ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>