<?php
declare(strict_types=1);

// Cargar dependencias de configuración y modelos de negocio antes de iniciar sesión
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Rol.php';
require_once __DIR__ . '/models/Permiso.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Recurso.php';
require_once __DIR__ . '/models/Prestamo.php';
require_once __DIR__ . '/models/Categoria.php';
require_once __DIR__ . '/models/Suscripcion.php';

// Iniciar sesión global con las clases ya definidas en memoria
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de codificación y zona horaria
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Santo_Domingo');

// URL base de producción para InfinityFree
define('BASE_URL', 'https://sgbv.infinityfreeapp.com/');

// Cargar enrutador
require_once __DIR__ . '/routes/web.php';

// Obtener URL amigable del parámetro o desde $_SERVER['REQUEST_URI'] si no se reescribe
$url = $_GET['url'] ?? '';

// Si url está vacía e intentamos deducir desde REQUEST_URI (útil en pruebas directas)
if (empty($url) && isset($_SERVER['REQUEST_URI'])) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
    $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '/';
    // Remover prefijo del path base si existe
    if ($basePath !== '/' && str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath));
    }
    $url = trim($path, '/');
    if ($url === 'index.php') {
        $url = 'home';
    }
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Despachar la petición al controlador correspondiente
$router->dispatch($url, $method);
