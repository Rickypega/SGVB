<?php
declare(strict_types=1);

/**
 * Controlador de Administración SGBV (Analíticas, CRUD Recursos e Historial)
 */
class AdminController {

    /**
     * Muestra el Dashboard Principal del Administrador con Analíticas Avanzadas
     */
    public function dashboard(): void {
        $this->requerirRolAdmin();

        // 1. Cálculo de analíticas avanzadas
        $promedioEdad = Usuario::obtenerPromedioEdadLectores();
        $generoPreferido = Prestamo::obtenerGeneroPreferido();
        $gananciasTotales = Prestamo::obtenerGananciasTotales();
        $totalRecursos = Recurso::obtenerTotalRecursos();

        // 2. Datos para gráficos (Chart.js) y tablas rápidas
        $estadisticasCategorias = Recurso::obtenerEstadisticasPorCategoria();
        $prestamos = Prestamo::obtenerTodos();
        $prestamosFlujo30Dias = Prestamo::obtenerFlujoUltimos30Dias(); // Tarea 11: info de últimos 30 días para gráfico de flujo
        $recursos = Recurso::obtenerTodos();
        $categorias = Categoria::obtenerTodas();

        require_once __DIR__ . '/../../views/admin/dashboard.php';
    }

    /**
     * Gestión CRUD de Recursos (Crear, Editar y Eliminar)
     */
    public function recursos(): void {
        $this->requerirRolAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accion = $_POST['accion'] ?? '';

            if ($accion === 'crear') {
                $nombrePortada = 'default_cover.jpg';
                $nombrePdf = null;

                // Procesar subida de Portada física (Tarea 6 & 7)
                if (!empty($_FILES['portada']['name']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['portada']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $dirCover = __DIR__ . '/../../public/uploads/portadas/';
                        if (!is_dir($dirCover)) { mkdir($dirCover, 0777, true); }
                        $nombrePortada = 'cover_' . time() . '_' . rand(100, 999) . '.' . $ext;
                        move_uploaded_file($_FILES['portada']['tmp_name'], $dirCover . $nombrePortada);
                    }
                }

                // Procesar subida de archivo PDF (Tarea 6)
                if (!empty($_FILES['archivo_pdf']['name']) && $_FILES['archivo_pdf']['error'] === UPLOAD_ERR_OK) {
                    $extPdf = strtolower(pathinfo($_FILES['archivo_pdf']['name'], PATHINFO_EXTENSION));
                    if ($extPdf === 'pdf') {
                        $dirPdf = __DIR__ . '/../../public/uploads/pdf/';
                        if (!is_dir($dirPdf)) { mkdir($dirPdf, 0777, true); }
                        $nombrePdf = 'recurso_' . time() . '_' . rand(100, 999) . '.pdf';
                        move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $dirPdf . $nombrePdf);
                    }
                }

                $datos = [
                    'titulo' => trim($_POST['titulo'] ?? ''),
                    'autor' => trim($_POST['autor'] ?? ''),
                    'isbn' => trim($_POST['isbn'] ?? ''),
                    'categoria_id' => (int)($_POST['categoria_id'] ?? 1),
                    'anio_publicacion' => (int)($_POST['anio_publicacion'] ?? date('Y')),
                    'tipo' => $_POST['tipo'] ?? 'libro',
                    'disponibilidad' => (int)($_POST['disponibilidad'] ?? 1),
                    'precio_renta' => (float)($_POST['precio_renta'] ?? 0.00),
                    'portada' => $nombrePortada,
                    'archivo_pdf' => $nombrePdf,
                    'descripcion' => trim($_POST['descripcion'] ?? '')
                ];

                if (empty($datos['titulo']) || empty($datos['autor']) || empty($datos['isbn'])) {
                    $_SESSION['error'] = 'Los campos Título, Autor e ISBN son obligatorios.';
                } else {
                    $nuevoRecurso = Recurso::crear($datos);
                    if ($nuevoRecurso) {
                        $_SESSION['exito'] = '¡Recurso digital creado exitosamente en el catálogo!';
                    } else {
                        $_SESSION['error'] = 'No se pudo crear el recurso. Verifica que el ISBN no esté duplicado.';
                    }
                }
                header('Location: ' . BASE_URL . 'admin/recursos');
                exit;
            }

