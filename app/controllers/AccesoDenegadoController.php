<?php
require_once __DIR__ . '/../../helpers/SessionHelper.php';
require_once __DIR__ . '/../../helpers/AuthHelper.php';

class AccesoDenegadoController {
    
    public function index() {
        // Requerir que esté autenticado para ver esta página
        AuthHelper::requireAuth();
        
        // Mostrar vista de acceso denegado
        require_once __DIR__ . '/../views/acceso-denegado.php';
    }
}