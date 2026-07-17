<?php
$tituloPagina = 'Política de Devolución Anticipada y Reintegro de Créditos | SGBV';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="glass-card p-4 p-md-5 animate-fade-in border-secondary shadow-lg">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-secondary flex-wrap gap-2">
                    <div>
                        <span class="badge bg-warning text-dark px-3 py-1 mb-2"><i class="bi bi-arrow-return-left me-1"></i> SGBV Legal & Garantía</span>
                        <h1 class="fw-extrabold text-gradient mb-0 display-6">Política de Devolución y Reintegro</h1>
                    </div>
                    <span class="text-secondary small">Vigente: Año <?= date('Y') ?></span>
                </div>

                <div class="text-light" style="line-height: 1.8;">
                    <p class="lead text-secondary mb-4">
                        En SGBV ofrecemos garantías justas para los lectores al tiempo que resguardamos celosamente los derechos económicos y creativos de los autores y editores literarios. Esta política norma la devolución voluntaria anticipada y el reintegro de créditos transaccionales.
                    </p>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-check-circle-fill text-success me-2"></i> 1. Condición Fundamental para Reintegro (Tarea 14)</h4>
                    <p>
                        Para solicitar y obtener el reintegro inmediato del valor pagado por la renta de un libro, audiolibro o artículo digital, <strong>el lector no debe haber abierto ni iniciado la lectura en el Visor Digital</strong> (`ha_leido == 0`).
                    </p>
                    <div class="alert bg-dark border border-success text-success p-3 rounded-4 mb-4">
                        <i class="bi bi-shield-check me-2"></i>
                        <strong>Garantía de Satisfacción:</strong> Si cometiste un error en tu selección o añadiste un libro equivocado y aún no has ingresado al botón de "Leer" o "Escuchar" en tu panel (`ha_leido = 0`), podrás devolverlo instantáneamente y el 100% de los Créditos SGBV (⛃) pagados volverá en segundos a tu saldo disponible.
                    </div>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> 2. Pérdida del Derecho de Reintegro Anticipado</h4>
                    <p>
                        Una vez que el usuario hace clic en el botón de lectura/escucha del recurso, el sistema marca auditable y permanentemente la columna <code>ha_leido = 1</code> en la base de datos para dicha transacción. A partir de ese preciso instante:
                    </p>
                    <ul>
                        <li>El recurso se considera consumido y revelado en su integridad literaria.</li>
                        <li>No procederá ninguna solicitud transaccional ni administrativa de reembolso de los créditos cobrados.</li>
                        <li>El usuario conservará el acceso ininterrumpido a su lectura hasta el cumplimiento exacto del plazo de 14 días en que se devuelva automáticamente.</li>
                    </ul>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-arrow-repeat me-2"></i> 3. Devolución Automática al Cumplir 14 Días (Tarea 10)</h4>
                    <p>
                        Le recordamos que todo préstamo se cierra y regresa al inventario de forma automática transcurridas las 336 horas exactas (14 días calendario) desde su renta. No requiere que usted realice ninguna acción de devolución manual ni preocúpese por recargos adicionales al finalizar su plazo legal de lectura.
                    </p>
                </div>

                <div class="mt-5 pt-4 border-top border-secondary d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <a href="<?= BASE_URL ?>estandar/panel" class="btn btn-outline-custom">
                        <i class="bi bi-bookmark-star me-1"></i> Ir a Mi Panel y Préstamos
                    </a>
                    <a href="<?= BASE_URL ?>contacto" class="btn btn-gradient-primary">
                        <i class="bi bi-headset me-1"></i> Soporte y Atención al Lector
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
