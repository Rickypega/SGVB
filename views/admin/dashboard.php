<?php
$tituloPagina = 'Dashboard de Analíticas | SGBV Admin';
require_once __DIR__ . '/../../layouts/header.php';

// Preparar datos JSON para Chart.js
$catNombres = [];
$catTotales = [];
if (!empty($estadisticasCategorias) && is_array($estadisticasCategorias)) {
    foreach ($estadisticasCategorias as $ec) {
        $catNombres[] = $ec['categoria'];
        $catTotales[] = (int)$ec['total'];
    }
}

// Tarea 11: Conteo por estados exclusivamente de los Últimos 30 Días para el gráfico de flujo
$conteoActivos30 = 0;
$conteoDevueltos30 = 0;
$flujoArray = !empty($prestamosFlujo30Dias) ? $prestamosFlujo30Dias : (!empty($prestamos) ? $prestamos : []);
if (is_array($flujoArray)) {
    foreach ($flujoArray as $p) {
        if ($p->estado === 'devuelto') {
            $conteoDevueltos30++;
        } else {
            $conteoActivos30++;
        }
    }
}
?>

<div class="container py-5">
    <!-- Cabecera y Botones de Acción -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
        <div>
            <span class="badge bg-dark border border-secondary text-primary px-3 py-2 mb-2">
                <i class="bi bi-shield-lock-fill me-1"></i> Módulo de Administración General
            </span>
            <h2 class="fw-extrabold text-gradient mb-1">Dashboard Ejecutivo y Analíticas</h2>
            <p class="text-secondary mb-0">Supervisión integral de métricas de negocio, rentas acumuladas y demografía de lectores.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>admin/recursos" class="btn btn-gradient-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill"></i> Catálogo
            </a>
            <a href="<?= BASE_URL ?>admin/historial" class="btn btn-outline-custom d-flex align-items-center gap-2">
                <i class="bi bi-clock-history"></i> Historial
            </a>
        </div>
    </div>

    <!-- Tarjetas Estadísticas Principales (Stat Cards) -->
    <div class="row g-4 mb-5">
        <div class="col-12 col-sm-6 col-xl-3 animate-fade-in">
            <div class="stat-card h-100">
                <div class="stat-icon stat-icon-success">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="text-muted small">Ganancias Acumuladas</div>
                <h3 class="fw-bold text-gradient-accent mb-1"><?= number_format((float)($gananciasTotales ?? 0), 2) ?> <span class="fs-6 text-secondary fw-normal">Créditos ⛃</span></h3>
                <div class="text-success small"><i class="bi bi-graph-up-arrow me-1"></i> Rentas procesadas y cobradas</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 animate-fade-in">
            <div class="stat-card h-100">
                <div class="stat-icon stat-icon-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="text-muted small">Edad Promedio de Lectores</div>
                <h3 class="fw-bold text-light mb-1"><?= number_format((float)($promedioEdad ?? 0), 1) ?> <span class="fs-6 text-secondary fw-normal">Años</span></h3>
                <div class="text-info small"><i class="bi bi-calculator me-1"></i> Consulta SQL AVG(TIMESTAMPDIFF)</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 animate-fade-in">
            <div class="stat-card h-100">
                <div class="stat-icon stat-icon-secondary">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div class="text-muted small">Género Preferido</div>
                <h3 class="fw-bold text-light mb-1 text-truncate" title="<?= htmlspecialchars($generoPreferido['categoria'] ?? 'N/A') ?>">
                    <?= htmlspecialchars($generoPreferido['categoria'] ?? 'Sin Rentas') ?>
                </h3>
                <div class="text-secondary small">
                    <i class="bi bi-bookmark-star me-1"></i> <?= $generoPreferido['total'] ?? 0 ?> préstamos en esta categoría
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 animate-fade-in">
            <div class="stat-card h-100">
                <div class="stat-icon stat-icon-accent">
                    <i class="bi bi-journals"></i>
                </div>
                <div class="text-muted small">Disponibles en Catálogo</div>
                <h3 class="fw-bold text-light mb-1"><?= $totalRecursos ?? 0 ?> <span class="fs-6 text-secondary fw-normal">Ejemplares</span></h3>
                <div class="text-warning small"><i class="bi bi-boxes me-1"></i> Libros, audiolibros y artículos</div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Analíticas (Chart.js) -->
    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <div class="glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Distribución de Inventario por Categoría</h5>
                    <span class="badge bg-dark border border-secondary text-secondary">Categorías Literarias</span>
                </div>
                <div style="position: relative; height: 300px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0"><i class="bi bi-pie-chart-fill text-info me-2"></i>Estado del Flujo de Préstamos</h5>
                        <small class="text-secondary">Exclusivo: Información de los últimos 30 días</small>
                    </div>
                    <span class="badge bg-info-subtle text-info border border-info">30 Días</span>
                </div>
                <div style="position: relative; height: 300px; display: flex; align-items: center; justify-content: center;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Vista previa y tabla rápida: Últimos Préstamos Procesados -->
    <div class="glass-card overflow-hidden mb-5">
        <div class="p-4 border-bottom border-secondary d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-arrow-left-right text-success me-2"></i>Transacciones y Préstamos Recientes</h4>
                <p class="text-secondary small mb-0">Muestra los préstamos recientes registrados en la plataforma con búsqueda y orden.</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <div class="input-group input-group-sm" style="max-width: 260px;">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control table-search-input" data-table="dashboardTable" placeholder="Filtrar tabla...">
                </div>
                <a href="<?= BASE_URL ?>admin/historial" class="btn btn-outline-custom btn-sm text-nowrap"><i class="bi bi-eye me-1"></i> Ver Todos</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-custom align-middle mb-0 sortable-table" id="dashboardTable">
                <thead>
                    <tr>
                        <th class="sortable" data-column="0"># <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="1">Lector <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="2">Recurso Digital <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="3">Fecha Préstamo <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="4">Monto Cobrado <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="5">Estado actual <i class="bi bi-arrow-down-up float-end"></i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($prestamos) && is_array($prestamos)): ?>
                        <?php foreach (array_slice($prestamos, 0, 8) as $p): ?>
                            <?php
                                $dias = $p->calcularDiasRestantes();
                                $dev = $p->estado === 'devuelto';
                            ?>
                            <tr>
                                <td><span class="badge bg-dark border border-secondary text-secondary">#<?= $p->id ?></span></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($p->usuario_nombre) ?></div>
                                    <div class="small text-secondary"><?= htmlspecialchars($p->usuario_correo) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-light"><?= htmlspecialchars($p->recurso_titulo) ?></div>
                                    <span class="badge bg-dark border border-secondary text-info small text-capitalize"><?= htmlspecialchars($p->recurso_tipo) ?></span>
                                </td>
                                <td><div class="small"><?= date('d/m/Y H:i', strtotime($p->fecha_prestamo)) ?></div></td>
                                <td><span class="fw-bold text-success"><?= number_format($p->monto_pagado, 2) ?> ⛃</span></td>
                                <td>
                                    <?php if ($dev): ?>
                                        <span class="badge bg-success-subtle text-success border border-success">Devuelto</span>
                                    <?php elseif ($dias < 0): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger">Vencido (<?= abs($dias) ?>d)</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary">Activo (<?= $dias ?>d)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No hay transacciones registradas aún.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Gestión y Añadir Categorías (Tarea 13) -->
