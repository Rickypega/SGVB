<?php
$tituloPagina = 'Registro de Lector | SGBV';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-7">
            <div class="glass-card p-4 p-sm-5 shadow-lg position-relative">
                <div class="position-absolute top-0 start-50 translate-middle-x w-50" style="height: 3px; background: linear-gradient(90deg, transparent, #06b6d4, transparent);"></div>
                
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-dark border border-secondary rounded-circle p-3 mb-3 text-info fs-2">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h2 class="fw-bold text-gradient">Registro de Nuevo Lector</h2>
                    <p class="text-muted small">Únete a SGBV y recibe un bono inicial de $30.00 en tu billetera virtual</p>
                </div>

                <form action="<?= BASE_URL ?>registro" method="POST">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label"><i class="bi bi-person me-1"></i> Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej. Roberto Gómez" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="correo" class="form-label"><i class="bi bi-envelope me-1"></i> Correo Electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo" placeholder="usuario@correo.com" required value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="cedula" class="form-label"><i class="bi bi-card-heading me-1"></i> Cédula / DNI</label>
                            <input type="text" class="form-control" id="cedula" name="cedula" placeholder="001-1234567-8" required value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>">
                            <div class="form-text text-secondary small"><i class="bi bi-check-circle me-1"></i> Se verificará automáticamente en la demo</div>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_nacimiento" class="form-label"><i class="bi bi-calendar-date me-1"></i> Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required value="<?= htmlspecialchars($_POST['fecha_nacimiento'] ?? '') ?>">
                            <div class="form-text text-warning small"><i class="bi bi-exclamation-triangle me-1"></i> Control de edad: Para rentas restringidas se verifican 18+ años</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label"><i class="bi bi-shield-lock me-1"></i> Contraseña de Acceso</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
                    </div>

                    <div class="alert alert-dark border border-secondary rounded-3 p-3 mb-4 small text-muted">
                        <i class="bi bi-info-circle-fill text-primary me-2"></i>
                        Al registrarte, declaras que tu fecha de nacimiento es verídica para el control del sistema. Tu saldo digital y verificación se activarán de inmediato.
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-gradient-secondary py-2 fw-bold fs-6">
                            <i class="bi bi-person-check-fill me-2"></i> Crear Cuenta y Recibir Bono de $30.00
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4 pt-3 border-top border-secondary">
                    <p class="text-muted mb-0 small">¿Ya posees una cuenta registrada? <a href="<?= BASE_URL ?>login" class="text-info fw-bold text-decoration-none">Inicia sesión aquí</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
