/**
 * Utilidades de Tabla del Sistema SGBV:
 * 1. Búsqueda y filtrado en tiempo real en tablas HTML (Tarea 14)
 * 2. Ordenamiento de columnas (Ascendente / Descendente) (Tarea 14)
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Búsqueda interactiva instantánea en tablas
    const searchInputs = document.querySelectorAll('.table-search-input');
    searchInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            const query = e.target.value.trim().toLowerCase();
            const targetTableId = input.getAttribute('data-table');
            const table = targetTableId ? document.getElementById(targetTableId) : input.closest('.glass-card, .container').querySelector('table');
            
            if (!table) return;
            const tbody = table.querySelector('tbody');
            if (!tbody) return;

            const rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                // Si la fila es de "No hay resultados", no ocultarla ni procesarla normal salvo chequeo global
                if (row.cells.length === 1 && row.cells[0].colSpan > 1) return;

                const textContent = row.textContent || row.innerText;
                if (textContent.toLowerCase().includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // 2. Ordenamiento interactivo de columnas por clic en encabezados .sortable
    const sortableTables = document.querySelectorAll('.sortable-table');
    sortableTables.forEach(table => {
        const headers = table.querySelectorAll('th.sortable');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            
            // Inicializar visualmente la primera columna (#) como orden ascendente por defecto
            if (header.getAttribute('data-column') === '0') {
                header.setAttribute('data-order', 'asc');
                const icon = header.querySelector('i.bi');
                if (icon) {
                    icon.className = 'bi bi-sort-numeric-down float-end text-primary';
                }
            }

            header.addEventListener('click', () => {
                const colIndex = parseInt(header.getAttribute('data-column') || '0', 10);
                const tbody = table.querySelector('tbody');
                if (!tbody) return;

                const rows = Array.from(tbody.querySelectorAll('tr'));
                // Si la tabla no tiene datos o solo una fila de vacío, salir
                if (rows.length <= 1 && rows[0] && rows[0].cells.length === 1 && rows[0].cells[0].colSpan > 1) return;

                const currentAsc = header.getAttribute('data-order') === 'asc';
                const newAsc = !currentAsc;

                // Reiniciar iconos y atributos de orden en los otros encabezados
                headers.forEach(h => {
                    h.removeAttribute('data-order');
                    const icon = h.querySelector('i.bi');
                    if (icon) {
                        icon.className = 'bi bi-arrow-down-up float-end text-secondary';
                    }
                });

                header.setAttribute('data-order', newAsc ? 'asc' : 'desc');
                const icon = header.querySelector('i.bi');
                if (icon) {
                    icon.className = newAsc ? 'bi bi-sort-alpha-down float-end text-primary' : 'bi bi-sort-alpha-up float-end text-primary';
                }

                rows.sort((rowA, rowB) => {
                    const cellA = rowA.cells[colIndex];
                    const cellB = rowB.cells[colIndex];
                    if (!cellA || !cellB) return 0;

                    let textA = (cellA.textContent || cellA.innerText).trim();
                    let textB = (cellB.textContent || cellB.innerText).trim();

                    // Limpiar símbolos de moneda ($) o hashtags (#) o fechas si es numérico/moneda
                    const numA = parseFloat(textA.replace(/[^0-9.-]+/g, ''));
                    const numB = parseFloat(textB.replace(/[^0-9.-]+/g, ''));

                    // Intentar comparar numéricamente si ambos parecen números puros o montos
                    if (!isNaN(numA) && !isNaN(numB) && (/^[#$]?\s*\d/.test(textA) || /^\d/.test(textA))) {
                        return newAsc ? (numA - numB) : (numB - numA);
                    }

                    // Comparación alfabética estándar (Intl.Collator)
                    return newAsc ? textA.localeCompare(textB, 'es', { numeric: true }) : textB.localeCompare(textA, 'es', { numeric: true });
                });

                // Re-insertar filas ordenadas en el DOM
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });
});
