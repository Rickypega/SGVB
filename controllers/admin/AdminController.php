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
     * Tarea 5: Edición del nombre de categorías existentes y auto-eliminación
     */
    public function editarCategoria(): void {
        $this->requerirRolAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || !empty($_POST['ajax']);

        if ($id <= 0) {
            $msg = 'Identificador de categoría inválido.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => $msg]);
                exit;
            }
            $_SESSION['error'] = $msg;
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'admin/recursos'));
            exit;
        }

        // Si el nombre se envía vacío, se interpreta como solicitud de eliminación
        if (empty($nombre)) {
            if (Categoria::tieneRecursos($id)) {
                $msg = 'No se puede eliminar la categoría porque tiene recursos (libros/artículos) vinculados a ella.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['exito' => false, 'mensaje' => $msg]);
                    exit;
                }
                $_SESSION['error'] = $msg;
            } else {
                if (Categoria::eliminar($id)) {
                    $msg = 'Categoría vacía eliminada exitosamente.';
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['exito' => true, 'mensaje' => $msg, 'categorias' => Categoria::obtenerTodas()]);
                        exit;
                    }
                    $_SESSION['exito'] = $msg;
                } else {
                    $msg = 'No se pudo eliminar la categoría.';
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['exito' => false, 'mensaje' => $msg]);
                        exit;
                    }
                    $_SESSION['error'] = $msg;
                }
            }
        } else {
            // Actualización normal
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
        $fechaInicio = !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
        $fechaFin = !empty($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

        $datos = [];
        $tituloReporte = '';
        if ($tipo === 'recursos') {
            $tituloReporte = 'Inventario del Catálogo Literario SGBV';
            $datos = Recurso::obtenerTodos($fechaInicio, $fechaFin);
        } elseif ($tipo === 'usuarios') {
            $tituloReporte = 'Listado de Lectores y Cuentas SGBV';
            $datos = Usuario::obtenerTodos($fechaInicio, $fechaFin);
        } else {
            $tituloReporte = 'Historial Global de Transacciones y Préstamos SGBV';
            $datos = Prestamo::obtenerTodos($fechaInicio, $fechaFin);
        }

        $usuarioSolicitante = $_SESSION['usuario']->nombre ?? 'Administrador';
        $cantidadRegistros = count($datos);

        if ($formato === 'pdf' || $formato === 'html') {
            // Renderizar formato estructurado y profesional para impresión o PDF
            echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>' . $tituloReporte . '</title>';
            echo '<style>
                :root { --primary: #2563eb; --text-main: #1e293b; --text-muted: #64748b; --border: #cbd5e1; --bg-even: #f8fafc; --bg-odd: #ffffff; }
                body { font-family: "Segoe UI", Tahoma, sans-serif; color: var(--text-main); background: #ffffff; margin: 0; padding: 0; font-size: 13px; }
                .container { max-width: 1100px; margin: 0 auto; padding: 40px; }
                .header-inst { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid var(--primary); padding-bottom: 15px; margin-bottom: 25px; }
                .header-inst .brand { display: flex; flex-direction: column; }
                .header-inst .brand h1 { margin: 0; font-size: 22px; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; }
                .header-inst .brand span { font-size: 13px; color: var(--text-muted); }
                .header-inst .meta { text-align: right; font-size: 12px; color: var(--text-muted); line-height: 1.5; }
                .header-inst .meta strong { color: var(--text-main); }
                .report-title { text-align: center; font-size: 18px; margin-bottom: 25px; font-weight: 600; text-transform: uppercase; color: var(--text-main); }
                table { width: 100%; border-collapse: collapse; margin-bottom: 30px; table-layout: fixed; word-wrap: break-word; }
                th, td { border: 1px solid var(--border); padding: 10px 8px; overflow: hidden; }
                th { background: #f1f5f9; color: var(--text-main); font-weight: 600; font-size: 11px; text-transform: uppercase; }
                tr:nth-child(even) { background-color: var(--bg-even); }
                tr:nth-child(odd) { background-color: var(--bg-odd); }
                .text-left { text-align: left; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .badge { background: #e2e8f0; color: #475569; padding: 3px 6px; border-radius: 4px; font-weight: bold; font-size: 10px; }
                @media print {
                    @page { margin: 15mm; @bottom-right { content: "Página " counter(page) " de " counter(pages); font-family: sans-serif; font-size: 10px; color: #64748b; } }
                    body { padding: 0; background: white; }
                    .container { width: 100%; max-width: none; padding: 0; }
                    table { page-break-inside: auto; }
                    tr { page-break-inside: avoid; page-break-after: auto; }
                    td { word-break: break-word; }
                    thead { display: table-header-group; }
                    tfoot { display: table-footer-group; }
                    .print-footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 11px; color: var(--text-muted); border-top: 1px solid var(--border); padding-top: 5px; }
                    body { counter-reset: page; }
                    .page-number:after { counter-increment: page; content: "Página " counter(page); }
                }
                @media screen {
                    .print-footer { display: none; }
                }
            </style></head><body onload="window.print()">';
            echo '<div class="container">';
            echo '<div class="header-inst">';
            echo '<div class="brand"><h1>SGBV - Biblioteca Digital</h1><span>Sistema de Gestión de Bibliotecas Virtuales</span></div>';
            echo '<div class="meta">Generado el: <strong>' . date('d/m/Y') . '</strong> a las <strong>' . date('H:i:s') . '</strong><br>Solicitado por: <strong>' . htmlspecialchars($usuarioSolicitante) . '</strong><br>Total de registros encontrados: <strong>' . $cantidadRegistros . '</strong></div>';
            echo '</div>';
            echo '<div class="report-title">' . htmlspecialchars($tituloReporte) . ($fechaInicio && $fechaFin ? ' (Del ' . $fechaInicio . ' al ' . $fechaFin . ')' : '') . '</div>';
            echo '<table><thead><tr>';

            if ($tipo === 'recursos') {
                echo '<th class="text-center" style="width:5%">#</th><th class="text-left" style="width:25%">Título</th><th class="text-left" style="width:15%">Autor</th><th class="text-left" style="width:15%">ISBN</th><th class="text-left" style="width:15%">Categoría</th><th class="text-center" style="width:10%">Tipo</th><th class="text-right" style="width:15%">Renta (⛃)</th></tr></thead><tbody>';
                $i = 1;
                foreach ($datos as $r) {
                    echo '<tr><td class="text-center"><span class="badge">' . $i++ . '</span></td><td class="text-left"><strong>' . htmlspecialchars($r->titulo) . '</strong></td><td class="text-left">' . htmlspecialchars($r->autor) . '</td><td class="text-left">' . htmlspecialchars($r->isbn) . '</td><td class="text-left">' . htmlspecialchars($r->categoria_nombre) . '</td><td class="text-center" style="text-transform:capitalize;">' . $r->tipo . '</td><td class="text-right">' . number_format($r->precio_renta, 2) . ' ⛃</td></tr>';
                }
            } elseif ($tipo === 'usuarios') {
                echo '<th class="text-center" style="width:5%">#</th><th class="text-left" style="width:25%">Lector</th><th class="text-left" style="width:25%">Correo</th><th class="text-left" style="width:15%">Rol</th><th class="text-right" style="width:15%">Saldo (⛃)</th><th class="text-center" style="width:15%">Fecha Registro</th></tr></thead><tbody>';
                $i = 1;
                foreach ($datos as $u) {
                    echo '<tr><td class="text-center"><span class="badge">' . $i++ . '</span></td><td class="text-left"><strong>' . htmlspecialchars($u->nombre) . '</strong></td><td class="text-left">' . htmlspecialchars($u->correo) . '</td><td class="text-left">' . ($u->rol_id === 1 ? 'Administrador' : 'Lector Estándar') . '</td><td class="text-right">' . number_format($u->saldo, 2) . ' ⛃</td><td class="text-center">' . date('d/m/Y', strtotime($u->fecha_registro)) . '</td></tr>';
                }
            } else {
                echo '<th class="text-center" style="width:5%">#</th><th class="text-left" style="width:20%">Lector</th><th class="text-left" style="width:25%">Recurso</th><th class="text-center" style="width:15%">Préstamo</th><th class="text-center" style="width:15%">Devolución</th><th class="text-right" style="width:10%">Monto</th><th class="text-center" style="width:10%">Estado</th></tr></thead><tbody>';
                $i = 1;
                foreach ($datos as $p) {
                    echo '<tr><td class="text-center"><span class="badge">' . $i++ . '</span></td><td class="text-left"><strong>' . htmlspecialchars($p->usuario_nombre) . '</strong></td><td class="text-left">' . htmlspecialchars($p->recurso_titulo) . '</td><td class="text-center">' . date('d/m/Y', strtotime($p->fecha_prestamo)) . '</td><td class="text-center">' . ($p->fecha_devolucion_real ? date('d/m/Y', strtotime($p->fecha_devolucion_real)) : 'Pendiente') . '</td><td class="text-right">' . number_format($p->monto_pagado, 2) . ' ⛃</td><td class="text-center" style="text-transform:uppercase;font-weight:bold;">' . $p->estado . '</td></tr>';
                }
            }
            echo '</tbody></table>';
            
            // Pie de página para impresión
            echo '<div class="print-footer"><span class="page-number"></span> - Documento generado por SGBV - Biblioteca Digital</div>';
            echo '</div></body></html>';
            exit;
        }

        // Formato Excel (HTML renderizado como XLS)
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=Reporte_SGBV_" . date('Y-m-d') . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo '<html xmlns:o="urn:schemas-microsoft-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        echo '<style>';
        echo 'table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; }';
        echo 'th { background-color: #1E293B; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #CBD5E1; padding: 10px; }';
        echo 'td { border: 1px solid #CBD5E1; padding: 8px; vertical-align: middle; white-space: nowrap; }';
        echo '.zebra { background-color: #F8FAFC; }';
        echo '.auditoria { background-color: #EFF6FF; color: #1E3A8A; font-weight: bold; font-size: 10pt; }';
        echo '.monto { text-align: right; }';
        echo '.centro { text-align: center; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<table>';

        $fechasFiltro = ($fechaInicio && $fechaFin) ? " | Fechas: $fechaInicio al $fechaFin" : "";

        if ($tipo === 'recursos') {
            echo '<tr><td colspan="9" class="auditoria">Reporte generado el: ' . date('d/m/Y H:i:s') . ' | Solicitado por: ' . htmlspecialchars($usuarioSolicitante) . ' | Total Registros: ' . $cantidadRegistros . $fechasFiltro . '</td></tr>';
            echo '<tr><td colspan="9"></td></tr>';
            echo '<thead><tr><th>#</th><th>Título</th><th>Autor</th><th>ISBN</th><th>Categoría</th><th>Tipo</th><th>Año Publicación</th><th>Precio Renta (Créditos)</th><th>Ejemplares Disponibles</th></tr></thead><tbody>';
            $i = 1;
            foreach ($datos as $r) {
                $zebra = ($i % 2 === 0) ? ' class="zebra"' : '';
                echo "<tr$zebra>";
                echo "<td class='centro'>{$i}</td>";
                echo "<td>" . htmlspecialchars($r->titulo) . "</td>";
                echo "<td>" . htmlspecialchars($r->autor) . "</td>";
                echo "<td>" . htmlspecialchars($r->isbn) . "</td>";
                echo "<td>" . htmlspecialchars($r->categoria_nombre) . "</td>";
                echo "<td class='centro'>" . ucfirst($r->tipo) . "</td>";
                echo "<td class='centro'>{$r->anio_publicacion}</td>";
                echo "<td class='monto'>" . number_format($r->precio_renta, 2) . "</td>";
                echo "<td class='centro'>{$r->disponibilidad}</td>";
                echo "</tr>";
                $i++;
            }
        } elseif ($tipo === 'usuarios') {
            echo '<tr><td colspan="6" class="auditoria">Reporte generado el: ' . date('d/m/Y H:i:s') . ' | Solicitado por: ' . htmlspecialchars($usuarioSolicitante) . ' | Total Registros: ' . $cantidadRegistros . $fechasFiltro . '</td></tr>';
            echo '<tr><td colspan="6"></td></tr>';
            echo '<thead><tr><th>#</th><th>Nombre Completo</th><th>Correo Electrónico</th><th>Rol en Sistema</th><th>Saldo Billetera (Créditos)</th><th>Fecha de Registro</th></tr></thead><tbody>';
            $i = 1;
            foreach ($datos as $u) {
                $zebra = ($i % 2 === 0) ? ' class="zebra"' : '';
                echo "<tr$zebra>";
                echo "<td class='centro'>{$i}</td>";
                echo "<td>" . htmlspecialchars($u->nombre) . "</td>";
                echo "<td>" . htmlspecialchars($u->correo) . "</td>";
                echo "<td>" . ($u->rol_id === 1 ? 'Administrador General' : 'Lector Estándar') . "</td>";
                echo "<td class='monto'>" . number_format($u->saldo, 2) . "</td>";
                echo "<td class='centro'>" . date('d/m/Y H:i', strtotime($u->fecha_registro)) . "</td>";
                echo "</tr>";
                $i++;
            }
        } else {
            echo '<tr><td colspan="10" class="auditoria">Reporte generado el: ' . date('d/m/Y H:i:s') . ' | Solicitado por: ' . htmlspecialchars($usuarioSolicitante) . ' | Total Registros: ' . $cantidadRegistros . $fechasFiltro . '</td></tr>';
            echo '<tr><td colspan="10"></td></tr>';
            echo '<thead><tr><th>#</th><th>Lector / Cliente</th><th>Correo Lector</th><th>Título Rentado</th><th>Tipo Recurso</th><th>Fecha Préstamo</th><th>Fecha Límite 14d</th><th>Fecha Devolución Real</th><th>Monto Pagado (Créditos)</th><th>Estado Transacción</th></tr></thead><tbody>';
            $i = 1;
            foreach ($datos as $p) {
                $zebra = ($i % 2 === 0) ? ' class="zebra"' : '';
                echo "<tr$zebra>";
                echo "<td class='centro'>{$i}</td>";
                echo "<td>" . htmlspecialchars($p->usuario_nombre) . "</td>";
                echo "<td>" . htmlspecialchars($p->usuario_correo) . "</td>";
                echo "<td>" . htmlspecialchars($p->recurso_titulo) . "</td>";
                echo "<td class='centro'>" . ucfirst($p->recurso_tipo) . "</td>";
                echo "<td class='centro'>" . date('d/m/Y H:i', strtotime($p->fecha_prestamo)) . "</td>";
                echo "<td class='centro'>" . date('d/m/Y', strtotime($p->fecha_devolucion_limite)) . "</td>";
                echo "<td class='centro'>" . ($p->fecha_devolucion_real ? date('d/m/Y H:i', strtotime($p->fecha_devolucion_real)) : 'Pendiente') . "</td>";
                echo "<td class='monto'>" . number_format($p->monto_pagado, 2) . "</td>";
                echo "<td class='centro'>" . strtoupper($p->estado) . "</td>";
                echo "</tr>";
                $i++;
            }
        }

        echo '</tbody></table></body></html>';
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
     * Verifica que el usuario tenga sesión activa con rol Administrador (rol_id = 1) o Gerente (rol_id = 3)
     */
    private function requerirRolAdmin(): void {
        if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario) || !in_array($_SESSION['usuario']->rol_id, [1, 3])) {
            $_SESSION['error'] = 'Acceso denegado. Se requieren permisos de Administrador o Gerente para acceder al panel.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    /**
     * Verifica que el usuario tenga sesión activa SOLO con rol Administrador (rol_id = 1)
     */
    private function requerirSoloAdmin(): void {
        if (!isset($_SESSION['usuario']) || !($_SESSION['usuario'] instanceof Usuario) || $_SESSION['usuario']->rol_id !== 1) {
            $_SESSION['error'] = 'Acceso denegado. Solo el Administrador General puede acceder a esta área.';
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit;
        }
    }

    /**
     * Gestión de Usuarios (Sólo Admin)
     */
    public function usuarios(): void {
        $this->requerirSoloAdmin();
        $usuarios = Usuario::obtenerStaff();
        require_once __DIR__ . '/../../views/admin/usuarios.php';
    }

    /**
     * Guardar/Crear/Editar un usuario de Staff (Admin o Gerente) con verificación de contraseña de Admin
     */
    public function guardarUsuario(): void {
        $this->requerirSoloAdmin();
        
        $adminActual = $_SESSION['usuario'];
        $passwordConfirmacion = $_POST['admin_password'] ?? '';
        
        if (!$adminActual->verificarPassword($passwordConfirmacion)) {
            $_SESSION['error'] = 'Contraseña de administrador incorrecta. No se aplicaron los cambios.';
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $rol_id = (int)($_POST['rol_id'] ?? 3);
        $password = $_POST['password'] ?? '';

        if (empty($nombre) || empty($correo)) {
            $_SESSION['error'] = 'El nombre y correo son obligatorios.';
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        if ($id > 0) {
            // Actualizar
            $exito = Usuario::actualizarStaff($id, $nombre, $correo, $rol_id, !empty($password) ? $password : null);
            $_SESSION[$exito ? 'exito' : 'error'] = $exito ? 'Usuario actualizado exitosamente.' : 'Error al actualizar usuario.';
        } else {
            // Crear
            if (empty($password)) {
                $_SESSION['error'] = 'La contraseña es obligatoria para nuevos usuarios.';
            } else {
                $exito = Usuario::crearStaff($nombre, $correo, $password, $rol_id);
                $_SESSION[$exito ? 'exito' : 'error'] = $exito ? 'Usuario creado exitosamente.' : 'Error al crear usuario. Verifica que el correo no esté duplicado.';
            }
        }
        
        header('Location: ' . BASE_URL . 'admin/usuarios');
        exit;
    }

    /**
     * Eliminar usuario (Sólo Admin)
     */
    public function eliminarUsuario(): void {
        $this->requerirSoloAdmin();

        $adminActual = $_SESSION['usuario'];
        $passwordConfirmacion = $_POST['admin_password'] ?? '';
        
        if (!$adminActual->verificarPassword($passwordConfirmacion)) {
            $_SESSION['error'] = 'Contraseña de administrador incorrecta. No se eliminó el usuario.';
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id === 1 || $id === $adminActual->id) {
            $_SESSION['error'] = 'No puedes eliminarte a ti mismo ni al Super Administrador.';
        } else {
            $exito = Usuario::eliminar($id);
            $_SESSION[$exito ? 'exito' : 'error'] = $exito ? 'Usuario eliminado exitosamente.' : 'No se pudo eliminar el usuario.';
        }

        header('Location: ' . BASE_URL . 'admin/usuarios');
        exit;
    }

    /**
     * Transferir créditos a un usuario por correo
     */
    public function transferirCreditos(): void {
        $this->requerirSoloAdmin();

        $adminActual = $_SESSION['usuario'];
        $passwordConfirmacion = $_POST['admin_password'] ?? '';
        
        if (!$adminActual->verificarPassword($passwordConfirmacion)) {
            $_SESSION['error'] = 'Contraseña de administrador incorrecta. Transferencia cancelada.';
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $correoDestino = trim($_POST['correo_destino'] ?? '');
        $monto = (float)($_POST['monto'] ?? 0);

        if ($monto <= 0) {
            $_SESSION['error'] = 'El monto debe ser mayor a 0.';
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        $usuarioDestino = Usuario::porCorreo($correoDestino);

        if (!$usuarioDestino) {
            $_SESSION['error'] = 'No se encontró ningún usuario con ese correo electrónico.';
            header('Location: ' . BASE_URL . 'admin/usuarios');
            exit;
        }

        if ($usuarioDestino->recargarSaldo($monto)) {
            // Registrar transacción
            $pdo = Database::getConnection();
            try {
                $stmt = $pdo->prepare("INSERT INTO transacciones_saldo (admin_id, usuario_destino_id, monto) VALUES (:admin, :destino, :monto)");
                $stmt->execute([
                    'admin' => $adminActual->id,
                    'destino' => $usuarioDestino->id,
                    'monto' => $monto
                ]);
                $_SESSION['exito'] = 'Se transfirieron ' . number_format($monto, 2) . ' créditos a ' . htmlspecialchars($usuarioDestino->nombre) . ' (' . htmlspecialchars($correoDestino) . ') exitosamente.';
            } catch (Exception $e) {
                error_log("Error al registrar transacción: " . $e->getMessage());
                $_SESSION['exito'] = 'Créditos transferidos, pero no se pudo registrar en el historial.';
            }
        } else {
            $_SESSION['error'] = 'Ocurrió un error al transferir los créditos.';
        }

        header('Location: ' . BASE_URL . 'admin/usuarios');
        exit;
    }
}