            if ($accion === 'editar') {
                $id = (int)($_POST['id'] ?? 0);
                $recursoActual = Recurso::porId($id);
                $nombrePortada = $recursoActual ? $recursoActual->portada : 'default_cover.jpg';
                $nombrePdf = $recursoActual ? $recursoActual->archivo_pdf : null;

                // Si se subió una nueva portada en la edición
                if (!empty($_FILES['portada']['name']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['portada']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $dirCover = __DIR__ . '/../../public/uploads/portadas/';
                        if (!is_dir($dirCover)) { mkdir($dirCover, 0777, true); }
                        $nombrePortada = 'cover_' . time() . '_' . rand(100, 999) . '.' . $ext;
                        move_uploaded_file($_FILES['portada']['tmp_name'], $dirCover . $nombrePortada);
                    }
                }

                // Si se subió un nuevo archivo PDF en la edición
                if (!empty($_FILES['archivo_pdf']['name']) && $_FILES['archivo_pdf']['error'] === UPLOAD_ERR_OK) {
                    $extPdf = strtolower(pathinfo($_FILES['archivo_pdf']['name'], PATHINFO_EXTENSION));
                    if ($extPdf === 'pdf') {
                        $dirPdf = __DIR__ . '/../../public/uploads/pdf/';
                        if (!is_dir($dirPdf)) { mkdir($dirPdf, 0777, true); }
                        $nombrePdf = 'recurso_' . time() . '_' . rand(100, 999) . '.pdf';
                        move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $dirPdf . $nombrePdf);
                    }
                }

                $datos = [
                    'titulo' => trim($_POST['titulo'] ?? ''),
                    'autor' => trim($_POST['autor'] ?? ''),
                    'isbn' => trim($_POST['isbn'] ?? ''),
                    'categoria_id' => (int)($_POST['categoria_id'] ?? 1),
                    'anio_publicacion' => (int)($_POST['anio_publicacion'] ?? date('Y')),
                    'tipo' => $_POST['tipo'] ?? 'libro',
                    'disponibilidad' => (int)($_POST['disponibilidad'] ?? 1),
                    'precio_renta' => (float)($_POST['precio_renta'] ?? 0.00),
                    'portada' => $nombrePortada,
                    'archivo_pdf' => $nombrePdf,
                    'descripcion' => trim($_POST['descripcion'] ?? '')
                ];

