<?php
declare(strict_types=1);

/**
 * Controlador de Préstamos y Panel del Lector Estándar
 */
class PrestamosController {

    /**
     * Muestra el Panel del Lector con sus préstamos activos, billetera virtual y días restantes
     */
    public function panel(): void {
        $usuario = $this->requerirAutenticacion();

        // Refrescar los datos del usuario desde la base de datos (para mostrar saldo y estado actualizados)
        $usuarioRefrescado = Usuario::porId($usuario->id);
        if ($usuarioRefrescado) {
            $usuario = $usuarioRefrescado;
            $_SESSION['usuario'] = $usuario;
        }

        // Tarea 10: Devolución automática de préstamos al cumplirse los 14 días
        Prestamo::devolucionAutomaticaAlVencer();

        // Tarea 2: Sincronizar carrito en memoria con la base de datos
        $_SESSION['carrito'] = Prestamo::obtenerCarritoBD($usuario->id);

        // Obtener la lista de préstamos del usuario con cálculo de días restantes
        $prestamos = Prestamo::obtenerPorUsuario($usuario->id);

        // Obtener el catálogo de recursos disponibles para rentar desde el mismo panel
        $recursos = Recurso::obtenerTodos();

        require_once __DIR__ . '/../views/estandar/panel.php';
    }

    /**
     * Tarea 1: Vista "Mi Librería" mostrando catálogo/biblioteca personalizada de libros alquilados o guardados
     */
    public function miLibreria(): void {
        $usuario = $this->requerirAutenticacion();
        $usuarioRefrescado = Usuario::porId($usuario->id);
        if ($usuarioRefrescado) {
            $usuario = $usuarioRefrescado;
            $_SESSION['usuario'] = $usuario;
        }

        Prestamo::devolucionAutomaticaAlVencer();
        $_SESSION['carrito'] = Prestamo::obtenerCarritoBD($usuario->id);

        // Obtener la biblioteca del usuario con toda la metadata del libro
        $misLibros = Prestamo::obtenerMisLibros($usuario->id);
        require_once __DIR__ . '/../views/estandar/mi_libreria.php';
    }

    /**
     * Procesa la solicitud de renta de un recurso literario o digital
     */
    public function rentar(): void {
        $usuario = $this->requerirAutenticacion();
        $recursoId = (int)($_REQUEST['recurso_id'] ?? 0);

        if ($recursoId <= 0) {
            $_SESSION['error'] = 'Identificador de recurso inválido para procesar la renta.';
            header('Location: ' . BASE_URL . 'estandar/panel');
            exit;
        }

        // Refrescar usuario desde BD para tener el saldo en memoria exacto antes de la transacción
        $usuarioActual = Usuario::porId($usuario->id);
        if (!$usuarioActual) {
            $_SESSION['error'] = 'Sesión de usuario no válida.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $recurso = Recurso::porId($recursoId);
        if (!$recurso) {
            $_SESSION['error'] = 'El recurso literario seleccionado no existe en la base de datos.';
            header('Location: ' . BASE_URL . 'estandar/panel');
            exit;
        }

        // Ejecutar el método transaccional estricto de negocio en el modelo
        $resultado = Prestamo::procesarRenta($usuarioActual, $recurso);

        if ($resultado['exito']) {
            $_SESSION['exito'] = $resultado['mensaje'];
            $_SESSION['usuario'] = $usuarioActual; // Actualizar sesión con el nuevo saldo
        } else {
            $_SESSION['error'] = $resultado['mensaje'];
        }

        header('Location: ' . BASE_URL . 'estandar/panel');
        exit;
    }

    /**
     * Procesa la devolución del ejemplar por parte del lector
     */
    public function devolver(): void {
        $usuario = $this->requerirAutenticacion();
        $prestamoId = (int)($_REQUEST['prestamo_id'] ?? 0);

        if ($prestamoId <= 0) {
            $_SESSION['error'] = 'Identificador de préstamo inválido.';
            header('Location: ' . BASE_URL . 'estandar/panel');
            exit;
        }

        if (!empty($_REQUEST['anticipado'])) {
            $resultado = Prestamo::devolverAnticipado($prestamoId, $usuario->id);
        } else {
            $resultado = Prestamo::devolver($prestamoId, $usuario->id);
        }

        if ($resultado['exito']) {
            $_SESSION['exito'] = $resultado['mensaje'];
        } else {
            $_SESSION['error'] = $resultado['mensaje'];
        }

        header('Location: ' . BASE_URL . 'estandar/panel');
        exit;
    }

    /**
     * Tarea 11: Procesa la renovación de un préstamo por 14 días adicionales (si faltan <= 3 días)
     */
    public function renovar(): void {
        $usuario = $this->requerirAutenticacion();
        $prestamoId = (int)($_REQUEST['prestamo_id'] ?? 0);

        if ($prestamoId <= 0) {
            $_SESSION['error'] = 'Identificador de préstamo inválido para renovar.';
            header('Location: ' . BASE_URL . 'estandar/panel');
            exit;
        }

        $resultado = Prestamo::renovarPrestamo($prestamoId, $usuario->id);
        if ($resultado['exito']) {
            $_SESSION['exito'] = $resultado['mensaje'];
        } else {
            $_SESSION['error'] = $resultado['mensaje'];
        }

        header('Location: ' . BASE_URL . 'estandar/panel');
        exit;
    }

    /**
     * Tarea 15: Visor digital de lectura del recurso con Text-to-Speech y protección
     */
    public function visor(): void {
        $usuario = $this->requerirAutenticacion();
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = 'Recurso no especificado para lectura.';
            header('Location: ' . BASE_URL . 'estandar/panel');
            exit;
        }

        // Verificar si el usuario tiene un préstamo activo para este recurso (o si es el propio Admin)
        $recurso = Recurso::porId($id);
        if (!$recurso) {
            $_SESSION['error'] = 'El recurso literario no existe.';
            header('Location: ' . BASE_URL . 'estandar/panel');
            exit;
        }

        $prestamos = Prestamo::obtenerPorUsuario($usuario->id);
        $prestamoActivo = null;
        foreach ($prestamos as $p) {
            if ($p->recurso_id === $id && $p->estado === 'activo') {
                $prestamoActivo = $p;
                break;
            }
        }

        if (!$prestamoActivo && $usuario->rol_id !== 1) {
            $_SESSION['error'] = 'No tienes una renta activa para leer este material. Debes rentarlo primero en el catálogo.';
            header('Location: ' . BASE_URL . 'home');
            exit;
        }

        // Marcar que el usuario ha abierto y leído el recurso (Tarea 14: no podrá devolver tras leer)
        if ($prestamoActivo) {
            Prestamo::marcarLeido($prestamoActivo->id, $usuario->id);
        }

        require_once __DIR__ . '/../views/estandar/visor.php';
    }

