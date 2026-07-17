<?php
$tituloPagina = 'Centro de Contacto y Soporte Al Lector | SGBV';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="row g-4">
                <!-- Información de Contacto -->
                <div class="col-md-5">
                    <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between border-secondary shadow-lg">
                        <div>
                            <span class="badge bg-primary text-white px-3 py-1 mb-3"><i class="bi bi-headset me-1"></i> Soporte 24/7</span>
                            <h2 class="fw-extrabold text-gradient mb-3">¿Necesitas Ayuda o Asesoría?</h2>
                            <p class="text-secondary mb-4">
                                Estamos aquí para resolver tus dudas sobre préstamos literarios, recargas de Créditos ⛃, reportes técnicos del Visor Digital o sugerencias para el catálogo SGBV.
                            </p>

                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="bg-dark border border-secondary text-info rounded-circle p-3 d-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                                    <i class="bi bi-envelope-check"></i>
                                </div>
                                <div>
                                    <div class="small text-secondary">Correo de Atención e Incidencias</div>
                                    <div class="fw-bold text-light">soporte@sgbv-virtual.com</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="bg-dark border border-secondary text-warning rounded-circle p-3 d-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div>
                                    <div class="small text-secondary">Oficina de Coordinación Digital</div>
                                    <div class="fw-bold text-light">Campus Central Bibliotecario SGBV</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-dark border border-secondary text-success rounded-circle p-3 d-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <div>
                                    <div class="small text-secondary">Horario de Respuesta en Línea</div>
                                    <div class="fw-bold text-light">Lunes a Domingo (Ininterrumpido)</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top border-secondary small text-muted">
                            <i class="bi bi-shield-lock me-1"></i> Tus datos de contacto están resguardados bajo nuestra Política de Privacidad.
                        </div>
                    </div>
                </div>

                <!-- Formulario de Contacto (Tarea 13) -->
                <div class="col-md-7">
                    <div class="glass-card p-4 p-md-5 h-100 border-secondary shadow-lg">
                        <h4 class="fw-bold text-light mb-1"><i class="bi bi-send-fill text-primary me-2"></i> Enviar Mensaje a Mesa de Ayuda</h4>
                        <p class="text-secondary small mb-4">Completa el formulario y nuestro equipo técnico te responderá al correo electrónico en minutos.</p>

                        <form action="<?= BASE_URL ?>contacto/procesar" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-light small">Nombre Completo *</label>
                                    <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($usuarioActual->nombre ?? '') ?>" required placeholder="Tu nombre y apellido">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-light small">Correo Electrónico *</label>
                                    <input type="email" class="form-control" name="correo" value="<?= htmlspecialchars($usuarioActual->correo ?? '') ?>" required placeholder="ejemplo@correo.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-light small">Asunto de tu Consulta *</label>
                                    <select class="form-select bg-dark text-light border-secondary" name="asunto" required>
                                        <option value="Soporte Técnico Visor / Lectura">Soporte Técnico Visor / Lectura Text-to-Speech</option>
                                        <option value="Duda sobre Renta o Devolución">Consulta sobre Renta, Plazo 14d o Devolución</option>
                                        <option value="Problemas con Recarga o Créditos">Problemas con Billetera o Créditos SGBV (⛃)</option>
                                        <option value="Sugerencia de Libro o Audiolibro">Sugerencia de Nuevo Material para el Catálogo</option>
                                        <option value="Otro Asunto">Otro Asunto Administrativo o General</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-light small">Mensaje o Detalle *</label>
                                    <textarea class="form-control" name="mensaje" rows="5" required placeholder="Describe detalladamente tu consulta, duda o reporte aquí..."></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-gradient-primary w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-send"></i> Enviar Consulta a SGBV
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
