<?php
$tituloPagina = 'Catálogo Digital | SGBV';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Hero Banner Premium -->
<section class="hero-banner text-center">
    <div class="container position-relative z-1">
        <span class="badge rounded-pill bg-dark border border-secondary px-3 py-2 text-info mb-3">
            <i class="bi bi-sparkles me-1"></i> Sistema de Gestión de Bibliotecas Virtuales 2.0
        </span>
        <h1 class="display-3 fw-extrabold text-gradient mb-3">
            Explora Nuestra <span class="text-gradient-accent">Biblioteca Digital</span>
        </h1>
        <p class="lead text-secondary max-w-600 mx-auto mb-5">
            Descubre miles de libros, audiolibros y artículos científicos. Renta al instante desde tu billetera virtual con control de disponibilidad en tiempo real.
        </p>

        <!-- Motor de Búsqueda y Filtros -->
        <div class="search-container">
            <form id="searchForm" action="<?= BASE_URL ?>buscar" method="GET" class="search-box" onsubmit="return false;">
                <i class="bi bi-search text-primary ms-3 fs-5"></i>
                <input type="text" id="searchInput" name="q" class="search-input" placeholder="Buscar por título, autor o ISBN..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" autocomplete="off">
                
                <select id="categorySelect" name="categoria_id" class="search-select d-none d-md-block ms-2">
                    <option value="0">Todas las categorías</option>
                    <?php if (!empty($categorias) && is_array($categorias)): ?>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ((int)($_GET['categoria_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </form>
            <div class="d-flex justify-content-between align-items-center mt-3 px-2">
                <span id="totalResultsCount" class="text-secondary small fw-medium">
                    <i class="bi bi-collection me-1"></i> <?= count($recursos ?? []) ?> recursos disponibles en el catálogo
                </span>
                <?php if (!empty($_GET['q']) || !empty($_GET['categoria_id'])): ?>
                    <a href="<?= BASE_URL ?>home" class="badge bg-dark border border-secondary text-light text-decoration-none py-1 px-2">
                        <i class="bi bi-x-circle me-1"></i> Limpiar Filtros
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Grid del Catálogo Digital -->
<section class="py-5">
    <div class="container">
        <div class="row g-4" id="booksGrid">
            <?php if (!empty($recursos) && is_array($recursos)): ?>
                <?php foreach ($recursos as $r): ?>
                    <?php
                        $esLibro = $r->tipo === 'libro';
                        $esAudio = $r->tipo === 'audiolibro';
                        $iconoTipo = $esLibro ? 'bi-book-half' : ($esAudio ? 'bi-headphones' : 'bi-file-earmark-text');
                        $colorTipo = $esLibro ? 'bg-primary' : ($esAudio ? 'bg-info text-dark' : 'bg-warning text-dark');
                        $disponible = $r->disponibilidad > 0;
                    ?>
                        <div class="col-12 col-md-6 col-lg-4 col-xl-3 animate-fade-in">
                            <div class="book-card glass-card h-100" style="cursor: pointer;" onclick="openBookModalById(<?= $r->id ?>)">
                                <div class="book-cover-container position-relative overflow-hidden d-flex align-items-center justify-content-center">
                                    <span class="book-badge-type <?= $colorTipo ?>"><i class="bi <?= $iconoTipo ?> me-1"></i> <?= $r->tipo ?></span>
                                    <span class="badge <?= $disponible ? 'bg-success' : 'bg-danger' ?> text-white px-2 py-1 shadow-sm" style="position: absolute; top: 12px; right: 12px; z-index: 2; font-weight: 600;">
                                        <i class="bi <?= $disponible ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?> me-1"></i> Disponibles: <?= $r->disponibilidad ?>
                                    </span>
                                    <?php if (!empty($r->portada) && $r->portada !== 'default_cover.jpg' && file_exists(__DIR__ . '/../public/uploads/portadas/' . $r->portada)): ?>
                                        <img src="<?= BASE_URL ?>public/uploads/portadas/<?= htmlspecialchars($r->portada) ?>" class="book-cover-img w-100 h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($r->titulo) ?>" onerror="this.onerror=null; this.classList.add('d-none'); const p = this.parentElement.querySelector('.book-cover-placeholder'); if(p) p.classList.remove('d-none');">
                                        <i class="bi <?= $iconoTipo ?> book-cover-placeholder d-none"></i>
                                    <?php else: ?>
                                        <i class="bi <?= $iconoTipo ?> book-cover-placeholder"></i>
                                    <?php endif; ?>
                                </div>
                            <div class="book-card-body d-flex flex-column">
                                <div class="mb-2">
                                    <span class="badge bg-dark border border-secondary text-secondary small"><?= htmlspecialchars($r->categoria_nombre) ?></span>
                                </div>
                                <h5 class="book-title text-truncate" title="<?= htmlspecialchars($r->titulo) ?>"><?= htmlspecialchars($r->titulo) ?></h5>
                                <p class="book-author mb-2"><i class="bi bi-person me-1"></i> <?= htmlspecialchars($r->autor) ?> (<?= $r->anio_publicacion ?>)</p>
                                <p class="text-muted small flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= htmlspecialchars($r->descripcion !== '' ? $r->descripcion : 'Sin descripción disponible para este recurso literario.') ?>
                                </p>
                                <div class="mt-3 pt-3 border-top border-secondary d-flex justify-content-between align-items-center" onclick="event.stopPropagation();">
                                    <span class="fw-bold text-gradient-accent fs-5"><?= number_format($r->precio_renta, 2) ?> Créditos ⛃</span>
                                    <div class="d-flex gap-1 align-items-center">
                                        <?php if ($disponible): ?>
                                            <button type="button" class="btn btn-outline-custom btn-sm py-1 px-2 text-info" onclick="addToCart(<?= $r->id ?>, `<?= addslashes($r->titulo) ?>`)" title="Añadir al Carrito">
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-gradient-primary btn-sm rounded-pill px-3" onclick="openConfirmModal(<?= $r->id ?>, `<?= addslashes($r->titulo) ?>`, <?= $r->precio_renta ?>)">
                                                <i class="bi bi-bookmark-plus me-1"></i> Rentar
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-2 py-1" onclick="subscribeToResource(<?= $r->id ?>, `<?= addslashes($r->titulo) ?>`)" title="Recibir aviso cuando esté disponible">
                                                <i class="bi bi-bell-fill me-1"></i> Suscribirme
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 my-3">
                    <div class="glass-card p-5 max-w-600 mx-auto">
                        <i class="bi bi-journal-x fs-1 text-secondary mb-3 d-block"></i>
                        <h4 class="fw-bold">Catálogo Vacio o Sin Coincidencias</h4>
                        <p class="text-muted">No se encontraron libros que coincidan con la búsqueda. Intenta con otros filtros.</p>
                        <a href="<?= BASE_URL ?>home" class="btn btn-outline-custom mt-2"><i class="bi bi-arrow-repeat me-1"></i> Ver Todos los Libros</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Sección de Información / Beneficios -->
<section class="py-5 bg-surface border-top border-bottom border-secondary">
    <div class="container py-3">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-dark border border-secondary rounded-circle p-3 mb-3 text-primary fs-3">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h5 class="fw-bold">Billetera Virtual Integrada</h5>
                    <p class="text-secondary small">Gestiona tu saldo digital y realiza rentas instantáneas sin complicaciones ni esperas prolongadas.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-dark border border-secondary rounded-circle p-3 mb-3 text-info fs-3">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h5 class="fw-bold">Préstamos de 14 Días</h5>
                    <p class="text-secondary small">Disfruta tus recursos digitales por dos semanas completas con conteo automático de días restantes.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-dark border border-secondary rounded-circle p-3 mb-3 text-success fs-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-bold">Seguridad y Control de Edad</h5>
                    <p class="text-secondary small">Verificación de identidad y control estricto de minoría de edad para garantizar un acceso responsable.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
