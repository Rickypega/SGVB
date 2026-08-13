<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$tituloPagina = 'Gestión de Usuarios y Transferencias | Admin SGBV';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container-fluid py-4 animate-fade-in">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold text-gradient mb-0"><i class="bi bi-people-fill me-2"></i>Gestión de Personal</h2>
            <p class="mb-0" style="color: var(--text-secondary) !important;">Administra cuentas de nivel superior y transfiere saldo a lectores.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Panel de Usuarios Staff -->
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4 glass-card h-100">
                <div class="card-header bg-transparent border-bottom border-secondary p-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold" style="color: var(--text-primary) !important;"><i class="bi bi-person-badge me-2" style="color: var(--text-secondary) !important;"></i>Cuentas de Administración</h5>
                    <button class="btn btn-gradient-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="prepararModalUsuario(null)">
                        <i class="bi bi-person-plus me-1"></i> Añadir Staff
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th class="text-center pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($usuarios) && is_array($usuarios)): ?>
                                    <?php foreach ($usuarios as $u): ?>
                                        <tr>
                                            <td class="ps-4" style="color: var(--text-secondary) !important;">#<?= $u->id ?></td>
                                            <td class="fw-bold" style="color: var(--text-primary) !important;"><?= htmlspecialchars($u->nombre) ?></td>
                                            <td><a href="mailto:<?= htmlspecialchars($u->correo) ?>" class="text-info text-decoration-none"><?= htmlspecialchars($u->correo) ?></a></td>
                                            <td>
                                                <span class="badge <?= $u->rol_id === 1 ? 'bg-danger' : 'bg-primary' ?> rounded-pill">
                                                    <?= $u->rol_id === 1 ? '<i class="bi bi-shield-lock-fill me-1"></i> Admin' : '<i class="bi bi-briefcase-fill me-1"></i> Gerente' ?>
                                                </span>
                                            </td>
                                            <td class="text-center pe-4">
                                                <button class="btn btn-sm btn-outline-secondary me-1" title="Editar Usuario" 
                                                        onclick='prepararModalUsuario(<?= json_encode(["id" => $u->id, "nombre" => $u->nombre, "correo" => $u->correo, "rol_id" => $u->rol_id]) ?>)'
                                                        data-bs-toggle="modal" data-bs-target="#modalUsuario">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php if ($u->id !== 1 && $u->id !== $_SESSION['usuario']->id): ?>
                                                <button class="btn btn-sm btn-outline-danger" title="Eliminar Usuario" onclick="prepararModalEliminar(<?= $u->id ?>, '<?= htmlspecialchars($u->nombre, ENT_QUOTES) ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4" style="color: var(--text-secondary) !important;">No hay cuentas de staff registradas.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transferencia de Créditos -->
        <div class="col-lg-4">
            <div class="card shadow-lg border-0 rounded-4 glass-card h-100">
                <div class="card-header bg-transparent border-bottom border-secondary p-4">
                    <h5 class="mb-0 fw-bold" style="color: var(--text-primary) !important;"><i class="bi bi-send-check me-2" style="color: var(--text-secondary) !important;"></i>Transferir Créditos</h5>
                </div>
                <div class="card-body p-4">
                    <p class="small mb-4" style="color: var(--text-secondary) !important;">Envía saldo (⛃) directamente a la billetera de un lector utilizando su correo electrónico. <strong>Se requiere contraseña de administrador.</strong></p>
                    
                    <form id="formTransferir" action="<?= BASE_URL ?>admin/transferir-creditos" method="POST" class="requires-auth">
                        <div class="mb-3">
                            <label class="form-label" style="color: var(--text-primary) !important;">Correo del Lector Destino</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="correo_destino" class="form-control" required placeholder="lector@ejemplo.com">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" style="color: var(--text-primary) !important;">Monto a Transferir (⛃)</label>
                            <div class="input-group">
                                <span class="input-group-text text-warning fw-bold">⛃</span>
                                <input type="number" name="monto" class="form-control fw-bold text-end" required min="1" step="0.5" placeholder="0.00">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-gradient-primary w-100 py-2 fw-bold">
                            <i class="bi bi-send-fill me-2"></i> Ejecutar Transferencia
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal CRUD Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold text-gradient" id="modalUsuarioTitulo">Gestionar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUsuario" action="<?= BASE_URL ?>admin/usuarios/guardar" method="POST" class="requires-auth">
                <input type="hidden" name="id" id="usuario_id" value="0">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-primary) !important;">Nombre Completo</label>
                        <input type="text" name="nombre" id="usuario_nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-primary) !important;">Correo Electrónico</label>
                        <input type="email" name="correo" id="usuario_correo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-primary) !important;">Rol en el Sistema</label>
                        <select name="rol_id" id="usuario_rol" class="form-select" required>
                            <option value="3">Gerente (Operativo)</option>
                            <option value="1">Administrador (Total)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: var(--text-primary) !important;">Contraseña <span id="pwdLabelHelp" class="small" style="color: var(--text-secondary) !important;"></span></label>
                        <input type="password" name="password" id="usuario_password" class="form-control" minlength="6" placeholder="Mínimo 6 caracteres">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gradient-primary"><i class="bi bi-save me-1"></i> Aplicar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulario Oculto para Eliminar -->