    /**
     * Endpoint de demostración para recargar la Billetera Virtual del Lector
     */
    public function recargar(): void {
        $usuario = $this->requerirAutenticacion();
        $monto = (float)($_POST['monto'] ?? 0.00);

        if ($monto < 5.00 || $monto > 500.00) {
            $_SESSION['error'] = 'El monto de recarga debe estar entre 5.00 y 500.00 Créditos ⛃ por transacción de prueba.';
            header('Location: ' . BASE_URL . 'estandar/panel');
            exit;
        }

        $usuarioActual = Usuario::porId($usuario->id);
        if ($usuarioActual && $usuarioActual->recargarSaldo($monto)) {
            $_SESSION['usuario'] = $usuarioActual;
            $_SESSION['exito'] = '¡Billetera recargada con éxito! Has añadido ' . number_format($monto, 2) . ' Créditos ⛃ a tu saldo virtual.';
        } else {
            $_SESSION['error'] = 'No se pudo procesar la recarga de tu billetera.';
        }

        header('Location: ' . BASE_URL . 'estandar/panel');
        exit;
    }

    /**
     * Añade un recurso digital o literario a la sesión del carrito de préstamos (Tarea 8)
     */
    /**
     * Añade un recurso digital o literario al carrito persistente en BD (Tarea 2 & 8)
     */
    public function agregarCarrito(): void {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || !empty($_POST['ajax']);
        
        if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
            $msg = 'Debes iniciar sesión para añadir libros a tu carrito.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => $msg]);
                exit;
            }
            $_SESSION['error'] = $msg;
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $usuario = $_SESSION['usuario'];
        $recursoId = (int)($_POST['recurso_id'] ?? 0);

