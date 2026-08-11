<?php
$tituloPagina = 'Política de Privacidad y Tratamiento de Datos | SGBV';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="glass-card p-4 p-md-5 animate-fade-in border-secondary shadow-lg">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-secondary flex-wrap gap-2">
                    <div>
                        <h1 class="fw-extrabold text-gradient mb-0 display-6">Política de Privacidad y Tratamiento de Datos</h1>
                    </div>
                    <span class="text-secondary small">Vigencia: Año <?= date('Y') ?></span>
                </div>

                <div class="text-light" style="line-height: 1.8;">

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-1-circle-fill me-2"></i> Recopilación de Información Personal</h4>
                    <p>
                        Para garantizar el correcto funcionamiento de nuestros servicios, recopilamos únicamente la información necesaria para gestionar su cuenta y brindar una experiencia personalizada. Esto incluye datos de identificación personal (como nombre, correo electrónico y documento de identidad), así como el registro de sus interacciones, transacciones e historial de uso dentro de la plataforma.
                    </p>
                    
                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-2-circle-fill me-2"></i> Uso y Finalidad de los Datos</h4>
                    <p>
                        La información recolectada se utiliza exclusivamente para:
                    </p>
                    <ul>
                        <li>Administrar el acceso a la plataforma y mantener su sesión segura.</li>
                        <li>Procesar y gestionar la disponibilidad, alquileres y plazos de los recursos digitales.</li>
                        <li>Monitorizar la actividad dentro del sistema con el fin de proteger la propiedad intelectual y prevenir el uso no autorizado del contenido.</li>
                    </ul>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-3-circle-fill me-2"></i> Seguridad Transaccional y Criptografía</h4>
                    <p>
                        Implementamos medidas de seguridad técnicas y administrativas de nivel estándar en la industria para proteger sus datos personales contra acceso, alteración o divulgación no autorizada.
                    </p>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-4-circle-fill me-2"></i> Derechos del Lector y Eliminación de Cuenta</h4>
                    <p>
                        Usted conserva en todo momento el derecho de acceder, actualizar o corregir su información personal. Asimismo, puede solicitar o ejecutar la cancelación definitiva de su cuenta desde el panel de configuración, lo cual conllevará la eliminación permanente de su perfil e historial de nuestros registros activos.
                    </p>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-5-circle-fill me-2"></i> Limitación de Responsabilidad </h4>
                    <p>
                        La presente plataforma ha sido desarrollada exclusivamente con fines educativos, académicos y de simulación para la Universidad Católica Tecnológica de Barahona (UCATEBA). El sistema no constituye una entidad comercial ni un servicio público oficial. Los administradores y desarrolladores no se hacen responsables por el uso indebido de la plataforma, la precisión de la información simulada ni la interrupción temporal o permanente del servicio.
                    </p>
                </div>

                <div class="mt-5 pt-4 border-top border-secondary d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <a href="<?= BASE_URL ?>home" class="btn btn-outline-custom">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Catálogo
                    </a>
                    <a href="<?= BASE_URL ?>contacto" class="btn btn-gradient-primary">
                        <i class="bi bi-envelope-fill me-1"></i> Consultas de Privacidad
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
