<?php
$tituloPagina = 'Configuración de Cuenta y Seguridad | SGBV';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <span class="badge bg-dark border border-secondary text-info px-3 py-2 mb-2">
                <i class="bi bi-gear-fill me-1"></i> Panel de Usuario
            </span>
            <h2 class="fw-extrabold text-gradient mb-1">Configuración y Seguridad de la Cuenta</h2>
            <p class="text-secondary mb-0">Modifica tus datos de perfil, cambia tu contraseña de acceso y gestiona tu privacidad.</p>
        </div>
        <div>
            <?php if ($usuario->rol_id === 1): ?>
                <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-outline-custom">
                    <i class="bi bi-speedometer2 me-1"></i> Volver al Dashboard
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>estandar/panel" class="btn btn-outline-custom">
                    <i class="bi bi-bookmark-star me-1"></i> Volver al Panel
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna Izquierda: Datos del Perfil y Billetera -->
        <div class="col-lg-6">
            <div class="glass-card p-4 p-md-5 h-100 border-secondary shadow-lg d-flex flex-column justify-content-between">
                <div>
                    <h4 class="fw-bold text-light mb-1"><i class="bi bi-person-lines-fill text-info me-2"></i> Datos Personales y de Registro</h4>
                    <p class="text-secondary small mb-4">Actualiza tus datos básicos e información para notificaciones de préstamos.</p>

                    <form action="<?= BASE_URL ?>usuario/actualizar" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-light small">Cédula o Documento de Identidad <span class="badge bg-secondary ms-2">Inmutable</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-card-heading"></i></span>
                                <input type="text" class="form-control bg-dark text-muted border-secondary" value="<?= htmlspecialchars($usuario->cedula) ?>" readonly disabled>
                            </div>
                            <div class="form-text text-secondary small">La cédula está asociada a tu registro de auditoría en biblioteca y no puede modificarse.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light small">Nombre Completo *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($usuario->nombre) ?>" required placeholder="Nombre y apellido">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light small">Correo Electrónico *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" name="correo" value="<?= htmlspecialchars($usuario->correo) ?>" required placeholder="correo@ejemplo.com">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light small">Fecha de Nacimiento</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" class="form-control" name="fecha_nacimiento" value="<?= htmlspecialchars((string)($usuario->fecha_nacimiento ?? '')) ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-light small">Contraseña de Confirmación *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control" name="password_confirmar_datos" required placeholder="Ingresa tu contraseña para autorizar cambios">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-gradient-primary w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-save2"></i> Guardar Cambios del Perfil
                        </button>
                    </form>
                </div>

                <div class="mt-4 pt-3 border-top border-secondary d-flex justify-content-between align-items-center small">
                    <span class="text-secondary"><i class="bi bi-wallet2 me-1"></i> Saldo en Billetera:</span>
                    <span class="fw-bold text-gradient-accent fs-6"><?= number_format($usuario->saldo, 2) ?> Créditos ⛃</span>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Cambio de Contraseña -->
        <div class="col-lg-6">
            <div class="glass-card p-4 p-md-5 h-100 border-secondary shadow-lg d-flex flex-column justify-content-between">
                <div>
                    <h4 class="fw-bold text-light mb-1"><i class="bi bi-shield-lock-fill text-warning me-2"></i> Seguridad y Contraseña</h4>
                    <p class="text-secondary small mb-4">Te recomendamos utilizar una contraseña robusta con letras, números y símbolos para resguardar tus créditos en SGBV.</p>

                    <form action="<?= BASE_URL ?>usuario/password" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-light small">Contraseña Actual *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control" name="password_anterior" required placeholder="Escribe tu contraseña actual">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light small">Nueva Contraseña *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" name="password_nueva" required minlength="6" placeholder="Mínimo 6 caracteres">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-light small">Confirmar Nueva Contraseña *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-check2-circle"></i></span>
                                <input type="password" class="form-control" name="password_confirmar" required minlength="6" placeholder="Repite la nueva contraseña">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-gradient-secondary w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-shield-check"></i> Actualizar Contraseña de Acceso
                        </button>
                    </form>
                </div>

                <!-- Zona de Peligro / Eliminación de Cuenta (Sólo para Lectores Estándar, Tarea 12) -->
                <?php if ($usuario->rol_id === 2): ?>
                    <div class="mt-5 pt-4 border-top border-danger border-opacity-50">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold text-danger mb-0"><i class="bi bi-exclamation-octagon-fill me-2"></i> Zona de Peligro</h6>
                            <span class="badge bg-danger-subtle text-danger border border-danger">Irreversible</span>
                        </div>
                        <p class="text-secondary small mb-3">
                            Al eliminar tu cuenta de lector, perderás tu saldo de <?= number_format($usuario->saldo, 2) ?> Créditos ⛃, tu historial literario, carrito y suscripciones activas sin posibilidad de recuperación.
                        </p>
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="bi bi-trash3-fill me-1"></i> Deseo Eliminar Mi Cuenta SGBV
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($usuario->rol_id === 2): ?>
<!-- Modal de Confirmación para Eliminar Cuenta -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border border-danger shadow-lg">
            <div class="modal-header border-bottom border-secondary bg-danger bg-opacity-10">
                <h5 class="modal-title fw-bold text-danger" id="deleteAccountModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> ¿Eliminar Cuenta Definitivamente?
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>usuario/eliminar" method="POST">
                <div class="modal-body p-4 text-light">
                    <p class="mb-3">Esta acción es permanente y procederá con el cierre de sesión y borrado total de tus registros y créditos transaccionales en SGBV.</p>
                    <p class="mb-3 small text-warning">
                        Para confirmar, escribe la palabra exacta <strong>ELIMINAR</strong> en el siguiente campo:
                    </p>
                    <input type="text" class="form-control bg-dark text-light border-danger text-center fw-bold" name="confirmacion_eliminar" required placeholder="Escribe ELIMINAR aquí" autocomplete="off">
                </div>
                <div class="modal-footer border-top border-secondary d-flex gap-2">
                    <button type="button" class="btn btn-outline-custom flex-fill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger flex-fill fw-bold">
                        <i class="bi bi-trash-fill me-1"></i> Confirmar Eliminación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
