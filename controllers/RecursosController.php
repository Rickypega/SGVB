<?php
declare(strict_types=1);

/**
 * Controlador de Catálogo Público y Gestión de Recursos para lectores
 */
class RecursosController {

    /**
     * Muestra la página principal del catálogo con filtros y buscador
     */
    public function home(): void {
        $termino = trim($_GET['q'] ?? '');
        $categoriaId = (int)($_GET['categoria_id'] ?? 0);

        // Obtener recursos filtrados o catálogo completo
        if (!empty($termino) || $categoriaId > 0) {
            $recursos = Recurso::buscar($termino, $categoriaId);
        } else {
            $recursos = Recurso::obtenerCatalogo();
        }

        // Obtener lista de categorías para el selector del buscador
        $categorias = $this->obtenerCategorias();

        require_once __DIR__ . '/../views/home.php';
    }

    /**
     * Motor de búsqueda interactivo (Soporta respuesta AJAX JSON o vista web)
     */
    public function buscar(): void {
        $termino = trim($_REQUEST['q'] ?? '');
        $categoriaId = (int)($_REQUEST['categoria_id'] ?? 0);
        $esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' 
                  || isset($_REQUEST['ajax']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

        $recursos = Recurso::buscar($termino, $categoriaId);

        if ($esAjax) {
            header('Content-Type: application/json; charset=utf-8');
            // Formatear array de recursos a formato JSON limpio
            $data = [];
            foreach ($recursos as $r) {
                $data[] = [
                    'id' => $r->id,
                    'titulo' => htmlspecialchars($r->titulo),
                    'autor' => htmlspecialchars($r->autor),
                    'isbn' => htmlspecialchars($r->isbn),
                    'categoria_nombre' => htmlspecialchars($r->categoria_nombre),
                    'anio_publicacion' => $r->anio_publicacion,
                    'tipo' => $r->tipo,
                    'disponibilidad' => $r->disponibilidad,
                    'precio_renta' => number_format($r->precio_renta, 2),
                    'portada' => htmlspecialchars($r->portada),
                    'descripcion' => htmlspecialchars($r->descripcion)
                ];
            }
            echo json_encode(['exito' => true, 'total' => count($data), 'recursos' => $data]);
            return;
        }

        // Si es petición web estándar, renderizar vista del catálogo
        $categorias = $this->obtenerCategorias();
        require_once __DIR__ . '/../views/home.php';
    }

    /**
     * Helper privado para obtener el listado de categorías literarias
     *
     * @return array<int, array<string, mixed>>
     */
    private function obtenerCategorias(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    /**
     * Tarea 13: Vista de Política de Privacidad
     */
    public function legalPrivacidad(): void {
        require_once __DIR__ . '/../views/legal/privacidad.php';
    }

    /**
     * Tarea 13: Vista de Términos y Condiciones
     */
    public function legalTerminos(): void {
        require_once __DIR__ . '/../views/legal/terminos.php';
    }

    /**
     * Tarea 13: Vista de Política de Devoluciones
     */
    public function legalDevoluciones(): void {
        require_once __DIR__ . '/../views/legal/devoluciones.php';
    }

    /**
     * Tarea 13: Vista de Contacto
     */
    public function contacto(): void {
        require_once __DIR__ . '/../views/contacto.php';
    }

    /**
     * Tarea 13: Procesar el formulario de contacto
     */
    public function procesarContacto(): void {
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $asunto = trim($_POST['asunto'] ?? '');
        $mensaje = trim($_POST['mensaje'] ?? '');

        if (empty($nombre) || empty($correo) || empty($mensaje)) {
            $_SESSION['error'] = 'Por favor completa los campos obligatorios del formulario.';
            header('Location: ' . BASE_URL . 'contacto');
            exit;
        }

        $_SESSION['exito'] = '¡Gracias por contactarnos, ' . htmlspecialchars($nombre) . '! Hemos recibido tu mensaje y te responderemos pronto a ' . htmlspecialchars($correo) . '.';
        header('Location: ' . BASE_URL . 'contacto');
        exit;
    }

    /**
     * Devuelve los detalles completos de un recurso en formato JSON (Tarea 4 & 8)
     */
    public function detalleJson(): void {
        header('Content-Type: application/json; charset=utf-8');
        $id = (int)($_GET['id'] ?? 0);
        $r = Recurso::porId($id);
        if (!$r) {
            echo json_encode(['exito' => false, 'mensaje' => 'Recurso no encontrado']);
            return;
        }
        echo json_encode([
            'exito' => true,
            'recurso' => [
                'id' => $r->id,
                'titulo' => htmlspecialchars($r->titulo),
                'autor' => htmlspecialchars($r->autor),
                'isbn' => htmlspecialchars($r->isbn),
                'categoria_nombre' => htmlspecialchars($r->categoria_nombre ?? 'General'),
                'anio_publicacion' => $r->anio_publicacion,
                'tipo' => $r->tipo,
                'disponibilidad' => $r->disponibilidad,
                'precio_renta' => $r->precio_renta,
                'portada' => htmlspecialchars($r->portada ?? ''),
                'descripcion' => htmlspecialchars($r->descripcion ?? '')
            ]
        ]);
    }
}
