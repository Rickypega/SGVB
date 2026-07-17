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
                        <span class="badge bg-primary text-white px-3 py-1 mb-2"><i class="bi bi-shield-lock-fill me-1"></i> SGBV Legal</span>
                        <h1 class="fw-extrabold text-gradient mb-0 display-6">Política de Privacidad y Tratamiento de Datos</h1>
                    </div>
                    <span class="text-secondary small">Actualizado: <?= date('F Y') ?></span>
                </div>

                <div class="text-light" style="line-height: 1.8;">
                    <p class="lead text-secondary mb-4">
                        En el <strong>Sistema de Gestión de Bibliotecas Virtuales (SGBV)</strong>, valoramos y respetamos la privacidad e integridad transaccional de todos nuestros lectores y administradores. Esta política describe cómo recopilamos, protegemos y gestionamos la información dentro de nuestra plataforma digital.
                    </p>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-1-circle-fill me-2"></i> Recopilación de Información Personal</h4>
                    <p>
                        Para el registro y uso regular de la plataforma, solicitamos datos esenciales estrictamente necesarios para la administración del servicio literario en línea:
                    </p>
                    <ul>
                        <li><strong>Datos de Identificación:</strong> Nombre completo, dirección de correo electrónico validada y número de cédula o documento de identidad auditable.</li>
                        <li><strong>Datos Transaccionales:</strong> Historial de rentas literarias, saldos en billetera virtual (Créditos SGBV ⛃) y registro de recursos marcados como leídos (`ha_leido`).</li>
                        <li><strong>Seguridad del Catálogo:</strong> Registramos las sesiones en línea y actividad interactiva en el Visor Digital con el fin de prevenir la copia no autorizada, captura de pantalla o difusión ilícita del catálogo de libros protegidos.</li>
                    </ul>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-2-circle-fill me-2"></i> Uso y Finalidad de los Datos</h4>
                    <p>
                        La información almacenada en nuestra base de datos relacional MySQL se utiliza con las siguientes finalidades:
                    </p>
                    <ul>
                        <li>Permitir el acceso personalizado al catálogo, gestión de carrito persistente y sincronización de sesiones transaccionales.</li>
                        <li>Gestionar el vencimiento automático de préstamos digitales tras el plazo estándar de 14 días y emitir notificaciones recordatorias.</li>
                        <li>Auditar devoluciones anticipadas garantizando la protección de los derechos de autor cuando el material no ha sido abierto o leído por el usuario (`ha_leido = 0`).</li>
                    </ul>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-3-circle-fill me-2"></i> Seguridad Transaccional y Criptografía</h4>
                    <p>
                        Todas las contraseñas de cuentas registradas son cifradas unidireccionalmente utilizando algoritmos criptográficos robustos nativos de PHP (`password_hash`). Ningún administrador ni tercero tiene acceso a la contraseña en texto plano de ningún lector de la plataforma.
                    </p>

                    <h4 class="fw-bold mt-4 mb-3 text-gradient-accent"><i class="bi bi-4-circle-fill me-2"></i> Derechos del Lector y Eliminación de Cuenta</h4>
                    <p>
                        Todo lector estándar goza del derecho de acceso, rectificación y cancelación de sus datos. A través de la sección de <strong>Configuración de Cuenta</strong>, usted puede modificar su contraseña y solicitar o ejecutar la eliminación total e irrevocable de su cuenta, lo cual depurará su historial activo, suscripciones y carrito de forma permanente.
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
