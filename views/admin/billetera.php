<?php
$tituloPagina = 'Mi Billetera Personal y Finanzas | SGBV Admin';
require_once __DIR__ . '/../../layouts/header.php';

// Calcular resumen financiero de ingresos del sistema
$totalRecaudado = 0.0;
$rentasCompletadas = 0;
if (!empty($prestamos) && is_array($prestamos)) {
    foreach ($prestamos as $p) {
        if ($p->estado !== 'reservado') {
            $totalRecaudado += $p->monto_pagado;
            $rentasCompletadas++;
        }
    }
}
?>

<div class="container py-5">
    <!-- Cabecera -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <span class="badge bg-dark border border-secondary text-info px-3 py-2 mb-2">
                <i class="bi bi-wallet2 me-1"></i> Billetera Privada de Administración
            </span>
            <h2 class="fw-extrabold text-gradient mb-1">Mi Billetera y Resumen de Fondos</h2>
            <p class="text-secondary mb-0">Gestiona tu saldo virtual personal para transacciones de prueba por separado de las analíticas del sistema.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-outline-custom d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- Tarjeta Principal: Mi Billetera Personal de Admin -->
    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="glass-card p-4 h-100 position-relative overflow-hidden border-info border-opacity-50 shadow-lg">
                <div class="position-absolute top-0 end-0 p-3 opacity-25">
                    <i class="bi bi-wallet-fill" style="font-size: 6rem; color: var(--secondary-color);"></i>
                </div>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-info-subtle text-info border border-info px-3 py-1">Saldo de Pruebas</span>
                    <span class="text-secondary small"><i class="bi bi-shield-check me-1"></i> Cuenta de Administrador</span>
                </div>
                <span class="text-secondary small d-block mb-1">Disponible en tu Billetera Personal</span>
                <h1 class="fw-extrabold text-gradient-accent mb-4 display-4"><?= number_format($usuario->saldo, 2) ?> <span class="fs-4 fw-normal text-secondary">Créditos ⛃</span></h1>
                
                <hr class="border-secondary mb-4">

                <h6 class="fw-bold text-light mb-3"><i class="bi bi-plus-circle-dotted me-2 text-info"></i>Recargar Mi Billetera Personal</h6>
                <form action="<?= BASE_URL ?>admin/billetera" method="POST" class="row g-2 align-items-center">
                    <div class="col-sm-7">
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary">⛃</span>
                            <input type="number" step="0.01" min="5.00" max="1000.00" class="form-control" name="monto" value="50.00" required placeholder="5.00 - 1000.00">
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <button type="submit" class="btn btn-gradient-secondary w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-lightning-charge-fill"></i> Recargar
                        </button>
                    </div>
                </form>
                <div class="form-text text-secondary small mt-2">
                    <i class="bi bi-info-circle me-1"></i> Este saldo es independiente del dinero recaudado por las rentas de los lectores.
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between border-secondary shadow-lg">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-light mb-0"><i class="bi bi-graph-up-arrow me-2 text-success"></i>Recaudación General del Sistema</h5>
                        <span class="badge bg-dark border border-secondary text-success">Ingresos SGBV</span>
                    </div>
                    <p class="text-secondary small mb-4">Total acumulado proveniente de todos los préstamos y alquileres de libros digitales realizados por lectores estándar en la plataforma.</p>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div class="p-3 bg-dark bg-opacity-50 rounded-4 border border-secondary text-center">
                                <span class="text-secondary small d-block">Ingresos Acumulados</span>
                                <span class="fs-3 fw-extrabold text-success"><?= number_format($totalRecaudado, 2) ?> Créditos ⛃</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 bg-dark bg-opacity-50 rounded-4 border border-secondary text-center">
                                <span class="text-secondary small d-block">Transacciones Totales</span>
                                <span class="fs-3 fw-extrabold text-light"><?= $rentasCompletadas ?> rentas</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                    <span class="small text-secondary"><i class="bi bi-calendar-check me-1"></i> Último cierre contable: Hoy</span>
                    <a href="<?= BASE_URL ?>admin/exportar?tipo=historial" class="btn btn-outline-custom btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV Contable
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
