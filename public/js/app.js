/**
 * Script Principal del Sistema SGBV (app.js)
 * Contiene funciones globales de UI/UX, modales, carrito, suscripciones y alertas.
 */

// Tarea 9: Cerrado automático de notificaciones y alertas en pantalla
document.addEventListener('DOMContentLoaded', () => {
    const autoCloseAlerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    autoCloseAlerts.forEach(alertEl => {
        // Ignorar alertas dentro de modales o las explicativas del pie/panel
        if (alertEl.closest('.modal') || alertEl.classList.contains('alert-dark') || alertEl.classList.contains('alert-permanent')) {
            return;
        }

        // Si tiene botón de cierre, o es notificación temporal de sesión
        if (alertEl.classList.contains('alert-dismissible') || alertEl.getAttribute('role') === 'alert') {
            setTimeout(() => {
                alertEl.style.transition = 'opacity 0.6s ease, max-height 0.6s ease, margin 0.6s ease, padding 0.6s ease';
                alertEl.style.opacity = '0';
                alertEl.style.maxHeight = '0px';
                alertEl.style.margin = '0px';
                alertEl.style.padding = '0px';
                alertEl.style.overflow = 'hidden';
                setTimeout(() => alertEl.remove(), 600);
            }, 4500); // Cierra en 4.5 segundos
        }
    });

    // Limpieza global de backdrop de modales al cerrarse para evitar que la pantalla quede semi oscura
    document.addEventListener('hidden.bs.modal', function () {
        if (!document.querySelector('.modal.show')) {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }
    });
});

/**
 * Tarea 6: Catálogo debe mostrar información técnica e individual del recurso (Modal de Detalles)
 */
