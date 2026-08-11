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
                    <div class="d-inline-flex align-items-center justify-content-center mb-3 fs-1 text-gradient-accent">
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
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_nacimiento" class="form-label"><i class="bi bi-calendar-date me-1"></i> Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required value="<?= htmlspecialchars($_POST['fecha_nacimiento'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label"><i class="bi bi-shield-lock me-1"></i> Contraseña de Acceso</label>
                        <div class="position-relative">
                            <input type="password" class="form-control pe-5" id="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
                            <span class="position-absolute top-50 end-0 translate-middle-y me-3 toggle-password" style="cursor: pointer; color: #6c757d; z-index: 10;">
                                <i class="bi bi-eye-fill"></i>
                            </span>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input bg-dark border-secondary" type="checkbox" id="termsCheck" required>
                        <label class="form-check-label text-muted small" for="termsCheck">
                            He leído y acepto la <a href="<?= BASE_URL ?>legal/privacidad" class="text-info text-decoration-none" target="_blank">Política de Privacidad</a> y los <a href="<?= BASE_URL ?>legal/terminos" class="text-info text-decoration-none" target="_blank">Términos y Condiciones</a>.
                        </label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" id="btnSubmit" class="btn btn-gradient-secondary py-2 fw-bold fs-6" disabled>
                            <i class="bi bi-person-check-fill me-2"></i> Crear Cuenta
                        </button>
                    </div>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const form = document.querySelector('form');
                        const btnSubmit = document.getElementById('btnSubmit');
                        const inputs = form.querySelectorAll('input[required]');

                        function checkFormValidity() {
                            let isValid = true;
                            inputs.forEach(input => {
                                if (input.type === 'checkbox') {
                                    if (!input.checked) isValid = false;
                                } else {
                                    if (input.value.trim() === '') isValid = false;
                                }
                            });
                            btnSubmit.disabled = !isValid;
                        }

                        inputs.forEach(input => {
                            input.addEventListener('input', checkFormValidity);
                            input.addEventListener('change', checkFormValidity);
                        });
                        
                        checkFormValidity();

                        const togglePassword = document.getElementById('togglePassword');
                        const password = document.getElementById('password');

                        togglePassword.addEventListener('click', function () {
                            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                            password.setAttribute('type', type);
                            this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
                        });
                    });
                </script>

                <div class="text-center mt-4 pt-3 border-top border-secondary">
                    <p class="text-muted mb-0 small">¿Ya posees una cuenta registrada? <a href="<?= BASE_URL ?>login" class="text-info fw-bold text-decoration-none">Inicia sesión aquí</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
