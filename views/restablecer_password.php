<?php
$tituloPagina = 'Restablecer Contraseña | SGBV';
require_once __DIR__ . '/../layouts/header.php';
$token = $_GET['token'] ?? $_POST['token'] ?? '';
?>
<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="glass-card p-4 p-sm-5 shadow-lg position-relative">
                <div class="position-absolute top-0 start-50 translate-middle-x w-50" style="height: 3px; background: linear-gradient(90deg, transparent, #198754, transparent);"></div>
                
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3 fs-1 text-success">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <h2 class="fw-bold text-gradient">Nueva Contraseña</h2>
                    <p class="text-muted small">Crea una nueva contraseña segura para tu cuenta.</p>
                </div>

                <form action="<?= BASE_URL ?>usuario/restablecer_password" method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label for="password" class="form-label"><i class="bi bi-lock me-1"></i> Nueva Contraseña</label>
                            <span class="small text-muted">Mín. 6 caracteres</span>
                        </div>
                        <div class="position-relative">
                            <input type="password" class="form-control pe-5" id="password" name="password" placeholder="••••••••" required autofocus>
                            <span class="position-absolute top-50 end-0 translate-middle-y me-3 toggle-password" style="cursor: pointer; color: #6c757d; z-index: 10;">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="confirmar_password" class="form-label"><i class="bi bi-check2-circle me-1"></i> Confirmar Contraseña</label>
                        <div class="position-relative">
                            <input type="password" class="form-control pe-5" id="confirmar_password" name="confirmar_password" placeholder="••••••••" required>
                            <span class="position-absolute top-50 end-0 translate-middle-y me-3 toggle-password" style="cursor: pointer; color: #6c757d; z-index: 10;">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success py-2 fw-bold fs-6">
                            <i class="bi bi-save me-2"></i> Guardar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