<form id="formEliminar" action="<?= BASE_URL ?>admin/usuarios/eliminar" method="POST" class="requires-auth d-none">
    <input type="hidden" name="id" id="eliminar_id">
</form>

<!-- Modal de Seguridad (Confirmación de Contraseña Admin) -->
<div class="modal fade" id="modalAuthAdmin" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-danger shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-shield-lock-fill me-2"></i>Verificación Requerida</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="cancelarAuth()"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="small mb-3" style="color: var(--text-secondary) !important;">Estás a punto de realizar una acción crítica. Ingresa tu contraseña de Administrador para confirmar.</p>
                <input type="password" id="adminPasswordInput" class="form-control text-center mb-3 fw-bold" placeholder="Tu Contraseña" required>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-secondary w-50" data-bs-dismiss="modal" onclick="cancelarAuth()">Cancelar</button>
                    <button type="button" class="btn btn-sm btn-danger w-50 fw-bold" onclick="confirmarAuth()">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let pendingForm = null;
let authModal = null;

document.addEventListener('DOMContentLoaded', function() {
    authModal = new bootstrap.Modal(document.getElementById('modalAuthAdmin'));

    // Interceptar envíos de formularios que requieran auth
    document.querySelectorAll('form.requires-auth').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            pendingForm = this;
            document.getElementById('adminPasswordInput').value = '';
            authModal.show();
            setTimeout(() => document.getElementById('adminPasswordInput').focus(), 500);
        });
    });
    
    // Escuchar Enter en el modal
    document.getElementById('adminPasswordInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            confirmarAuth();
        }
    });
});

function confirmarAuth() {
    const pwd = document.getElementById('adminPasswordInput').value;
    if (!pwd) {
        Swal.fire({icon: 'warning', title: 'Requerido', text: 'Debes ingresar tu contraseña para continuar.', confirmButtonColor: '#f59e0b'});
        return;
    }
    
    // Añadir la contraseña al formulario original y enviarlo
    if (pendingForm) {
        let input = pendingForm.querySelector('input[name="admin_password"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'admin_password';
            pendingForm.appendChild(input);
        }
        input.value = pwd;
        
        // Quitar la clase para evitar el ciclo infinito y enviar
        pendingForm.classList.remove('requires-auth');
        authModal.hide();
        pendingForm.submit();
    }
}

function cancelarAuth() {
    pendingForm = null;
    document.getElementById('adminPasswordInput').value = '';
}

// Preparar modal de CRUD Usuario
function prepararModalUsuario(user = null) {
    const isEdit = user !== null;
    document.getElementById('modalUsuarioTitulo').innerHTML = isEdit ? '<i class="bi bi-pencil-square me-2"></i>Editar Usuario' : '<i class="bi bi-person-plus me-2"></i>Nuevo Usuario';
    document.getElementById('usuario_id').value = isEdit ? user.id : '0';
    document.getElementById('usuario_nombre').value = isEdit ? user.nombre : '';
    document.getElementById('usuario_correo').value = isEdit ? user.correo : '';
    document.getElementById('usuario_rol').value = isEdit ? user.rol_id : '3';
    
    const pwdInput = document.getElementById('usuario_password');
    pwdInput.value = '';
    pwdInput.required = !isEdit;
    document.getElementById('pwdLabelHelp').innerText = isEdit ? '(Opcional. Llenar solo para cambiar)' : '(Obligatorio)';
}

// Preparar modal de Eliminar
function prepararModalEliminar(id, nombre) {
    Swal.fire({
        title: '¿Eliminar Usuario?',
        html: `Estás a punto de eliminar al usuario <b>${nombre}</b>. Esta acción requerirá tu contraseña.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('eliminar_id').value = id;
            document.getElementById('formEliminar').dispatchEvent(new Event('submit')); // Trigger submit to show auth modal
        }
    });
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
