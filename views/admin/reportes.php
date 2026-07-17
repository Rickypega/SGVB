<?php
$tituloPagina = 'Central de Reportes y Analíticas | SGBV Admin';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <span class="badge bg-dark border border-secondary text-info px-3 py-2 mb-2">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Auditoría y Exportación de Datos
            </span>
            <h2 class="fw-extrabold text-gradient mb-1">Centro Especializado de Reportes</h2>
            <p class="text-secondary mb-0">Genera reportes personalizados en formato Excel (CSV UTF-8) o PDF / Imprimible con datos en vivo.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-outline-custom">
                <i class="bi bi-arrow-left me-1"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- Tarjetas de Exportación Rápida -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle p-3 mb-3 fs-3">
                        <i class="bi bi-book-half"></i>
                    </div>
                    <h5 class="fw-bold text-light">Inventario de Recursos</h5>
                    <p class="text-secondary small">Descarga el catálogo completo de libros, precios, existencias e ISBN registrados.</p>
                </div>
                <div class="d-flex gap-2 mt-3 pt-3 border-top border-secondary">
                    <a href="<?= BASE_URL ?>admin/reportes/exportar?tipo=recursos&formato=csv" class="btn btn-gradient-secondary btn-sm flex-fill d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-file-earmark-excel"></i> Excel (CSV)
                    </a>
                    <a href="<?= BASE_URL ?>admin/reportes/exportar?tipo=recursos&formato=pdf" target="_blank" class="btn btn-outline-custom btn-sm flex-fill d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-file-earmark-pdf"></i> PDF / Print
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info rounded-circle p-3 mb-3 fs-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5 class="fw-bold text-light">Padrón de Lectores</h5>
                    <p class="text-secondary small">Reporte completo de cuentas, saldos en billetera, cédulas e historiales de registro.</p>
                </div>
                <div class="d-flex gap-2 mt-3 pt-3 border-top border-secondary">
                    <a href="<?= BASE_URL ?>admin/reportes/exportar?tipo=usuarios&formato=csv" class="btn btn-gradient-secondary btn-sm flex-fill d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-file-earmark-excel"></i> Excel (CSV)
                    </a>
                    <a href="<?= BASE_URL ?>admin/reportes/exportar?tipo=usuarios&formato=pdf" target="_blank" class="btn btn-outline-custom btn-sm flex-fill d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-file-earmark-pdf"></i> PDF / Print
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle p-3 mb-3 fs-3">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                    <h5 class="fw-bold text-light">Flujo y Préstamos</h5>
                    <p class="text-secondary small">Historial auditable de rentas, devoluciones, fechas límites y montos cobrados.</p>
                </div>
                <div class="d-flex gap-2 mt-3 pt-3 border-top border-secondary">
                    <a href="<?= BASE_URL ?>admin/reportes/exportar?tipo=historial&formato=csv" class="btn btn-gradient-secondary btn-sm flex-fill d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-file-earmark-excel"></i> Excel (CSV)
                    </a>
                    <a href="<?= BASE_URL ?>admin/reportes/exportar?tipo=historial&formato=pdf" target="_blank" class="btn btn-outline-custom btn-sm flex-fill d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-file-earmark-pdf"></i> PDF / Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Vista previa de transacciones y filtros en vivo -->
    <div class="glass-card overflow-hidden">
        <div class="p-4 border-bottom border-secondary bg-dark bg-opacity-50 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="fw-bold mb-0 text-light"><i class="bi bi-table me-2 text-primary"></i>Vista Previa de Transacciones Recientes</h5>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control form-control-sm table-search-input" data-table="reportesTable" placeholder="Buscar transacción...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-custom align-middle mb-0 sortable-table" id="reportesTable">
                <thead>
                    <tr>
                        <th class="sortable" data-column="0"># <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="1">Lector <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="2">Recurso <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="3">Fecha Préstamo <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="4">Límite / Devolución <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="5">Monto <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="6">Estado <i class="bi bi-arrow-down-up float-end"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($prestamos) && is_array($prestamos)): ?>
                        <?php foreach (array_slice($prestamos, 0, 50) as $p): ?>
                            <tr>
                                <td><span class="badge bg-dark border border-secondary text-secondary">#<?= $p->id ?></span></td>
                                <td>
                                    <div class="fw-bold text-light"><?= htmlspecialchars($p->usuario_nombre) ?></div>
                                    <div class="small text-secondary"><?= htmlspecialchars($p->usuario_correo) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-light"><?= htmlspecialchars($p->recurso_titulo) ?></div>
                                    <span class="badge bg-dark border border-secondary text-info text-capitalize small"><?= $p->recurso_tipo ?></span>
                                </td>
                                <td class="text-secondary small"><?= $p->fecha_prestamo ?></td>
                                <td>
                                    <div class="small text-warning">Límite: <?= $p->fecha_devolucion_limite ?></div>
                                    <?php if (!empty($p->fecha_devolucion_real)): ?>
                                        <div class="small text-success">Devuelto: <?= $p->fecha_devolucion_real ?></div>
                                    <?php else: ?>
                                        <div class="small text-muted">En transcurso</div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="fw-bold text-gradient-accent"><?= number_format($p->monto_pagado, 2) ?> ⛃</span></td>
                                <td>
                                    <?php
                                        $badgeEstado = 'bg-primary-subtle text-primary border border-primary';
                                        if ($p->estado === 'devuelto') { $badgeEstado = 'bg-success-subtle text-success border border-success'; }
                                    ?>
                                    <span class="badge <?= $badgeEstado ?> px-3 text-uppercase"><?= $p->estado ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No hay registros para mostrar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
