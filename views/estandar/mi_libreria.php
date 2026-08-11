<?php
$tituloPagina = 'Mi Librería Digital SGBV | Biblioteca Personal';
require_once __DIR__ . '/../../layouts/header.php';

$uniqueBooksCount = 0;
if (!empty($misLibros)) {
    $uniqueIds = [];
    foreach ($misLibros as $l) {
        if (isset($l['recurso_id'])) {
            $uniqueIds[$l['recurso_id']] = true;
        }
    }
    $uniqueBooksCount = count($uniqueIds);
}
?>

<div class="container py-5">
    <!-- Header Hero -->
    <div class="glass-card p-4 p-md-5 mb-5 position-relative overflow-hidden border border-secondary shadow-lg">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-primary text-white px-3 py-2 mb-3 rounded-pill fw-bold">
                    <i class="bi bi-collection-play-fill me-1"></i> Colección Personal del Lector
                </span>
                <h1 class="display-5 fw-bold text-gradient mb-3">Mi Librería & Biblioteca Digital</h1>
                <p class="text-secondary fs-6 mb-0 max-w-600">
                    Aquí encontrarás todos los libros digitales y audiolibros que has alquilado o guardado en el sistema. Accede instantáneamente al visor de lectura interactivo o gestiona el tiempo de tus préstamos.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-inline-flex flex-column align-items-center bg-dark bg-opacity-75 p-3 rounded-4 border border-secondary shadow">
                    <span class="text-secondary small text-uppercase fw-bold">Total en Colección</span>
                    <span class="display-6 fw-bold text-info"><?= $uniqueBooksCount ?> <small class="fs-6 text-muted">ejemplares</small></span>
                    <a href="<?= BASE_URL ?>home" class="btn btn-outline-custom btn-sm mt-2 rounded-pill px-3">
                        <i class="bi bi-plus-circle me-1"></i> Explorar Catálogo
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Buscador y Filtros de Mi Librería (Tarea 9) -->
    <div class="glass-card p-4 mb-4 border border-secondary shadow">
        <div class="row g-3 align-items-center">
            <div class="col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-search"></i></span>
                    <input type="text" id="buscarMiLibreria" class="form-control bg-dark text-light border-secondary" placeholder="Buscar por título, autor o categoría..." onkeyup="filtrarMiLibreria()">
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <select id="filtroEstadoLibreria" class="form-select bg-dark text-light border-secondary" onchange="filtrarMiLibreria()">
                    <option value="todos">Todos los estados (Activos e Historial)</option>
                    <option value="activo">Solo préstamos activos / en lectura</option>
                    <option value="devuelto">Historial devuelto / reembolsado</option>
                </select>
            </div>
            <div class="col-lg-3 text-lg-end">
                <span class="text-secondary small" id="contadorFiltrados">Mostrando <b><?= $uniqueBooksCount ?></b> recursos únicos</span>
            </div>
        </div>
    </div>

    <!-- Tabs de Mi Librería -->
    <ul class="nav nav-tabs mb-4 border-secondary" id="libraryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active bg-dark text-light border-secondary border-bottom-0 fw-bold" id="activos-tab" data-bs-toggle="tab" data-bs-target="#activos" type="button" role="tab" aria-controls="activos" aria-selected="true">
                <i class="bi bi-journal-bookmark me-1"></i> Préstamos Vigentes/Activos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link bg-dark text-secondary border-secondary fw-bold" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab" aria-controls="historial" aria-selected="false">
                <i class="bi bi-clock-history me-1"></i> Historial / Préstamos Finalizados
            </button>
        </li>
    </ul>

    <div class="tab-content" id="libraryTabsContent">
        <!-- Pestaña Activos -->
        <div class="tab-pane fade show active" id="activos" role="tabpanel" aria-labelledby="activos-tab">
            <div class="row g-4" id="gridActivos">
                <?php if (!empty($misLibros) && is_array($misLibros)): ?>
                    <?php 
                    $hayActivos = false;
                    foreach ($misLibros as $l): 
                        if ($l['estado'] !== 'activo') continue;
                        $hayActivos = true;
                    ?>
                <?php
                    $esActivo = $l['estado'] === 'activo';
                    $esDevuelto = $l['estado'] === 'devuelto';
                    $esLibro = ($l['recurso_tipo'] ?? 'libro') === 'libro';
                    $iconoTipo = $esLibro ? 'bi-book-half' : 'bi-headphones';
                    $colorTipo = $esLibro ? 'bg-primary' : 'bg-info text-dark';
                    $diasRestantes = 0;
                    if ($esActivo && !empty($l['fecha_devolucion_limite'])) {
                        $hoy = new DateTime();
                        $limite = new DateTime($l['fecha_devolucion_limite']);
                        $diff = $hoy->diff($limite);
                        $diasRestantes = $diff->invert ? -$diff->days : $diff->days;
                    }
                ?>
                <div class="col-12 col-md-6 col-lg-4 col-xl-3 animate-fade-in libreria-item" 
                     data-titulo="<?= htmlspecialchars(strtolower($l['recurso_titulo'] ?? '')) ?>" 
                     data-autor="<?= htmlspecialchars(strtolower($l['recurso_autor'] ?? '')) ?>"
                     data-categoria="<?= htmlspecialchars(strtolower($l['categoria_nombre'] ?? '')) ?>"
                     data-estado="<?= $l['estado'] ?>">
                    <div class="book-card glass-card h-100 d-flex flex-column border border-secondary shadow-sm">
                        <!-- Portada con clic a detalles o visor -->
                        <div class="book-cover-container position-relative overflow-hidden d-flex align-items-center justify-content-center" style="cursor: pointer;" onclick="openBookModalById(<?= $l['recurso_id'] ?>)">
                            <span class="book-badge-type <?= $colorTipo ?>"><i class="bi <?= $iconoTipo ?> me-1"></i> <?= htmlspecialchars($l['recurso_tipo'] ?? 'libro') ?></span>
                            
                            <!-- Estado de Préstamo -->
                            <?php if ($esActivo): ?>
                                <span class="badge bg-success text-white px-2 py-1 shadow-sm" style="position: absolute; top: 12px; right: 12px; z-index: 2;">
                                    <i class="bi bi-clock-history me-1"></i> <?= $diasRestantes >= 0 ? "Restan {$diasRestantes} días" : "Vencido" ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary text-white px-2 py-1 shadow-sm" style="position: absolute; top: 12px; right: 12px; z-index: 2;">
                                    <i class="bi bi-check2-all me-1"></i> Entregado
                                </span>
                            <?php endif; ?>

                            <!-- Imagen / Placeholder (Tarea 5) -->
                            <?php if (!empty($l['recurso_portada']) && $l['recurso_portada'] !== 'default_cover.jpg' && file_exists(__DIR__ . '/../../public/uploads/portadas/' . $l['recurso_portada'])): ?>
                                <img src="<?= BASE_URL ?>public/uploads/portadas/<?= htmlspecialchars($l['recurso_portada']) ?>" 
                                     class="book-cover-img w-100 h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($l['recurso_titulo'] ?? '') ?>"
                                     onerror="this.onerror=null; this.classList.add('d-none'); const p = this.parentElement.querySelector('.book-cover-placeholder'); if(p) p.classList.remove('d-none');">
                                <i class="bi <?= $iconoTipo ?> book-cover-placeholder d-none"></i>
                            <?php else: ?>
                                <i class="bi <?= $iconoTipo ?> book-cover-placeholder"></i>
                            <?php endif; ?>
                        </div>

                        <!-- Cuerpo de la Tarjeta -->
                        <div class="book-card-body d-flex flex-column flex-grow-1 p-3">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <span class="badge bg-dark border border-secondary text-secondary small"><?= htmlspecialchars($l['categoria_nombre'] ?? 'General') ?></span>
                            </div>
                            
                            <h6 class="book-title fw-bold text-light mb-1 text-truncate" title="<?= htmlspecialchars($l['recurso_titulo'] ?? '') ?>">
                                <?= htmlspecialchars($l['recurso_titulo'] ?? '') ?>
                            </h6>
                            <p class="book-author text-secondary small mb-3"><i class="bi bi-person me-1"></i> <?= htmlspecialchars($l['recurso_autor'] ?? '') ?></p>
                            <div class="mt-auto pt-2 border-top border-secondary d-flex flex-column gap-2">
                                <?php if ($esActivo): ?>
                                    <a href="<?= BASE_URL ?>estandar/visor?id=<?= $l['recurso_id'] ?>" class="btn btn-gradient-primary btn-sm w-100 fw-bold py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                        <i class="bi bi-book-half fs-6"></i> Leer / Abrir Visor
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm w-100 fw-bold py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm" disabled>
                                        <i class="bi bi-lock-fill fs-6"></i> Préstamo Finalizado
                                    </button>
                                <?php endif; ?>
                                <div class="d-flex gap-2 align-items-center justify-content-between">
                                    <?php if ($esActivo): ?>
                                        <?php if (((int)$l['ha_leido']) === 0): ?>
                                            <form action="<?= BASE_URL ?>estandar/devolver" method="POST" class="m-0 flex-grow-1">
                                                <input type="hidden" name="prestamo_id" value="<?= $l['id'] ?>">
                                                <input type="hidden" name="anticipado" value="1">
                                                <button type="submit" class="btn btn-outline-warning btn-sm w-100 py-1" title="Anular renta e integrar créditos">
                                                    <i class="bi bi-cash-coin me-1"></i> Reembolsar
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge bg-secondary border border-secondary text-light w-100 py-2 text-center">
                                                <i class="bi bi-lock-fill me-1"></i> No reembolsable
                                            </span>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>estandar/renovar?prestamo_id=<?= $l['id'] ?>" class="btn btn-outline-info btn-sm flex-grow-1 py-1 text-truncate" title="Extender tiempo de préstamo">
                                            <i class="bi bi-calendar-plus me-1"></i> Renovar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
                <?php if (!$hayActivos): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-journal-x fs-1 text-secondary mb-3 d-block"></i>
                        <h5 class="text-secondary">No tienes préstamos activos</h5>
                        <p class="text-muted small">Tus libros actualmente en alquiler aparecerán aquí.</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <h5 class="text-secondary">Tu Librería está vacía</h5>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <!-- Pestaña Historial -->
        <div class="tab-pane fade" id="historial" role="tabpanel" aria-labelledby="historial-tab">
            <div class="row g-4" id="gridHistorial">
                <?php if (!empty($misLibros) && is_array($misLibros)): ?>
                    <?php 
                    $hayHistorial = false;
                    foreach ($misLibros as $l): 
                        if ($l['estado'] !== 'devuelto') continue;
                        $hayHistorial = true;
                        $esLibro = ($l['recurso_tipo'] ?? 'libro') === 'libro';
                        $iconoTipo = $esLibro ? 'bi-book-half' : 'bi-headphones';
                        $colorTipo = $esLibro ? 'bg-primary' : 'bg-info text-dark';
                    ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3 animate-fade-in libreria-item" 
                         data-titulo="<?= htmlspecialchars(strtolower($l['recurso_titulo'] ?? '')) ?>" 
                         data-autor="<?= htmlspecialchars(strtolower($l['recurso_autor'] ?? '')) ?>"
                         data-categoria="<?= htmlspecialchars(strtolower($l['categoria_nombre'] ?? '')) ?>"
                         data-estado="<?= $l['estado'] ?>">
                        <div class="book-card glass-card h-100 d-flex flex-column border border-secondary shadow-sm" style="opacity: 0.85;">
                            <div class="book-cover-container position-relative overflow-hidden d-flex align-items-center justify-content-center">
                                <span class="book-badge-type <?= $colorTipo ?>"><i class="bi <?= $iconoTipo ?> me-1"></i> <?= htmlspecialchars($l['recurso_tipo'] ?? 'libro') ?></span>
                                <span class="badge bg-secondary text-white px-2 py-1 shadow-sm" style="position: absolute; top: 12px; right: 12px; z-index: 2;">
                                    <i class="bi bi-check2-all me-1"></i> Finalizado
                                </span>
                                <?php if (!empty($l['recurso_portada']) && $l['recurso_portada'] !== 'default_cover.jpg' && file_exists(__DIR__ . '/../../public/uploads/portadas/' . $l['recurso_portada'])): ?>
                                    <img src="<?= BASE_URL ?>public/uploads/portadas/<?= htmlspecialchars($l['recurso_portada']) ?>" class="book-cover-img w-100 h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($l['recurso_titulo'] ?? '') ?>">
                                <?php else: ?>
                                    <i class="bi <?= $iconoTipo ?> book-cover-placeholder"></i>
                                <?php endif; ?>
                            </div>
                            <div class="book-card-body d-flex flex-column flex-grow-1 p-3">
                                <h6 class="book-title fw-bold text-light mb-1"><?= htmlspecialchars($l['recurso_titulo'] ?? '') ?></h6>
                                <p class="book-author text-secondary small mb-3"><i class="bi bi-person me-1"></i> <?= htmlspecialchars($l['recurso_autor'] ?? '') ?></p>
                                <div class="mt-auto pt-2 border-top border-secondary">
                                    <button class="btn btn-secondary btn-sm w-100 fw-bold py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm" disabled>
                                        <i class="bi bi-lock-fill fs-6"></i> Préstamo Finalizado
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (!$hayHistorial): ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-clock-history fs-1 text-secondary mb-3 d-block"></i>
                            <h5 class="text-secondary">No hay historial</h5>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Filtrado instantáneo por búsqueda o estado (Tarea 9)
function filtrarMiLibreria() {
    const query = document.getElementById('buscarMiLibreria').value.toLowerCase().trim();
    const estado = document.getElementById('filtroEstadoLibreria').value;
    const items = document.querySelectorAll('.libreria-item');
    let count = 0;

    items.forEach(item => {
        const titulo = item.getAttribute('data-titulo') || '';
        const autor = item.getAttribute('data-autor') || '';
        const categoria = item.getAttribute('data-categoria') || '';
        const itemEstado = item.getAttribute('data-estado') || '';

        const pasaTexto = query === '' || titulo.includes(query) || autor.includes(query) || categoria.includes(query);
        const pasaEstado = estado === 'todos' || itemEstado === estado;

        if (pasaTexto && pasaEstado) {
            item.style.display = '';
            count++;
        } else {
            item.style.display = 'none';
        }
    });

    const display = document.getElementById('contadorFiltrados');
    if (display) display.innerHTML = `Mostrando <b>${count}</b> recursos`;
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
