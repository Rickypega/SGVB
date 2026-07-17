</main>

<!-- 1. Offcanvas: Carrito de Préstamos -->
<div class="offcanvas offcanvas-end text-bg-dark border-start border-secondary shadow-lg" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title text-gradient fw-bold" id="cartOffcanvasLabel">
            <i class="bi bi-cart3 text-info me-2"></i>Carrito de Préstamos
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4 d-flex flex-column">
        <div id="cartItemsContainer" class="flex-grow-1 overflow-auto mb-3">
            <?php
                $carritoItems = $_SESSION['carrito'] ?? [];
                $cartTotal = 0.0;
            ?>
            <?php if (!empty($carritoItems)): ?>
                <?php foreach ($carritoItems as $item): ?>
                    <?php $cartTotal += (float)$item['precio_renta']; ?>
                    <div class="glass-card p-3 mb-2 d-flex justify-content-between align-items-center cart-item-row" id="cart-row-<?= $item['id'] ?>" style="cursor: pointer;" onclick="openBookModalById(<?= $item['id'] ?>)">
                        <div>
                            <div class="fw-bold text-light small"><?= htmlspecialchars($item['titulo']) ?></div>
                            <div class="text-secondary" style="font-size: 0.8rem;"><?= htmlspecialchars($item['autor']) ?> &bull; <span class="text-info text-capitalize"><?= htmlspecialchars($item['tipo']) ?></span></div>
                            <div class="text-success fw-bold small mt-1"><?= number_format((float)$item['precio_renta'], 2) ?> Créditos ⛃</div>
                        </div>
                        <form action="<?= BASE_URL ?>estandar/carrito/eliminar" method="POST" class="m-0 remove-cart-form">
                            <input type="hidden" name="recurso_id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-outline-custom btn-sm py-1 px-2 text-danger" title="Eliminar del carrito" onclick="event.stopPropagation();">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5 text-secondary" id="emptyCartMessage">
                    <i class="bi bi-cart-x fs-1 d-block mb-2 text-muted"></i>
                    Tu carrito de préstamos se encuentra vacío.<br>
                    <small>Explora el catálogo para agregar libros digitales o audiolibros.</small>
                </div>
            <?php endif; ?>
        </div>

        <div class="border-top border-secondary pt-3 mt-auto">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary fw-medium">Subtotal (14 días):</span>
                <span class="fs-5 fw-bold text-gradient-accent" id="cartTotalDisplay"><?= number_format($cartTotal, 2) ?> Créditos ⛃</span>
            </div>
            <?php if ($usuarioActual): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 small <?= $usuarioActual->saldo < $cartTotal ? 'text-danger' : 'text-secondary' ?>" id="cartBalanceCheck">
                    <span>Tu Saldo Virtual:</span>
                    <span class="fw-bold"><?= number_format($usuarioActual->saldo, 2) ?> Créditos ⛃</span>
                </div>
            <?php endif; ?>

            <?php if ($usuarioActual): ?>
                <form action="<?= BASE_URL ?>estandar/carrito/procesar" method="POST" id="processCartForm">
                    <button type="submit" class="btn btn-gradient-primary w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2" <?= empty($carritoItems) ? 'disabled' : '' ?> id="btnProcessCart">
                        <i class="bi bi-check2-circle"></i> Procesar Préstamo Múltiple
                    </button>
                </form>
            <?php else: ?>
                <a href="<?= BASE_URL ?>login" class="btn btn-outline-custom w-100 py-2 text-center">Inicia sesión para procesar</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 2. Modal: Detalle Completo del Libro (Tarea 6) -->
