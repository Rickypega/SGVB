<?php
$tituloPagina = 'Mi Panel y Billetera | SGBV';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container py-5">
    <!-- Cabecera del Lector y Billetera Virtual -->
    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <div class="glass-card p-4 p-md-5 h-100 d-flex flex-column justify-content-between position-relative">
                <div class="position-absolute top-0 start-50 translate-middle-x w-50" style="height: 3px; background: linear-gradient(90deg, transparent, #6366f1, transparent);"></div>
                <div>
                    <span class="badge bg-dark border border-secondary text-primary px-3 py-2 mb-3">
                        <i class="bi bi-person-check-fill me-1"></i> Panel de Lector Estándar
                    </span>
                    <h2 class="fw-extrabold text-gradient">¡Bienvenido, <?= htmlspecialchars($usuario->nombre ?? 'Lector') ?>!</h2>
                    <p class="text-secondary mb-4">
                        Aquí puedes consultar tus libros en préstamo, revisar el tiempo límite restante de 14 días y administrar los fondos de tu Billetera Digital.
                    </p>
                </div>
                <!-- Tarea 10: No mostrar correo, nacimiento ni cédula verificada por privacidad -->
                <div class="d-flex flex-wrap gap-3 align-items-center pt-3 border-top border-secondary">
                    <span class="badge bg-dark border border-secondary p-2 px-3 text-info">
                        <i class="bi bi-shield-check me-1"></i> Cuenta Activa y Protegida
                    </span>
                    <span class="badge bg-dark border border-secondary p-2 px-3 text-secondary">
                        <i class="bi bi-bookmark-star me-1"></i> Préstamos en Curso: <?= count($prestamos ?? []) ?>
                    </span>
                    <span class="badge bg-dark border border-secondary p-2 px-3 text-secondary">
                        <i class="bi bi-clock me-1"></i> Límite de Renta: 14 Días
                    </span>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <!-- Tarjeta de Billetera Virtual -->
            <div class="glass-card p-4 p-md-5 h-100 position-relative border-primary" style="background: linear-gradient(145deg, rgba(30,41,59,0.9), rgba(15,23,42,0.95));">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-secondary text-uppercase fw-semibold tracking-wider small"><i class="bi bi-wallet-fill text-info me-2"></i>Billetera Virtual SGBV</span>
                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1 small">Activa</span>
                </div>
                
                <div class="mb-4">
                    <div class="text-muted small">Saldo Disponible</div>
                    <div class="display-4 fw-bold text-gradient-accent"><?= number_format((float)($usuario->saldo ?? 0), 2) ?> <span class="fs-6 text-secondary fw-normal">Créditos ⛃</span></div>
                </div>

                <!-- Botones rápidos de recarga para demostración -->
                <form action="<?= BASE_URL ?>estandar/recargar" method="POST">
                    <label class="form-label small text-secondary mb-2"><i class="bi bi-plus-circle me-1"></i> Recarga Rápida (Demo):</label>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <button type="submit" name="monto" value="10.00" class="btn btn-outline-custom w-100 py-2 small fw-bold">+ 10 ⛃</button>
                        </div>
                        <div class="col-4">
                            <button type="submit" name="monto" value="20.00" class="btn btn-outline-custom w-100 py-2 small fw-bold">+ 20 ⛃</button>
                        </div>
                        <div class="col-4">
                            <button type="submit" name="monto" value="50.00" class="btn btn-gradient-secondary w-100 py-2 small fw-bold">+ 50 ⛃</button>
                        </div>
                    </div>
                </form>
                <div class="text-center small text-muted">
                    <i class="bi bi-shield-lock me-1"></i> Fondos aplicables para rentas en el catálogo digital
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Mis Préstamos Activos y Días Restantes -->
    <div class="mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
            <h3 class="fw-bold mb-0"><i class="bi bi-bookmark-check-fill text-primary me-2"></i>Mis Préstamos Activos y Días Restantes</h3>
            <span class="badge bg-dark border border-secondary px-3 py-2 text-secondary">
                Total: <?= count($prestamos ?? []) ?> registros
            </span>
        </div>

        <div class="glass-card overflow-hidden">
            <!-- Barra de búsqueda instantánea y filtros para tabla (Tarea 14) -->
            <div class="p-3 border-bottom border-secondary bg-dark bg-opacity-50 d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div class="input-group" style="max-width: 320px;">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control form-control-sm table-search-input" data-table="prestamosTable" placeholder="Filtrar préstamos activos...">
                </div>
                <div class="small text-secondary">
                    <i class="bi bi-info-circle me-1"></i> Haz clic en los encabezados de columna para ordenar
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0 sortable-table" id="prestamosTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-column="0">Recurso Digital <i class="bi bi-arrow-down-up float-end"></i></th>
                            <th class="sortable" data-column="1">Tipo <i class="bi bi-arrow-down-up float-end"></i></th>
                            <th class="sortable" data-column="2">Fecha de Préstamo <i class="bi bi-arrow-down-up float-end"></i></th>
                            <th class="sortable" data-column="3">Fecha Límite (14 Días) <i class="bi bi-arrow-down-up float-end"></i></th>
                            <th class="sortable" data-column="4">Tiempo Restante <i class="bi bi-arrow-down-up float-end"></i></th>
                            <th class="sortable" data-column="5">Estado <i class="bi bi-arrow-down-up float-end"></i></th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($prestamos) && is_array($prestamos)): ?>
                            <?php foreach ($prestamos as $p): ?>
                                <?php
                                    $diasRestantes = $p->calcularDiasRestantes();
                                    $estDevuelto = $p->estado === 'devuelto';
                                    
                                    if ($estDevuelto) {
                                        $claseBadge = 'bg-dark text-secondary border border-secondary';
                                        $textoBadge = '<i class="bi bi-check-all me-1"></i> Devuelto el ' . date('d/m/Y', strtotime($p->fecha_devolucion_real ?? ''));
                                    } elseif ($diasRestantes < 0) {
                                        $claseBadge = 'countdown-red';
                                        $textoBadge = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Vencido por ' . abs($diasRestantes) . ' días';
                                    } elseif ($diasRestantes <= 3) {
                                        $claseBadge = 'countdown-yellow';
                                        $textoBadge = '<i class="bi bi-clock-history me-1"></i> Restan ' . $diasRestantes . ' días';
                                    } else {
                                        $claseBadge = 'countdown-green';
                                        $textoBadge = '<i class="bi bi-calendar-check me-1"></i> Restan ' . $diasRestantes . ' días';
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-light"><?= htmlspecialchars($p->recurso_titulo) ?></div>
                                        <div class="small text-secondary"><i class="bi bi-person me-1"></i> <?= htmlspecialchars($p->recurso_autor) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-dark border border-secondary text-info text-capitalize"><?= htmlspecialchars($p->recurso_tipo) ?></span>
                                    </td>
                                    <td>
                                        <div class="small"><?= date('d/m/Y H:i', strtotime($p->fecha_prestamo)) ?></div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?= date('d/m/Y H:i', strtotime($p->fecha_devolucion_limite)) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge-countdown <?= $claseBadge ?>"><?= $textoBadge ?></span>
                                    </td>
                                    <td>
                                        <?php if ($estDevuelto): ?>
                                            <span class="badge bg-success-subtle text-success border border-success">Devuelto</span>
                                        <?php elseif ($diasRestantes < 0): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger">Vencido</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary">Activo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if (!$estDevuelto): ?>
                                             <div class="d-flex justify-content-end align-items-center gap-1">
                                                 <?php if (((int)$p->ha_leido) === 0): ?>
                                                     <form action="<?= BASE_URL ?>estandar/devolver" method="POST" class="d-inline m-0">
                                                         <input type="hidden" name="prestamo_id" value="<?= $p->id ?>">
                                                         <input type="hidden" name="anticipado" value="1">
                                                         <button type="submit" class="btn btn-outline-warning btn-sm rounded-pill px-3 py-1" title="Reembolsar y recuperar créditos" onclick="return confirm('¿Confirmas que deseas reembolsar este recurso a la biblioteca? Se te reintegrarán los créditos a tu billetera.');">
                                                             <i class="bi bi-cash-coin me-1"></i> Reembolsar
                                                         </button>
                                                     </form>
                                                 <?php else: ?>
                                                     <span class="badge bg-secondary border border-secondary text-light px-3 py-1" title="El recurso ya fue abierto y leído">
                                                         <i class="bi bi-lock-fill me-1"></i> No reembolsable
                                                     </span>
                                                 <?php endif; ?>

                                                 <a href="<?= BASE_URL ?>estandar/renovar?prestamo_id=<?= $p->id ?>" class="btn btn-outline-info btn-sm rounded-pill px-2 py-1" title="Extender tiempo (+14 días)" onclick="return confirm('¿Deseas renovar o extender este préstamo por 14 días adicionales?');">
                                                     <i class="bi bi-clock-history"></i> Renovar
                                                 </a>
                                             </div>
                                         <?php else: ?>
                                             <span class="text-muted small"><i class="bi bi-check-circle me-1"></i> Entregado / Reembolsado</span>
                                         <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox fs-2 text-secondary mb-2 d-block"></i>
                                    <div class="fw-bold text-muted">Aún no posees préstamos registrados</div>
                                    <div class="small text-secondary">Explora el catálogo inferior y solicita tu primera renta en segundos.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sección: Catálogo Rápido para Rentar desde el Panel -->
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-grid-fill text-info me-2"></i>Catálogo Disponible para Rentar</h3>
                <p class="text-secondary small mb-0">Selecciona el recurso y el débito se realizará con previa confirmación o vía carrito.</p>
            </div>
            <a href="<?= BASE_URL ?>home" class="btn btn-outline-custom btn-sm"><i class="bi bi-arrow-up-right me-1"></i> Ver Catálogo Completo</a>
        </div>

        <div class="row g-4">
            <?php if (!empty($recursos) && is_array($recursos)): ?>
                <?php foreach (array_slice($recursos, 0, 8) as $r): ?>
                    <?php
                        $disponible = $r->disponibilidad > 0;
                        $puedeComprar = ($usuario->saldo ?? 0) >= $r->precio_renta;
                    ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3 animate-fade-in">
                        <div class="book-card glass-card h-100" style="cursor: pointer;" onclick="openBookModalById(<?= $r->id ?>)">
                            <div class="book-cover-container position-relative overflow-hidden d-flex align-items-center justify-content-center">
                                <span class="book-badge-type bg-primary"><i class="bi bi-book-half me-1"></i> <?= $r->tipo ?></span>
                                <span class="badge <?= $disponible ? 'bg-success' : 'bg-danger' ?> text-white px-2 py-1 shadow-sm" style="position: absolute; top: 12px; right: 12px; z-index: 2; font-weight: 600;">
                                    <i class="bi <?= $disponible ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?> me-1"></i> Disponibles: <?= $r->disponibilidad ?>
                                </span>
                                <?php if (!empty($r->portada) && $r->portada !== 'default_cover.jpg' && file_exists(__DIR__ . '/../../public/uploads/portadas/' . $r->portada)): ?>
                                    <img src="<?= BASE_URL ?>public/uploads/portadas/<?= htmlspecialchars($r->portada) ?>" class="book-cover-img w-100 h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($r->titulo) ?>" onerror="this.onerror=null; this.classList.add('d-none'); const p = this.parentElement.querySelector('.book-cover-placeholder'); if(p) p.classList.remove('d-none');">
                                    <i class="bi bi-journal-album book-cover-placeholder d-none"></i>
                                <?php else: ?>
                                    <i class="bi bi-journal-album book-cover-placeholder"></i>
                                <?php endif; ?>
                            </div>
                            <div class="book-card-body d-flex flex-column">
                                <div class="mb-2">
                                    <span class="badge bg-dark border border-secondary text-secondary small"><?= htmlspecialchars($r->categoria_nombre) ?></span>
                                </div>
                                <h5 class="book-title text-truncate" title="<?= htmlspecialchars($r->titulo) ?>"><?= htmlspecialchars($r->titulo) ?></h5>
                                <p class="book-author mb-2 small"><i class="bi bi-person me-1"></i> <?= htmlspecialchars($r->autor) ?> (<?= $r->anio_publicacion ?>)</p>
                                <div class="mt-auto pt-3 border-top border-secondary d-flex justify-content-between align-items-center" onclick="event.stopPropagation();">
                                    <div>
                                        <div class="small text-secondary">Precio Renta</div>
                                        <div class="fw-bold text-gradient-accent fs-5"><?= number_format($r->precio_renta, 2) ?> Créditos ⛃</div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <?php if ($disponible): ?>
                                            <button type="button" class="btn btn-outline-custom btn-sm py-1 px-2 text-info" onclick="addToCart(<?= $r->id ?>, `<?= addslashes($r->titulo) ?>`)" title="Añadir al Carrito">
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-gradient-primary btn-sm rounded-pill px-3" onclick="openConfirmModal(<?= $r->id ?>, `<?= addslashes($r->titulo) ?>`, <?= $r->precio_renta ?>)">
                                                <i class="bi bi-bookmark-plus me-1"></i> Rentar
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-2 py-1" onclick="subscribeToResource(<?= $r->id ?>, `<?= addslashes($r->titulo) ?>`)" title="Avísame">
                                                <i class="bi bi-bell-fill me-1"></i> Suscribirme
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