                if ($id > 0 && Recurso::actualizar($id, $datos)) {
                    $_SESSION['exito'] = '¡El recurso #' . $id . ' ha sido actualizado correctamente!';
                } else {
                    $_SESSION['error'] = 'Error al actualizar el recurso.';
                }
                header('Location: ' . BASE_URL . 'admin/recursos');
                exit;
            }

            if ($accion === 'eliminar') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0 && Recurso::eliminar($id)) {
                    $_SESSION['exito'] = 'El recurso #' . $id . ' ha sido eliminado del catálogo.';
                } else {
                    $_SESSION['error'] = 'No se pudo eliminar el recurso (puede tener préstamos asociados).';
                }
                header('Location: ' . BASE_URL . 'admin/recursos');
                exit;
            }
        }

        // Si es GET, cargar catálogo y categorías para mostrar la tabla con modales
        $recursos = Recurso::obtenerTodos();
        $categorias = Categoria::obtenerTodas();

        require_once __DIR__ . '/../../views/admin/recursos.php';
    }

    /**
     * Muestra el historial global de préstamos del sistema y auditoría de transacciones
     */
    public function historial(): void {
        $this->requerirRolAdmin();

        $prestamos = Prestamo::obtenerTodos();
        require_once __DIR__ . '/../../views/admin/historial.php';
    }

    /**
     * Gestión de Creación de Categorías Literarias (Tarea 13)
     */
    public function guardarCategoria(): void {
        $this->requerirRolAdmin();
        $nombre = trim($_POST['nombre'] ?? '');
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || !empty($_POST['ajax']);

        if (empty($nombre)) {
            $msg = 'El nombre de la categoría no puede estar vacío.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => $msg]);
                exit;
            }
            $_SESSION['error'] = $msg;
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'admin/recursos'));
            exit;
        }

        if (Categoria::crear($nombre)) {
            $msg = '¡Categoría "' . htmlspecialchars($nombre) . '" añadida con éxito!';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => true, 'mensaje' => $msg, 'categorias' => Categoria::obtenerTodas()]);
                exit;
            }
            $_SESSION['exito'] = $msg;
        } else {
            $msg = 'No se pudo crear la categoría (podría ya existir en el sistema).';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => $msg]);
                exit;
            }
            $_SESSION['error'] = $msg;
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'admin/recursos'));
        exit;
    }

    /**
     * Tarea 5: Edición del nombre de categorías existentes
     */
    public function editarCategoria(): void {
        $this->requerirRolAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || !empty($_POST['ajax']);

        if ($id <= 0 || empty($nombre)) {
            $msg = 'Identificador inválido o nombre vacío.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => $msg]);
                exit;
            }
            $_SESSION['error'] = $msg;
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'admin/recursos'));
            exit;
        }

        if (Categoria::actualizar($id, $nombre)) {
            $msg = '¡Categoría actualizada con éxito a "' . htmlspecialchars($nombre) . '"!';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => true, 'mensaje' => $msg, 'categorias' => Categoria::obtenerTodas()]);
                exit;
            }
            $_SESSION['exito'] = $msg;
        } else {
            $msg = 'No se pudo actualizar la categoría.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => $msg]);
                exit;
            }
            $_SESSION['error'] = $msg;
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'admin/recursos'));
        exit;
    }

    /**
     * Tarea 15: Vista separada para la gestión y exportación de reportes
     */
    public function reportes(): void {
        $this->requerirRolAdmin();
        $prestamos = Prestamo::obtenerTodos();
        $recursos = Recurso::obtenerTodos();
        $usuarios = Usuario::obtenerTodos();
        require_once __DIR__ . '/../../views/admin/reportes.php';
    }

    /**
     * Exportación de reportes amigables de transacciones e inventario en formato EXCEL (CSV con BOM) y PDF (HTML Impresión) (Tarea 15)
     */
    public function exportarReporte(): void {
        $this->requerirRolAdmin();
        $tipo = $_GET['tipo'] ?? 'historial';
        $formato = $_GET['formato'] ?? 'csv';

        if ($formato === 'pdf' || $formato === 'html') {
            $tituloReporte = ($tipo === 'recursos') ? 'Inventario del Catálogo Literario SGBV' : (($tipo === 'usuarios') ? 'Listado de Lectores y Cuentas SGBV' : 'Historial Global de Transacciones y Préstamos SGBV');
            $datos = ($tipo === 'recursos') ? Recurso::obtenerTodos() : (($tipo === 'usuarios') ? Usuario::obtenerTodos() : Prestamo::obtenerTodos());
            
            // Renderizar formato amigable en HTML con estilos CSS para impresión PDF o Guardar
            echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>' . $tituloReporte . '</title>';
            echo '<style>body{font-family:Segoe UI,Tahoma,sans-serif;color:#f8fafc;background:#0b0f19;padding:30px;}h1{color:#f8fafc;font-size:24px;border-bottom:2px solid #3b82f6;padding-bottom:10px;}table{width:100%;border-collapse:collapse;margin-top:20px;font-size:13px;}th{background:#0b0f19;color:#f8fafc;text-align:left;padding:10px;border:1px solid #334155;}td{background:#0f172a;color:#f8fafc;padding:8px 10px;border:1px solid #1e293b;}.badge{font-weight:bold;background:#1e293b;color:#f8fafc;padding:2px 6px;border-radius:4px;}.header-info{display:flex;justify-content:space-between;font-size:12px;color:#94a3b8;margin-bottom:15px;}</style></head><body onload="window.print()">';
            echo '<div class="header-info"><span>SGBV - Sistema de Gestión de Bibliotecas Virtuales</span><span>Fecha y Hora de Emisión: ' . date('d/m/Y H:i:s') . '</span></div>';
            echo '<h1>' . htmlspecialchars($tituloReporte) . '</h1>';
            echo '<table><thead><tr>';

            if ($tipo === 'recursos') {
                echo '<th>#</th><th>Título</th><th>Autor</th><th>ISBN</th><th>Categoría</th><th>Tipo</th><th>Año</th><th>Renta (⛃)</th><th>Disponibles</th></tr></thead><tbody>';
                foreach ($datos as $r) {
                    echo '<tr><td><span class="badge">#' . $r->id . '</span></td><td><strong>' . htmlspecialchars($r->titulo) . '</strong></td><td>' . htmlspecialchars($r->autor) . '</td><td>' . htmlspecialchars($r->isbn) . '</td><td>' . htmlspecialchars($r->categoria_nombre) . '</td><td style="text-transform:capitalize;">' . $r->tipo . '</td><td>' . $r->anio_publicacion . '</td><td>' . number_format($r->precio_renta, 2) . ' ⛃</td><td><strong>' . $r->disponibilidad . ' uds</strong></td></tr>';
                }
            } elseif ($tipo === 'usuarios') {
                echo '<th>#</th><th>Nombre Lector</th><th>Correo</th><th>Cédula</th><th>Rol</th><th>Saldo (⛃)</th><th>Fecha Registro</th></tr></thead><tbody>';
                foreach ($datos as $u) {
                    echo '<tr><td><span class="badge">#' . $u->id . '</span></td><td><strong>' . htmlspecialchars($u->nombre) . '</strong></td><td>' . htmlspecialchars($u->correo) . '</td><td>' . htmlspecialchars($u->cedula) . '</td><td>' . ($u->rol_id === 1 ? 'Administrador' : 'Lector Estándar') . '</td><td>' . number_format($u->saldo, 2) . ' ⛃</td><td>' . $u->fecha_registro . '</td></tr>';
                }
            } else {
                echo '<th>#</th><th>Lector</th><th>Correo</th><th>Recurso Rentado</th><th>Tipo</th><th>Fecha Préstamo</th><th>Límite 14d</th><th>Devolución</th><th>Monto (⛃)</th><th>Estado</th></tr></thead><tbody>';
                foreach ($datos as $p) {
                    echo '<tr><td><span class="badge">#' . $p->id . '</span></td><td><strong>' . htmlspecialchars($p->usuario_nombre) . '</strong></td><td>' . htmlspecialchars($p->usuario_correo) . '</td><td>' . htmlspecialchars($p->recurso_titulo) . '</td><td style="text-transform:capitalize;">' . $p->recurso_tipo . '</td><td>' . $p->fecha_prestamo . '</td><td>' . $p->fecha_devolucion_limite . '</td><td>' . ($p->fecha_devolucion_real ?? 'Pendiente') . '</td><td>' . number_format($p->monto_pagado, 2) . ' ⛃</td><td style="text-transform:uppercase;font-weight:bold;">' . $p->estado . '</td></tr>';
                }
            }
            echo '</tbody></table></body></html>';
            exit;
        }

        // Formato CSV (Excel amigable con BOM UTF-8)
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_sgbv_' . $tipo . '_' . date('Y-m-d_His') . '.csv"');
        
        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'w');

        if ($tipo === 'recursos') {
            fputcsv($output, ['#', 'Título', 'Autor', 'ISBN', 'Categoría', 'Tipo', 'Año Publicación', 'Precio Renta (Créditos ⛃)', 'Ejemplares Disponibles']);
            $recursos = Recurso::obtenerTodos();
            foreach ($recursos as $r) {
                fputcsv($output, [
                    '#' . $r->id,
                    $r->titulo,
                    $r->autor,
                    $r->isbn,
                    $r->categoria_nombre,
                    $r->tipo,
                    $r->anio_publicacion,
                    number_format($r->precio_renta, 2),
                    $r->disponibilidad
                ]);
            }
        } elseif ($tipo === 'usuarios') {
            fputcsv($output, ['#', 'Nombre Completo', 'Correo Electrónico', 'Cédula', 'Rol en Sistema', 'Saldo Billetera (Créditos ⛃)', 'Fecha de Registro']);
            $usuarios = Usuario::obtenerTodos();
            foreach ($usuarios as $u) {
                fputcsv($output, [
                    '#' . $u->id,
                    $u->nombre,
                    $u->correo,
                    $u->cedula,
                    $u->rol_id === 1 ? 'Administrador General' : 'Lector Estándar',
                    number_format($u->saldo, 2),
                    $u->fecha_registro
                ]);
            }
        } else {
            fputcsv($output, ['# Préstamo', 'Lector / Cliente', 'Correo Lector', 'Título Rentado', 'Tipo Recurso', 'Fecha Préstamo', 'Fecha Límite 14d', 'Fecha Devolución Real', 'Monto Pagado (Créditos ⛃)', 'Estado Transacción']);
            $prestamos = Prestamo::obtenerTodos();
            foreach ($prestamos as $p) {
                fputcsv($output, [
                    '#' . $p->id,
                    $p->usuario_nombre,
                    $p->usuario_correo,
                    $p->recurso_titulo,
                    $p->recurso_tipo,
                    $p->fecha_prestamo,
                    $p->fecha_devolucion_limite,
                    $p->fecha_devolucion_real ?? 'Pendiente de Devolución',
                    number_format($p->monto_pagado, 2),
                    strtoupper($p->estado)
                ]);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Billetera Personal del Administrador y Resumen de Fondos (Tarea 16)
     * Muestra la billetera virtual personal del admin aparte del resumen de ingresos globales.
     */
    public function billetera(): void {
        $this->requerirRolAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Permitir al admin recargar su propia billetera virtual para pruebas de transacciones personales
            $monto = (float)($_POST['monto'] ?? 0.00);
            if ($monto >= 5.00 && $monto <= 1000.00) {
                $usuarioActual = Usuario::porId($_SESSION['usuario']->id);
                if ($usuarioActual && $usuarioActual->recargarSaldo($monto)) {
                    $_SESSION['usuario'] = $usuarioActual;
                    $_SESSION['exito'] = '¡Has recargado $' . number_format($monto, 2) . ' a tu billetera personal de administrador!';
                } else {
                    $_SESSION['error'] = 'No se pudo procesar la recarga.';
                }
            } else {
                $_SESSION['error'] = 'El monto de recarga para pruebas debe estar entre $5.00 y $1,000.00 USD.';
            }
            header('Location: ' . BASE_URL . 'admin/billetera');
            exit;
        }

        $usuarioRefrescado = Usuario::porId($_SESSION['usuario']->id);
        if ($usuarioRefrescado) {
            $_SESSION['usuario'] = $usuarioRefrescado;
        }
        $usuario = $_SESSION['usuario'];
        $prestamos = Prestamo::obtenerTodos();

        require_once __DIR__ . '/../../views/admin/billetera.php';
    }

    /**
     * Verifica que el usuario tenga sesión activa con rol Administrador (rol_id = 1)
     */
    private function requerirRolAdmin(): void {
        if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario) || $_SESSION['usuario']->rol_id !== 1) {
            $_SESSION['error'] = 'Acceso denegado. Se requieren permisos de Administrador para acceder al panel.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }
}