        if ($recursoId <= 0) {
            $msg = 'Identificador de recurso inválido.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => $msg]);
                exit;
            }
            $_SESSION['error'] = $msg;
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'estandar/panel'));
            exit;
        }

        $recurso = Recurso::porId($recursoId);
        if (!$recurso || $recurso->disponibilidad <= 0) {
            $msg = 'El recurso no existe o se encuentra temporalmente agotado (Disponibles: 0).';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => $msg]);
                exit;
            }
            $_SESSION['error'] = $msg;
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'estandar/panel'));
            exit;
        }

        // REGLA DE NEGOCIO: Verificar si el usuario ya tiene este libro en préstamo activo
        $prestamosActivos = Prestamo::obtenerPorUsuario($usuario->id);
        foreach ($prestamosActivos as $pAct) {
            if ($pAct->recurso_id === $recursoId && $pAct->estado === 'activo') {
                $msg = 'Ya rentaste o posees una renta activa de "' . $recurso->titulo . '". No puedes volver a añadirlo al carrito ni rentarlo de nuevo; debes extender su tiempo de préstamo en tu librería o panel.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['exito' => false, 'mensaje' => $msg]);
                    exit;
                }
                $_SESSION['error'] = $msg;
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'estandar/panel'));
                exit;
            }
        }

        // Guardar en la base de datos para persistencia total entre dispositivos
        Prestamo::agregarAlCarritoBD($usuario->id, $recursoId);
        $_SESSION['carrito'] = Prestamo::obtenerCarritoBD($usuario->id);

        $msg = '¡"' . $recurso->titulo . '" añadido a tu carrito de préstamos por 14 días!';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'exito' => true, 
                'mensaje' => $msg, 
                'totalItems' => count($_SESSION['carrito']),
                'carrito' => array_values($_SESSION['carrito'])
            ]);
            exit;
        }

        $_SESSION['exito'] = $msg;
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'estandar/panel'));
        exit;
    }

    /**
     * Elimina un recurso del carrito de préstamos de la BD
     */
    public function eliminarCarrito(): void {
        $recursoId = (int)($_POST['recurso_id'] ?? 0);
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || !empty($_POST['ajax']);

        if (isset($_SESSION['usuario']) && $_SESSION['usuario'] instanceof Usuario) {
            Prestamo::eliminarDelCarritoBD($_SESSION['usuario']->id, $recursoId);
            $_SESSION['carrito'] = Prestamo::obtenerCarritoBD($_SESSION['usuario']->id);
        } else {
            if (isset($_SESSION['carrito'][$recursoId])) {
                unset($_SESSION['carrito'][$recursoId]);
            }
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'exito' => true, 
                'totalItems' => count($_SESSION['carrito'] ?? []),
                'carrito' => array_values($_SESSION['carrito'] ?? [])
            ]);
            exit;
        }

        $_SESSION['exito'] = 'Recurso eliminado del carrito.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'estandar/panel'));
        exit;
    }

    /**
     * Procesa todos los ítems del carrito en una sola transacción PDO (Tarea 8)
     */
    public function procesarCarrito(): void {
        $usuario = $this->requerirAutenticacion();
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || !empty($_POST['ajax']);

        $usuarioActual = Usuario::porId($usuario->id);
        if (!$usuarioActual) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => 'Sesión no válida.']);
                exit;
            }
            $_SESSION['error'] = 'Sesión no válida.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $items = $_SESSION['carrito'] ?? [];
        $resultado = Prestamo::procesarCarrito($usuarioActual, $items);

        if ($resultado['exito']) {
            $_SESSION['carrito'] = Prestamo::obtenerCarritoBD($usuarioActual->id); // Vaciar carrito tras la transacción exitosa en BD
            $_SESSION['usuario'] = $usuarioActual;
            $_SESSION['exito'] = $resultado['mensaje'];
        } else {
            $_SESSION['error'] = $resultado['mensaje'];
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'exito' => $resultado['exito'],
                'mensaje' => $resultado['mensaje'],
                'nuevoSaldo' => $usuarioActual->saldo,
                'totalItems' => 0
            ]);
            exit;
        }

        header('Location: ' . BASE_URL . 'estandar/panel');
        exit;
    }

    /**
     * Registra al lector en la lista de notificación de un recurso agotado (Tarea 12)
     */
    public function suscribir(): void {
        $usuario = $this->requerirAutenticacion();
        $recursoId = (int)($_POST['recurso_id'] ?? 0);
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || !empty($_POST['ajax']);

        if ($recursoId <= 0) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => 'Recurso inválido.']);
                exit;
            }
            $_SESSION['error'] = 'Recurso inválido para suscripción.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'estandar/panel'));
            exit;
        }

        $resultado = Suscripcion::suscribir($usuario->id, $recursoId);

        if ($resultado['exito']) {
            $_SESSION['exito'] = $resultado['mensaje'];
        } else {
            $_SESSION['error'] = $resultado['mensaje'];
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($resultado);
            exit;
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'estandar/panel'));
        exit;
    }

    /**
     * Verifica que exista sesión activa de usuario, si no redirige a login
     */
    private function requerirAutenticacion(): Usuario {
        if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario)) {
            $_SESSION['error'] = 'Debes iniciar sesión para acceder a tu panel y gestionar préstamos.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        return $_SESSION['usuario'];
    }
}
