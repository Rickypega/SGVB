<?php
$tituloPagina = 'Historial Global de Préstamos | SGBV Admin';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <span class="badge bg-dark border border-secondary text-primary px-3 py-2 mb-2">
                <i class="bi bi-clock-history me-1"></i> Registro y Auditoría
            </span>
            <h2 class="fw-extrabold text-gradient mb-1">Historial Global de Préstamos y Rentas</h2>
            <p class="text-secondary mb-0">Consulta detallada de todas las transacciones generadas por los lectores en la biblioteca digital.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>admin/exportar?tipo=historial" class="btn btn-gradient-secondary d-flex align-items-center gap-2" title="Exportar reporte de transacciones en Excel / CSV">
                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV
            </a>
            <button type="button" onclick="window.print()" class="btn btn-outline-custom d-flex align-items-center gap-2" title="Imprimir reporte o guardar como PDF">
                <i class="bi bi-printer"></i> Imprimir / PDF
            </button>
            <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-outline-custom d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- Resumen de Totales -->
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="glass-card p-3 text-center">
                <span class="text-secondary small d-block">Transacciones Totales</span>
                <span class="fs-4 fw-bold text-light"><?= count($prestamos ?? []) ?></span>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="glass-card p-3 text-center">
                <span class="text-secondary small d-block">Monto Acumulado (Ingresos)</span>
                <?php
                    $sumaIngresos = 0;
                    if (!empty($prestamos) && is_array($prestamos)) {
                        foreach ($prestamos as $p) {
                            if ($p->estado !== 'reservado') $sumaIngresos += $p->monto_pagado;
                        }
                    }
                ?>
                <span class="fs-4 fw-bold text-success"><?= number_format($sumaIngresos, 2) ?> Créditos ⛃</span>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="glass-card p-3 text-center">
                <span class="text-secondary small d-block">Límite por Préstamo</span>
                <span class="fs-4 fw-bold text-info">14 Días exactos</span>
            </div>
        </div>
    </div>

    <!-- Tabla Completa del Historial -->
    <div class="glass-card overflow-hidden">
        <!-- Barra de búsqueda instantánea y filtros (Tarea 14) -->
        <div class="p-3 border-bottom border-secondary bg-dark bg-opacity-50 d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div class="input-group" style="max-width: 340px;">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control form-control-sm table-search-input" data-table="historialTable" placeholder="Filtrar por lector, correo, recurso...">
            </div>
            <div class="small text-secondary">
                <i class="bi bi-info-circle me-1"></i> Haz clic en los encabezados para ordenar ascendentemente o descendentemente
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-custom align-middle mb-0 sortable-table" id="historialTable">
                <thead>
                    <tr>
                        <th class="sortable" data-column="0"># <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="1">Usuario (Lector) <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="2">Recurso Rentado <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="3">Fecha de Préstamo <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="4">Fecha Límite (14d) <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="5">Monto (⛃) <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="6">Días Restantes <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="7">Estado Transacción <i class="bi bi-arrow-down-up float-end"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($prestamos) && is_array($prestamos)): ?>
                        <?php foreach ($prestamos as $p): ?>
                            <?php
                                $dias = $p->calcularDiasRestantes();
                                $devuelto = $p->estado === 'devuelto';
                            ?>
                            <tr>
                                <td><span class="badge bg-dark border border-secondary text-secondary">#<?= $p->id ?></span></td>
                                <td>
                                    <div class="fw-bold text-light"><?= htmlspecialchars($p->usuario_nombre) ?></div>
                                    <div class="small text-secondary"><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($p->usuario_correo) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($p->recurso_titulo) ?></div>
                                    <span class="badge bg-dark border border-secondary text-info small text-capitalize"><?= htmlspecialchars($p->recurso_tipo) ?></span>
                                </td>
                                <td>
                                    <div class="small"><?= date('d/m/Y H:i', strtotime($p->fecha_prestamo)) ?></div>
                                </td>
                                <td>
                                    <div class="small text-warning fw-medium"><?= date('d/m/Y H:i', strtotime($p->fecha_devolucion_limite)) ?></div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success"><?= number_format($p->monto_pagado, 2) ?> ⛃</span>
                                </td>
                                <td>
                                    <?php if ($devuelto): ?>
                                        <span class="badge bg-dark border border-secondary text-secondary">Devuelto el <?= date('d/m/Y', strtotime($p->fecha_devolucion_real ?? '')) ?></span>
                                    <?php elseif ($dias < 0): ?>
                                        <span class="badge countdown-red">Retraso: <?= abs($dias) ?> días</span>
                                    <?php elseif ($dias <= 3): ?>
                                        <span class="badge countdown-yellow">Quedan <?= $dias ?> días</span>
                                    <?php else: ?>
                                        <span class="badge countdown-green">Quedan <?= $dias ?> días</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($devuelto): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-3">Devuelto</span>
                                    <?php elseif ($dias < 0): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger px-3">Vencido</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary px-3">Activo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">No existen transacciones históricas en el sistema.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