function openBookDetailModal(id, titulo, autor, isbn, categoriaNombre, anio, tipo, disponibilidad, precio, descripcion, portada = '') {
    const titleEl = document.getElementById('detailTitle');
    const authorEl = document.getElementById('detailAuthor');
    const isbnEl = document.getElementById('detailIsbn');
    const catEl = document.getElementById('detailCategory');
    const yearEl = document.getElementById('detailYear');
    const typeEl = document.getElementById('detailTypeBadge');
    const stockEl = document.getElementById('detailStock');
    const descEl = document.getElementById('detailDescription');
    const priceEl = document.getElementById('detailPriceBadge');
    const btnContainer = document.getElementById('detailActions');
    const coverImg = document.getElementById('detailCoverImg');
    const coverIcon = document.getElementById('detailCoverIcon');

    if (!titleEl || !btnContainer) return;

    titleEl.textContent = titulo || 'Sin Título';
    if (authorEl) authorEl.textContent = autor || 'Desconocido';
    if (isbnEl) isbnEl.textContent = isbn || 'N/A';
    if (catEl) catEl.textContent = categoriaNombre || 'General';
    if (yearEl) yearEl.textContent = anio || 'N/A';
    if (typeEl) typeEl.textContent = (tipo || 'Libro').toUpperCase();
    
    if (stockEl) {
        stockEl.textContent = `${disponibilidad} uds en inventario`;
        stockEl.className = `badge ${disponibilidad > 0 ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger'}`;
    }

    if (descEl) {
        descEl.textContent = descripcion && descripcion.trim() !== '' ? descripcion : 'Este recurso literario no posee una descripción detallada en este momento. Puedes consultar el ISBN o autor para mayores referencias de su contenido.';
    }
    if (priceEl) priceEl.textContent = `${parseFloat(precio || 0).toFixed(2)} ⛃`;

    // Manejo de imagen de portada en modal
    if (coverImg && coverIcon) {
        if (portada && portada !== 'default_cover.jpg' && portada !== '') {
            coverImg.onerror = function() {
                this.classList.add('d-none');
                if (coverIcon) coverIcon.classList.remove('d-none');
            };
            coverImg.src = `${BASE_URL}public/uploads/portadas/${portada}`;
            coverImg.classList.remove('d-none');
            coverIcon.classList.add('d-none');
        } else {
            coverImg.classList.add('d-none');
            coverIcon.classList.remove('d-none');
        }
    }

    // Configurar el botón de acción en el modal según disponibilidad
    if (disponibilidad > 0) {
        btnContainer.innerHTML = `
            <button type="button" class="btn btn-outline-custom btn-sm py-2 px-3 text-info me-2" onclick="addToCart(${id}, \`${titulo.replace(/`/g, '\\`')}\`)">
                <i class="bi bi-cart-plus me-1"></i> Al Carrito
            </button>
            <button type="button" class="btn btn-gradient-primary py-2 px-4 rounded-pill fw-bold" onclick="bootstrap.Modal.getInstance(document.getElementById('viewBookModal')).hide(); openConfirmModal(${id}, \`${titulo.replace(/`/g, '\\`')}\`, ${precio});">
                <i class="bi bi-bookmark-plus me-1"></i> Rentar Ahora
            </button>
        `;
    } else {
        btnContainer.innerHTML = `
            <button type="button" class="btn btn-outline-warning py-2 px-4 rounded-pill fw-bold" onclick="bootstrap.Modal.getInstance(document.getElementById('viewBookModal')).hide(); subscribeToResource(${id}, \`${titulo.replace(/`/g, '\\`')}\`);">
                <i class="bi bi-bell-fill me-1"></i> Suscribirme y Avisar Disponibilidad
            </button>
        `;
    }

    const modalEl = document.getElementById('viewBookModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

/**
 * Tarea 7: El botón de rentar no debe rentar automáticamente sin confirmación previa
 */
function openConfirmModal(id, titulo, precio) {
    const titleEl = document.getElementById('confirmBookTitle');
    const priceEl = document.getElementById('confirmBookPrice');
    const inputIdEl = document.getElementById('confirmRecursoId');

    if (!titleEl || !inputIdEl) return;

    titleEl.textContent = titulo;
    if (priceEl) priceEl.textContent = `${parseFloat(precio || 0).toFixed(2)} Créditos ⛃`;
    inputIdEl.value = id;

    const modalEl = document.getElementById('confirmRentarModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

/**
 * Tarea 8: Añadir al carrito sin perder la página ni recargar abruptamente (o con feedback claro)
 */
function addToCart(id, titulo) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${BASE_URL}estandar/carrito/agregar`;
    
    const inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'recurso_id';
    inputId.value = id;

    form.appendChild(inputId);
    document.body.appendChild(form);
    form.submit();
}

/**
 * Tarea 12: Suscripción para notificación cuando un producto agotado vuelva a estar disponible
 */
function subscribeToResource(id, titulo) {
    if (!confirm(`¿Deseas suscribirte para recibir un aviso en cuanto el recurso "${titulo}" tenga unidades disponibles en inventario?`)) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${BASE_URL}estandar/suscribir`;

    const inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'recurso_id';
    inputId.value = id;

    form.appendChild(inputId);
    document.body.appendChild(form);
    form.submit();
}

/**
 * Abre el modal de detalles obteniendo la información fresca y robusta por ID desde el servidor (Tarea 4 & 8)
 */
function openBookModalById(id) {
    // Cerrar carrito si está abierto en offcanvas
    const cartEl = document.getElementById('cartOffcanvas');
    if (cartEl && typeof bootstrap !== 'undefined') {
        const bsOffcanvas = bootstrap.Offcanvas.getInstance(cartEl);
        if (bsOffcanvas) bsOffcanvas.hide();
    }

    fetch(`${BASE_URL}api/recurso?id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.exito && data.recurso) {
                const r = data.recurso;
                openBookDetailModal(r.id, r.titulo, r.autor, r.isbn, r.categoria_nombre, r.anio_publicacion, r.tipo, r.disponibilidad, r.precio_renta, r.descripcion, r.portada);
            } else {
                alert('No se pudieron cargar los detalles del recurso.');
            }
        })
        .catch(e => console.error('Error abriendo modal por ID:', e));
}

/**
 * Abre el modal de edición de recursos desde atributo data-recurso robusto para soportar libros futuros y caracteres especiales (Tarea 10)
 */
function openEditModalFromAttr(el) {
    const dataStr = el.getAttribute('data-recurso');
    if (!dataStr) return;
    try {
        const r = JSON.parse(dataStr);
        openEditModal(r.id, r.titulo || '', r.autor || '', r.isbn || '', r.categoria_id || 1, r.anio_publicacion || 2024, r.tipo || 'libro', r.disponibilidad || 0, r.precio_renta || 0, r.descripcion || '');
    } catch (e) {
        console.error('Error parseando datos para edición:', e);
    }
}

/**
 * Tarea 6: Sistema Global de Máscaras y Restricciones Automáticas de Formato (Cédula, ISBN, Números, Guiones)
 */
function inicializarMascarasYRestricciones() {
    // 1. Cédula: 000-0000000-0
    document.querySelectorAll('input[name="cedula"], #cedula, #verificar_cedula_input').forEach(input => {
        input.setAttribute('maxlength', '13');
        input.addEventListener('input', function(e) {
            let val = this.value.replace(/\D/g, '');
            if (val.length > 11) val = val.slice(0, 11);
            if (val.length > 10) {
                this.value = val.slice(0, 3) + '-' + val.slice(3, 10) + '-' + val.slice(10);
            } else if (val.length > 3) {
                this.value = val.slice(0, 3) + '-' + val.slice(3);
            } else {
                this.value = val;
            }
        });
    });

    // 2. ISBN: 978-0-00-000000-0
    document.querySelectorAll('input[name="isbn"], #isbn, #edit_isbn').forEach(input => {
        input.setAttribute('maxlength', '17');
        input.addEventListener('input', function(e) {
            let val = this.value.replace(/[^\dXx]/g, '');
            if (val.length > 13) val = val.slice(0, 13);
            if (val.length > 12) {
                this.value = val.slice(0, 3) + '-' + val.slice(3, 4) + '-' + val.slice(4, 6) + '-' + val.slice(6, 12) + '-' + val.slice(12);
            } else if (val.length > 6) {
                this.value = val.slice(0, 3) + '-' + val.slice(3, 4) + '-' + val.slice(4, 6) + '-' + val.slice(6);
            } else if (val.length > 4) {
                this.value = val.slice(0, 3) + '-' + val.slice(3, 4) + '-' + val.slice(4);
            } else if (val.length > 3) {
                this.value = val.slice(0, 3) + '-' + val.slice(3);
            } else {
                this.value = val;
            }
        });
    });

    // 3. Solo Números enteros (Disponibilidad, Año)
    document.querySelectorAll('input[name="disponibilidad"], input[name="anio_publicacion"]').forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });
    });

    // 4. Saldo y Precios (Números y un punto decimal)
    document.querySelectorAll('input[name="precio_renta"], input[name="monto"]').forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9.]/g, '');
            const parts = this.value.split('.');
            if (parts.length > 2) {
                this.value = parts[0] + '.' + parts.slice(1).join('');
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    inicializarMascarasYRestricciones();
});

