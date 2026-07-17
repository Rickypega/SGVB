/**
 * Motor de Búsqueda Interactivo de SGBV en tiempo real (AJAX / Fetch)
 */
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const categorySelect = document.getElementById('categorySelect');
    const booksGrid = document.getElementById('booksGrid');
    const totalResultsBadge = document.getElementById('totalResultsCount');
    let debounceTimer = null;

    if (!searchInput || !booksGrid) {
        return; // No estamos en la vista con el catálogo dinámico
    }

    const performSearch = async () => {
        const query = searchInput.value.trim();
        const categoriaId = categorySelect ? categorySelect.value : 0;

        try {
            booksGrid.style.opacity = '0.5';
            const response = await fetch(`${BASE_URL}buscar?q=${encodeURIComponent(query)}&categoria_id=${encodeURIComponent(categoriaId)}&ajax=1`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            booksGrid.style.opacity = '1';

            if (totalResultsBadge && data.total !== undefined) {
                totalResultsBadge.textContent = `${data.total} recursos encontrados`;
            }

            renderBooks(data.recursos || []);
        } catch (error) {
            console.error('Error al realizar la búsqueda:', error);
            booksGrid.style.opacity = '1';
        }
    };

    const renderBooks = (recursos) => {
        booksGrid.innerHTML = '';

        if (recursos.length === 0) {
            booksGrid.innerHTML = `
                <div class="col-12 text-center py-5 my-3">
                    <div class="glass-card p-5 max-w-600 mx-auto">
                        <i class="bi bi-search fs-1 text-secondary mb-3 d-block"></i>
                        <h4 class="fw-bold">No se encontraron resultados</h4>
                        <p class="text-muted">No hay libros, audiolibros ni artículos que coincidan con los criterios de búsqueda elegidos.</p>
                        <button class="btn btn-outline-custom mt-2" onclick="document.getElementById('searchInput').value=''; document.getElementById('categorySelect').value='0'; document.getElementById('searchInput').dispatchEvent(new Event('input'));">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restablecer Filtros
                        </button>
                    </div>
                </div>
            `;
            return;
        }

        recursos.forEach(r => {
            const esLibro = r.tipo === 'libro';
            const esAudio = r.tipo === 'audiolibro';
            const iconoTipo = esLibro ? 'bi-book-half' : (esAudio ? 'bi-headphones' : 'bi-file-earmark-text');
            const colorTipo = esLibro ? 'bg-primary' : (esAudio ? 'bg-info text-dark' : 'bg-warning text-dark');
            const disponible = r.disponibilidad > 0;

            const cardHtml = `
                <div class="col-12 col-md-6 col-lg-4 col-xl-3 animate-fade-in">
                    <div class="book-card glass-card h-100" style="cursor: pointer;" onclick="openBookModalById(${r.id})">
                        <div class="book-cover-container position-relative overflow-hidden d-flex align-items-center justify-content-center">
                            <span class="book-badge-type ${colorTipo}"><i class="bi ${iconoTipo} me-1"></i> ${r.tipo}</span>
                            <span class="badge ${disponible ? 'bg-success' : 'bg-danger'} text-white px-2 py-1 shadow-sm" style="position: absolute; top: 12px; right: 12px; z-index: 2; font-weight: 600;">
                                <i class="bi ${disponible ? 'bi-check-circle-fill' : 'bi-x-circle-fill'} me-1"></i> Disponibles: ${r.disponibilidad}
                            </span>
                            ${r.portada && r.portada !== 'default_cover.jpg' ? `
                                <img src="${BASE_URL}public/uploads/portadas/${r.portada}" class="book-cover-img w-100 h-100" style="object-fit: cover;" alt="${r.titulo}" onerror="this.onerror=null; this.classList.add('d-none'); const p = this.parentElement.querySelector('.book-cover-placeholder'); if(p) p.classList.remove('d-none');">
                                <i class="bi ${iconoTipo} book-cover-placeholder d-none"></i>
                            ` : `
                                <i class="bi ${iconoTipo} book-cover-placeholder"></i>
                            `}
                        </div>
                        <div class="book-card-body d-flex flex-column">
                            <div class="mb-2">
                                <span class="badge bg-dark border border-secondary text-secondary small">${r.categoria_nombre}</span>
                            </div>
                            <h5 class="book-title text-truncate" title="${r.titulo}">${r.titulo}</h5>
                            <p class="book-author mb-2"><i class="bi bi-person me-1"></i> ${r.autor} (${r.anio_publicacion})</p>
                            <p class="text-muted small flex-grow-1 text-truncate-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                ${r.descripcion || 'Sin descripción disponible para este recurso literario.'}
                            </p>
                            <div class="mt-3 pt-3 border-top border-secondary d-flex justify-content-between align-items-center" onclick="event.stopPropagation();">
                                <span class="fw-bold text-gradient-accent fs-5">${r.precio_renta} Créditos ⛃</span>
                                <div class="d-flex gap-1 align-items-center">
                                    ${disponible ? `
                                        <button type="button" class="btn btn-outline-custom btn-sm py-1 px-2 text-info" onclick="addToCart(${r.id}, \`${r.titulo.replace(/`/g, '\\`')}\`)" title="Añadir al Carrito">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-gradient-primary btn-sm rounded-pill px-3" onclick="openConfirmModal(${r.id}, \`${r.titulo.replace(/`/g, '\\`')}\`, ${r.precio_renta})">
                                            <i class="bi bi-bookmark-plus me-1"></i> Rentar
                                        </button>
                                    ` : `
                                        <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-2 py-1" onclick="subscribeToResource(${r.id}, \`${r.titulo.replace(/`/g, '\\`')}\`)" title="Recibir aviso cuando esté disponible">
                                            <i class="bi bi-bell-fill me-1"></i> Suscribirme
                                        </button>
                                    `}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            booksGrid.insertAdjacentHTML('beforeend', cardHtml);
        });
    };

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performSearch, 250);
    });

    if (categorySelect) {
        categorySelect.addEventListener('change', () => {
            clearTimeout(debounceTimer);
            performSearch();
        });
    }
});
