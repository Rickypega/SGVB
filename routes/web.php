<?php
declare(strict_types=1);

/**
 * Enrutador modular del sistema SGBV
 */
class Router {
    private array $routes = [];

    /**
     * Registra una ruta GET
     */
    public function get(string $path, string $handler): void {
        $this->routes['GET'][trim($path, '/')] = $handler;
    }

    /**
     * Registra una ruta POST
     */
    public function post(string $path, string $handler): void {
        $this->routes['POST'][trim($path, '/')] = $handler;
    }

    /**
     * Despacha la petición actual al controlador y método correspondiente
     */
    public function dispatch(string $url, string $method): void {
        $cleanUrl = trim($url, '/');
        
        // Si la URL está vacía, redirigir por defecto a 'home'
        if ($cleanUrl === '') {
            $cleanUrl = 'home';
        }

        $handler = $this->routes[$method][$cleanUrl] ?? null;

        // Si no existe para el método actual, verificar en GET/POST como respaldo o mostrar 404
        if (!$handler) {
            $otherMethod = ($method === 'GET') ? 'POST' : 'GET';
            if (isset($this->routes[$otherMethod][$cleanUrl])) {
                $handler = $this->routes[$otherMethod][$cleanUrl];
            } else {
                $this->send404();
                return;
            }
        }

        // Dividir Controller@method
        [$controllerName, $actionName] = explode('@', $handler);

        // Ubicar e instanciar el controlador
        $controllerPath = $this->resolveControllerPath($controllerName);

        if (!file_exists($controllerPath)) {
            error_log("No se encuentra el archivo del controlador: " . $controllerPath);
            $this->send404();
            return;
        }

        require_once $controllerPath;

        if (!class_exists($controllerName)) {
            error_log("No existe la clase $controllerName en $controllerPath");
            $this->send404();
            return;
        }

        $controllerInstance = new $controllerName();

        if (!method_exists($controllerInstance, $actionName)) {
            error_log("No existe el método $actionName en la clase $controllerName");
            $this->send404();
            return;
        }

        // Ejecutar acción
        $controllerInstance->$actionName();
    }

    /**
     * Resuelve la ruta física del controlador (incluyendo subcarpetas si aplica)
     */
    private function resolveControllerPath(string $controllerName): string {
        if ($controllerName === 'AdminController') {
            return __DIR__ . '/../controllers/admin/AdminController.php';
        }
        return __DIR__ . '/../controllers/' . $controllerName . '.php';
    }

    /**
     * Respuesta 404 Not Found
     */
    private function send404(): void {
        http_response_code(404);
        require_once __DIR__ . '/../layouts/header.php';
        echo '<div class="container my-5 text-center py-5">';
        echo '<div class="card shadow-lg p-5 border-0 rounded-4 glass-card max-w-600 mx-auto">';
        echo '<h1 class="display-1 fw-bold text-gradient"><i class="bi bi-exclamation-triangle-fill"></i> 404</h1>';
        echo '<h3 class="fw-bold mt-3">Página no encontrada</h3>';
        echo '<p class="text-muted mt-2">La ruta que intentas consultar no existe o ha sido movida.</p>';
        echo '<div class="mt-4"><a href="' . BASE_URL . 'home" class="btn btn-primary rounded-pill px-4 py-2"><i class="bi bi-house-door me-2"></i>Volver al Catálogo</a></div>';
        echo '</div></div>';
        require_once __DIR__ . '/../layouts/footer.php';
    }
}

// Configuración de rutas SGBV
$router = new Router();

// Rutas Públicas / Catálogo
$router->get('home', 'RecursosController@home');
$router->get('buscar', 'RecursosController@buscar');
$router->post('buscar', 'RecursosController@buscar');

// Rutas de Autenticación
$router->get('login', 'AuthController@login');
$router->post('login', 'AuthController@login');
$router->get('registro', 'AuthController@registro');
$router->post('registro', 'AuthController@registro');
$router->get('logout', 'AuthController@logout');

// Rutas Lector Estándar & Visor Digital
$router->get('estandar/panel', 'PrestamosController@panel');
$router->post('estandar/rentar', 'PrestamosController@rentar');
$router->get('estandar/rentar', 'PrestamosController@rentar');
$router->post('estandar/devolver', 'PrestamosController@devolver');
$router->get('estandar/devolver', 'PrestamosController@devolver');
$router->post('estandar/recargar', 'PrestamosController@recargar');
$router->post('estandar/carrito/agregar', 'PrestamosController@agregarCarrito');
$router->post('estandar/carrito/eliminar', 'PrestamosController@eliminarCarrito');
$router->post('estandar/carrito/procesar', 'PrestamosController@procesarCarrito');
$router->post('estandar/suscribir', 'PrestamosController@suscribir');
$router->get('estandar/renovar', 'PrestamosController@renovar');
$router->post('estandar/renovar', 'PrestamosController@renovar');
$router->get('estandar/visor', 'PrestamosController@visor');
$router->get('libreria/mis_libros', 'PrestamosController@miLibreria');
$router->get('mi_libreria', 'PrestamosController@miLibreria');
$router->get('api/recurso', 'RecursosController@detalleJson');

// Rutas Configuración de Usuario (Tarea 12)
$router->get('usuario/configuracion', 'UsuarioController@configuracion');
$router->post('usuario/actualizar', 'UsuarioController@actualizarDatos');
$router->post('usuario/password', 'UsuarioController@cambiarPassword');
$router->post('usuario/eliminar', 'UsuarioController@eliminarCuenta');

// Rutas Administrador
$router->get('admin/dashboard', 'AdminController@dashboard');
$router->get('admin/recursos', 'AdminController@recursos');
$router->post('admin/recursos', 'AdminController@recursos');
$router->get('admin/historial', 'AdminController@historial');
$router->post('admin/categorias', 'AdminController@guardarCategoria');
$router->post('admin/categorias/editar', 'AdminController@editarCategoria');
$router->get('admin/reportes', 'AdminController@reportes');
$router->get('admin/reportes/exportar', 'AdminController@exportarReporte');
$router->get('admin/exportar', 'AdminController@exportarReporte');
$router->get('admin/billetera', 'AdminController@billetera');
$router->post('admin/billetera', 'AdminController@billetera');

// Rutas Legales y Contacto (Tarea 13)
$router->get('legal/privacidad', 'RecursosController@legalPrivacidad');
$router->get('legal/terminos', 'RecursosController@legalTerminos');
$router->get('legal/devoluciones', 'RecursosController@legalDevoluciones');
$router->get('contacto', 'RecursosController@contacto');
$router->post('contacto', 'RecursosController@procesarContacto');