<div class="modal fade" id="manageCategoriesModal" tabindex="-1" aria-labelledby="manageCategoriesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold text-gradient" id="manageCategoriesModalLabel"><i class="bi bi-tags me-2"></i>Categorías Literarias del Sistema</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="<?= BASE_URL ?>admin/categorias/guardar" method="POST" class="mb-4">
                    <label class="form-label fw-bold text-light">Añadir Nueva Categoría</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="nombre" required placeholder="Ej. Ciencia Ficción, Historia, Biografías...">
                        <button type="submit" class="btn btn-gradient-primary"><i class="bi bi-plus-lg me-1"></i> Añadir</button>
                    </div>
                </form>

                <h6 class="text-secondary text-uppercase fw-bold small mb-3">Categorías Existentes en Catálogo</h6>
                <div class="d-flex flex-wrap gap-2 max-h-300 overflow-auto p-2 glass-card border-secondary">
                    <?php if (!empty($categorias) && is_array($categorias)): ?>
                        <?php foreach ($categorias as $cat): ?>
                            <span class="badge bg-dark border border-secondary px-3 py-2 text-light fs-6">
                                <i class="bi bi-tag text-warning me-1"></i> <?= htmlspecialchars($cat['nombre'] ?? $cat->nombre ?? '') ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-muted small">No hay categorías cargadas.</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

<!-- Script para inicializar visualizaciones con Chart.js en modo oscuro premium -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Configuración general para gráficos oscuros
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Inter', -apple-system, sans-serif";

    // 1. Gráfico de Barras: Distribución por Categoría
    const ctxCategory = document.getElementById('categoryChart')?.getContext('2d');
    if (ctxCategory) {
        new Chart(ctxCategory, {
            type: 'bar',
            data: {
                labels: <?= json_encode($catNombres) ?>,
                datasets: [{
                    label: 'Cantidad de Recursos',
                    data: <?= json_encode($catTotales) ?>,
                    backgroundColor: 'rgba(99, 102, 241, 0.65)',
                    borderColor: '#6366f1',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: 'rgba(255, 255, 255, 0.06)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Gráfico de Rosca (Doughnut): Estado de Préstamos (Últimos 30 Días)
    const ctxStatus = document.getElementById('statusChart')?.getContext('2d');
    if (ctxStatus) {
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Activos (30d)', 'Devueltos (30d)'],
                datasets: [{
                    data: [<?= $conteoActivos30 ?>, <?= $conteoDevueltos30 ?>],
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(16, 185, 129, 0.8)'
                    ],
                    borderColor: '#1e293b',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 20, usePointStyle: true }
                    }
                },
                cutout: '70%'
            }
        });
    }
});
</script>
