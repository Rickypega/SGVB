<?php
$tituloPagina = 'Recargar Billetera Virtual | SGBV';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container py-4 my-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="glass-card p-4 p-sm-5 shadow-lg position-relative border border-primary">
                <div class="position-absolute top-0 start-50 translate-middle-x w-50" style="height: 3px; background: linear-gradient(90deg, transparent, #0d6efd, transparent);"></div>
                
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3 fs-1 text-primary">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h2 class="fw-bold">Recargar Billetera</h2>
                    <p class="text-muted small">Añade fondos de forma segura usando Stripe (Modo Pruebas)</p>
                </div>

                <div class="alert alert-info border border-info rounded-3 p-3 mb-4 d-flex gap-3 align-items-center">
                    <i class="bi bi-info-circle-fill fs-3 text-info"></i>
                    <div class="small">
                        Estás a punto de recargar tu billetera virtual SGBV. Los fondos se convertirán en <strong>Créditos ⛃</strong> para rentar recursos de nuestra biblioteca.
                    </div>
                </div>

                <form id="pagoForm">
                    <div class="mb-4">
                        <label for="monto" class="form-label fw-bold text-secondary">Selecciona o ingresa el monto (USD)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-dark border-secondary text-light">$</span>
                            <input type="number" class="form-control" id="monto" name="monto" min="5" max="500" step="1" value="10.00" required>
                        </div>
                        <div class="form-text text-muted">Mínimo: $5.00 | Máximo: $500.00</div>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-primary w-100 py-2 small fw-bold" onclick="document.getElementById('monto').value='10.00'">$10.00</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-primary w-100 py-2 small fw-bold" onclick="document.getElementById('monto').value='20.00'">$20.00</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-primary w-100 py-2 small fw-bold" onclick="document.getElementById('monto').value='50.00'">$50.00</button>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" id="btnPagar" class="btn btn-gradient-primary py-3 fw-bold fs-5 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-credit-card-2-front"></i> Ir a Pagar con Stripe
                        </button>
                        <a href="<?= BASE_URL ?>estandar/panel" class="btn btn-outline-secondary mt-2">Cancelar</a>
                    </div>
                </form>

                <div class="text-center mt-4 text-secondary small">
                    <i class="bi bi-shield-check text-success"></i> Pago procesado de forma segura mediante <a href="https://stripe.com" target="_blank" class="text-info text-decoration-none">Stripe</a>.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SDK Stripe JS v3 -->
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stripe = Stripe('<?= htmlspecialchars($stripePublishableKey ?? '') ?>');
    const btnPagar = document.getElementById('btnPagar');
    const form = document.getElementById('pagoForm');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const monto = document.getElementById('monto').value;
        const originalText = btnPagar.innerHTML;
        btnPagar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Conectando con Stripe...';
        btnPagar.disabled = true;

        fetch('<?= BASE_URL ?>billetera/procesar-pago', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'monto=' + encodeURIComponent(monto) + '&ajax=1'
        })
        .then(response => response.text())
        .then(text => {
            console.log("Respuesta RAW del servidor:", text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("Error parseando JSON:", e);
                throw new Error("El servidor devolvió una respuesta no válida (no JSON). Revisa la consola.");
            }
        })
        .then(session => {
            if (session.exito) {
                return stripe.redirectToCheckout({ sessionId: session.id });
            } else {
                throw new Error(session.error || 'Error al crear la sesión de pago.');
            }
        })
        .then(result => {
            if (result && result.error) {
                throw new Error(result.error.message);
            }
        })
        .catch(error => {
            Swal.fire('Error', error.message, 'error');
            btnPagar.innerHTML = originalText;
            btnPagar.disabled = false;
        });
    });
});
</script>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
