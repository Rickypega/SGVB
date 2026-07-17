<?php
$tituloPagina = 'Términos y Condiciones de Renta y Servicio | SGBV';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="glass-card p-4 p-md-5 animate-fade-in border-secondary shadow-lg">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-secondary flex-wrap gap-2">
                    <div>
                        <span class="badge bg-info text-dark px-3 py-1 mb-2"><i class="bi bi-file-earmark-text-fill me-1"></i> SGBV Legal</span>
                        <h1 class="fw-extrabold text-gradient mb-0 display-6">Términos y Condiciones de Uso y Renta</h1>
                    </div>
                    <span class="text-secondary small">Vigencia: Año <?= date('Y') ?></span>
                </div>

                <div class="text-light" style="line-height: 1.8;">
                    <p class="lead text-secondary mb-4">
                        Bienvenido a <strong>SGBV (Sistema de Gestión de Bibliotecas Virtuales)</strong>. Al acceder, registrarse o rentar recursos en nuestro catálogo literario en línea, usted acepta cumplir y regirse por los presentes términos y condiciones transaccionales y de propiedad intelectual.
                    </p>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-journal-check me-2"></i> 1. Naturaleza del Servicio de Biblioteca Digital</h4>
                    <p>
                        SGBV opera como un ecosistema literario en línea bajo el modelo de renta virtual con plazos acotados. La adquisición de una renta de recurso digital no otorga la propiedad del archivo al usuario, sino un derecho de lectura, visualización o reproducción de voz alta limitado y exclusivo dentro de nuestra plataforma digital por un periodo exacto de <strong>catorce (14) días calendario</strong>.
                    </p>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-clock-history me-2"></i> 2. Plazos y Vencimiento Automático (Tarea 10)</h4>
                    <p>
                        A diferencia de las bibliotecas físicas tradicionales donde existen sanciones pecuniarias por retraso, en SGBV <strong>el estado de alquiler nunca entra en mora ni genera multas por retraso de entrega</strong>. Al cumplirse exactamente los 14 días desde la fecha transaccional de préstamo, nuestro sistema automatizado procede al retiro transaccional del recurso, cambiando su estado a <code>devuelto</code> e incrementando automáticamente la disponibilidad en el inventario del catálogo.
                    </p>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-wallet-fill me-2"></i> 3. Moneda Transaccional y Recargas (Créditos ⛃)</h4>
                    <p>
                        Todas las rentas literarias dentro del sistema se valorizan y procesan exclusivamente a través de nuestra unidad interna denominada <strong>Créditos SGBV (⛃)</strong>. Las recargas realizadas en el panel del usuario o billetera son de carácter no reembolsable en dinero fiduciario y están destinadas exclusivamente a la renta en línea de libros, audiolibros y artículos del catálogo SGBV.
                    </p>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-shield-x me-2"></i> 4. Protección del Derecho de Autor y Prohibición de Duplicación (Tarea 15)</h4>
                    <p>
                        El Visor de Lectura Digital incorpora medidas activas y pasivas de seguridad para proteger a los autores y editoriales aliadas:
                    </p>
                    <ul>
                        <li>Queda totalmente prohibido el uso de herramientas externas de scraping, grabación de pantalla o captura masiva del contenido del Visor Digital.</li>
                        <li>El sistema desactiva intencionalmente el menú contextual, atajos de copia y comandos de impresión dentro de la zona de lectura en línea.</li>
                        <li>El incumplimiento deliberado y reiterado de estas políticas de seguridad DRM derivará en la suspensión inmediata e irrevocable de la cuenta del lector sin reintegro de créditos remanentes.</li>
                    </ul>
                </div>

                <div class="mt-5 pt-4 border-top border-secondary d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <a href="<?= BASE_URL ?>home" class="btn btn-outline-custom">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Catálogo
                    </a>
                    <a href="<?= BASE_URL ?>legal/devoluciones" class="btn btn-gradient-secondary">
                        Política de Devoluciones <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
