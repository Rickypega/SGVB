<?php
$tituloPagina = 'Iniciar Sesión | SGBV';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="glass-card p-4 p-sm-5 shadow-lg position-relative">
                <!-- Efecto de brillo de fondo -->
                <div class="position-absolute top-0 start-50 translate-middle-x w-50" style="height: 3px; background: linear-gradient(90deg, transparent, #6366f1, transparent);"></div>
                
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-dark border border-secondary rounded-circle p-3 mb-3 text-primary fs-2">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <h2 class="fw-bold text-gradient">Iniciar Sesión</h2>
                    <p class="text-muted small">Accede a tu cuenta de la Biblioteca Digital SGBV</p>
                </div>

                <form action="<?= BASE_URL ?>login" method="POST">
                    <div class="mb-3">
                        <label for="correo" class="form-label"><i class="bi bi-envelope me-1"></i> Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="ejemplo@sgbv.com" required autofocus value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <label for="password" class="form-label"><i class="bi bi-key me-1"></i> Contraseña</label>
                            <span class="small text-muted">Mín. 6 caracteres</span>
                        </div>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-gradient-primary py-2 fw-bold fs-6">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Entrar al Sistema
                        </button>
                    </div>
                </form>

                <!-- Cuentas de demostración de acceso rápido -->
                <div class="mt-4 pt-3 border-top border-secondary">
                    <div class="text-center small text-muted mb-2"><i class="bi bi-info-circle text-info me-1"></i> Cuentas de Demostración</div>
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-sm btn-outline-custom w-100 py-1" onclick="document.getElementById('correo').value='admin@sgbv.com'; document.getElementById('password').value='admin123';">
                                <i class="bi bi-shield-lock me-1"></i> Admin Demo
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-sm btn-outline-custom w-100 py-1" onclick="document.getElementById('correo').value='lector@sgbv.com'; document.getElementById('password').value='lector123';">
                                <i class="bi bi-person me-1"></i> Lector Demo
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4 pt-3 border-top border-secondary">
                    <p class="text-muted mb-0 small">¿Aún no tienes cuenta en SGBV? <a href="<?= BASE_URL ?>registro" class="text-primary fw-bold text-decoration-none">Regístrate aquí</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