<div class="modal fade" id="viewBookModal" tabindex="-1" aria-labelledby="viewBookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold text-gradient" id="viewBookModalLabel"><i class="bi bi-book me-2"></i>Ficha Técnica y Detalles del Recurso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 align-items-center">
                    <div class="col-md-4 text-center">
                        <div class="book-cover-container rounded-4 w-100 shadow-lg border border-secondary d-flex align-items-center justify-content-center position-relative overflow-hidden" id="detailCoverContainer" style="height: 280px;">
                            <i class="bi bi-book-half book-cover-placeholder" id="detailCoverIcon"></i>
                            <img src="" id="detailCoverImg" class="d-none w-100 h-100" style="object-fit: cover;" alt="Portada del recurso">
                            <span class="book-badge-type" id="detailTypeBadge">Libro</span>
                            <span class="book-badge-price" id="detailPriceBadge">0.00 ⛃</span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-dark border border-secondary text-info" id="detailCategory">Categoría</span>
                            <span class="badge bg-dark border border-secondary text-secondary" id="detailYear">2024</span>
                        </div>
                        <h3 class="fw-extrabold text-light mb-1" id="detailTitle">Título del Libro</h3>
                        <p class="text-secondary fs-6 mb-3" id="detailAuthor">Autor del Libro</p>

                        <div class="p-3 glass-card mb-3">
                            <div class="row g-2 text-center">
                                <div class="col-6 border-end border-secondary">
                                    <span class="text-secondary small d-block">Ejemplares Disponibles</span>
                                    <span class="fs-5 fw-bold" id="detailStock">5 uds</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-secondary small d-block">Código ISBN</span>
                                    <span class="fs-6 fw-semibold text-light" id="detailIsbn">978-000000</span>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-secondary text-uppercase fw-bold small">Sinopsis / Descripción</h6>
                        <p class="text-light small mb-0" id="detailDescription" style="line-height: 1.6;">Sin descripción disponible.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary justify-content-between">
                <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cerrar</button>
                <div class="d-flex gap-2" id="detailActions">
                    <!-- Botones dinámicos según stock y rol -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. Modal: Confirmación de Renta Individual (Tarea 7) -->
<div class="modal fade" id="confirmRentarModal" tabindex="-1" aria-labelledby="confirmRentarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-warning">
            <div class="modal-header border-secondary bg-dark">
                <h5 class="modal-title fw-bold text-warning" id="confirmRentarModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmación de Renta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3 fs-6">¿Estás seguro de que deseas confirmar el alquiler inmediato de este recurso literario?</p>
                <div class="glass-card p-3 mb-3 border-secondary">
                    <div class="fw-bold text-light mb-1" id="confirmBookTitle">Título del Libro</div>
                    <div class="d-flex justify-content-between text-secondary small">
                        <span>Período de préstamo:</span>
                        <span class="text-light fw-medium">14 días exactos</span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary small mt-1">
                        <span>Costo de transacción:</span>
                        <span class="text-success fw-bold fs-6" id="confirmBookPrice">0.00 Créditos ⛃</span>
                    </div>
                </div>
                <div class="alert alert-dark border border-secondary rounded-3 p-2 small mb-0 text-muted">
                    <i class="bi bi-info-circle text-info me-1"></i> El importe será debitado directamente de tu saldo en la billetera virtual.
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancelar</button>
                <form action="<?= BASE_URL ?>estandar/rentar" method="POST" id="confirmRentarForm">
                    <input type="hidden" name="recurso_id" id="confirmRecursoId">
                    <button type="submit" class="btn btn-gradient-primary fw-bold"><i class="bi bi-check-lg me-1"></i> Confirmar y Rentar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<footer class="mt-auto py-4 border-top border-secondary">
    <div class="container">
        <div class="row align-items-center gy-3">
            <div class="col-md-6 text-center text-md-start">
                <span class="fw-bold text-light">SGBV - Sistema de Gestión de Bibliotecas Virtuales © 2026</span>
            </div>
            <div class="col-md-6 text-center text-md-end d-flex flex-wrap justify-content-center justify-content-md-end gap-3 small">
                <a href="<?= BASE_URL ?>legal/privacidad" class="text-secondary text-decoration-none hover-white">Política de Privacidad</a>
                <span class="text-secondary">|</span>
                <a href="<?= BASE_URL ?>legal/terminos" class="text-secondary text-decoration-none hover-white">Términos y Condiciones</a>
                <span class="text-secondary">|</span>
                <a href="<?= BASE_URL ?>legal/devoluciones" class="text-secondary text-decoration-none hover-white">Política de Devolución</a>
                <span class="text-secondary">|</span>
                <a href="<?= BASE_URL ?>contacto" class="text-secondary text-decoration-none hover-white">Contacto</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js para visualizaciones en Dashboard -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<!-- Scripts personalizados de búsqueda interactiva en tiempo real -->
<script>
    const BASE_URL = <?= json_encode(BASE_URL) ?>;
    const USUARIO_ROL_ID = <?= $usuarioActual ? $usuarioActual->rol_id : 'null' ?>;
</script>
<script src="<?= BASE_URL ?>public/js/search.js"></script>
<script src="<?= BASE_URL ?>public/js/table-utils.js"></script>
<script src="<?= BASE_URL ?>public/js/app.js"></script>
</body>
</html>
