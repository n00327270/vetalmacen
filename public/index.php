<?php
/**
 * Punto de entrada principal del sistema
 * Maneja el enrutamiento básico
 * Fecha: 2026-01-23
 */

// Iniciar sesión
session_start();

// Incluir archivos de configuración
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/azure_config.php';
require_once __DIR__ . '/../config/recaptcha_config.php';

// Incluir helpers
require_once __DIR__ . '/../helpers/SessionHelper.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';

// Obtener la URL solicitada
$url = isset($_GET['url']) ? $_GET['url'] : 'auth/login';
$url = rtrim($url, '/');
$url = explode('/', $url);

// Determinar controlador y método
$map = [
    'productos' => 'Producto',
    'usuarios'  => 'Usuario',
    'categorias' => 'Categoria',
    'reportes' => 'Reporte',
    'proveedores' => 'Proveedor',
    'sucursales' => 'Sucursal',
    'subcategorias' => 'Subcategoria',
    'ordenes_entrada' => 'OrdenEntrada',
    'ordenes_salida' => 'OrdenSalida',
];

$controllerBase = $map[$url[0]] ?? ucfirst($url[0]);
$controllerName = $controllerBase . 'Controller';

$controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';

// Verificar si el controlador existe
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    $controller = new $controllerName();
    
    // Determinar método
    $method = isset($url[1]) ? $url[1] : 'index';
    
    // Obtener parámetros adicionales
    $params = array_slice($url, 2);
    
    // Llamar al método del controlador
    if (method_exists($controller, $method)) {
        call_user_func_array([$controller, $method], $params);
    } else {
        // Método no encontrado
        http_response_code(404);
        echo "Método no encontrado";
    }
} else {
    // Controlador no encontrado
    http_response_code(404);
    echo "Página no encontrada";
}