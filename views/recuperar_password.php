<?php
$tituloPagina = 'Recuperar Contraseña | SGBV';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="glass-card p-4 p-sm-5 shadow-lg position-relative">
                <div class="position-absolute top-0 start-50 translate-middle-x w-50" style="height: 3px; background: linear-gradient(90deg, transparent, #dc3545, transparent);"></div>
                
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3 fs-1 text-danger">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h2 class="fw-bold text-gradient">Recuperar Contraseña</h2>
                    <p class="text-muted small">Ingresa tus datos para enviarte un enlace de recuperación seguro.</p>
                </div>

                <form action="<?= BASE_URL ?>usuario/recuperar_password" method="POST">
                    <div class="mb-3">
                        <label for="correo" class="form-label"><i class="bi bi-envelope me-1"></i> Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="ejemplo@sgbv.com" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="cedula" class="form-label"><i class="bi bi-card-heading me-1"></i> Cédula / DNI</label>
                        <input type="text" class="form-control" id="cedula" name="cedula" placeholder="000-0000000-0" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger py-2 fw-bold fs-6">
                            <i class="bi bi-send me-2"></i> Enviar Enlace
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4 pt-3 border-top border-secondary">
                    <p class="text-muted mb-0 small"><a href="<?= BASE_URL ?>usuario/login" class="text-primary fw-bold text-decoration-none"><i class="bi bi-arrow-left"></i> Volver a Iniciar Sesión</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
