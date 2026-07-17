<?php
$tituloPagina = 'Gestión de Recursos | SGBV Admin';
require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="container py-5">
    <!-- Cabecera -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <span class="badge bg-dark border border-secondary text-info px-3 py-2 mb-2">
                <i class="bi bi-gear-fill me-1"></i> Administración de Inventario
            </span>
            <h2 class="fw-extrabold text-gradient mb-1">Catálogo Literario y Recursos Digitales</h2>
            <p class="text-secondary mb-0">Crea, edita o elimina libros, audiolibros y artículos del sistema en tiempo real.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-custom d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
                <i class="bi bi-tags-fill text-warning"></i> Categorías
            </button>
            <a href="<?= BASE_URL ?>admin/exportar?tipo=recursos" class="btn btn-gradient-secondary d-flex align-items-center gap-2" title="Exportar catálogo en Excel / CSV">
                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV
            </a>
            <button type="button" onclick="window.print()" class="btn btn-outline-custom d-flex align-items-center gap-2" title="Imprimir catálogo">
                <i class="bi bi-printer"></i> Imprimir / PDF
            </button>
            <button type="button" class="btn btn-gradient-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createResourceModal">
                <i class="bi bi-plus-lg"></i> Añadir Nuevo Recurso
            </button>
        </div>
    </div>

    <!-- Tabla de Recursos CRUD -->
    <div class="glass-card overflow-hidden">
        <!-- Barra de búsqueda y filtros (Tarea 14) -->
        <div class="p-3 border-bottom border-secondary bg-dark bg-opacity-50 d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div class="input-group" style="max-width: 320px;">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control form-control-sm table-search-input" data-table="recursosTable" placeholder="Filtrar por título, ISBN, autor...">
            </div>
            <div class="small text-secondary">
                <i class="bi bi-collection me-1"></i> Total en inventario: <?= count($recursos ?? []) ?> recursos
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-custom align-middle mb-0 sortable-table" id="recursosTable">
                <thead>
                    <tr>
                        <th class="sortable" data-column="0"># <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="1">Título e ISBN <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="2">Autor / Año <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="3">Categoría <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="4">Tipo <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="5">Precio Renta <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="sortable" data-column="6">Disponibles <i class="bi bi-arrow-down-up float-end"></i></th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recursos) && is_array($recursos)): ?>
                        <?php foreach ($recursos as $r): ?>
                            <tr>
                                <td><span class="badge bg-dark border border-secondary text-secondary">#<?= $r->id ?></span></td>
                                <td>
                                    <div class="fw-bold text-light"><?= htmlspecialchars($r->titulo) ?></div>
                                    <div class="small text-secondary"><i class="bi bi-upc-scan me-1"></i> <?= htmlspecialchars($r->isbn) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($r->autor) ?></div>
                                    <div class="small text-secondary"><?= $r->anio_publicacion ?></div>
                                </td>
                                <td><span class="badge bg-dark border border-secondary text-light"><?= htmlspecialchars($r->categoria_nombre) ?></span></td>
                                <td><span class="badge bg-dark border border-secondary text-info text-capitalize"><?= $r->tipo ?></span></td>
                                <td><span class="fw-bold text-gradient-accent fs-6"><?= number_format($r->precio_renta, 2) ?> ⛃</span></td>
                                <td>
                                    <span class="badge <?= $r->disponibilidad > 0 ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger' ?> px-3">
                                        Disponibles: <?= $r->disponibilidad ?> uds
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-custom btn-sm py-1 px-2"
                                                data-recurso="<?= htmlspecialchars(json_encode($r, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                                onclick="openEditModalFromAttr(this)"
                                                title="Editar Recurso">
                                            <i class="bi bi-pencil-square text-info"></i>
                                        </button>
                                        <form action="<?= BASE_URL ?>admin/recursos" method="POST" class="d-inline" onsubmit="return confirm('¿Confirmas que deseas eliminar permanentemente este recurso?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id" value="<?= $r->id ?>">
                                            <button type="submit" class="btn btn-outline-custom btn-sm py-1 px-2 text-danger" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">No se encontraron recursos en el catálogo.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Gestión, Edición y Añadir Categorías (Tarea 13 & 5) -->
<div class="modal fade" id="manageCategoriesModal" tabindex="-1" aria-labelledby="manageCategoriesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold text-gradient" id="manageCategoriesModalLabel"><i class="bi bi-tags me-2"></i>Categorías Literarias del Sistema</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="<?= BASE_URL ?>admin/categorias" method="POST" class="mb-4">
                    <label class="form-label fw-bold text-light">Añadir Nueva Categoría</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="nombre" required placeholder="Ej. Ciencia Ficción, Historia, Biografías...">
                        <button type="submit" class="btn btn-gradient-primary"><i class="bi bi-plus-lg me-1"></i> Añadir</button>
                    </div>
                </form>

                <h6 class="text-secondary text-uppercase fw-bold small mb-3">Editar Categorías Existentes en Catálogo</h6>
                <div class="d-flex flex-column gap-2 max-h-300 overflow-auto p-2 glass-card border-secondary">
                    <?php if (!empty($categorias) && is_array($categorias)): ?>
                        <?php foreach ($categorias as $cat): ?>
                            <?php $catId = $cat['id'] ?? $cat->id ?? 0; $catNombre = htmlspecialchars($cat['nombre'] ?? $cat->nombre ?? ''); ?>
                            <form action="<?= BASE_URL ?>admin/categorias/editar" method="POST" class="d-flex align-items-center justify-content-between gap-2 p-2 bg-dark rounded border border-secondary">
                                <input type="hidden" name="id" value="<?= $catId ?>">
                                <div class="d-flex align-items-center gap-2 flex-grow-1">
                                    <span class="badge bg-secondary">#<?= $catId ?></span>
                                    <input type="text" class="form-control form-control-sm bg-transparent border-0 text-light fw-bold" name="nombre" value="<?= $catNombre ?>" required>
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-info py-1 px-2" title="Guardar Cambio de Nombre">
                                    <i class="bi bi-check-lg"></i> Guardar
                                </button>
                            </form>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-muted small">No hay categorías cargadas.</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Añadir Nuevo Recurso -->
<div class="modal fade" id="createResourceModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold text-gradient" id="createModalLabel"><i class="bi bi-book-plus me-2"></i>Añadir Nuevo Recurso Digital</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>admin/recursos" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Título del Recurso *</label>
                            <input type="text" class="form-control" name="titulo" required placeholder="Ej. El Programador Pragmático">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Autor *</label>
                            <input type="text" class="form-control" name="autor" required placeholder="Ej. David Thomas">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">ISBN *</label>
                            <input type="text" class="form-control" name="isbn" required placeholder="978-0135957059">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoría *</label>
                            <select class="form-select" name="categoria_id" required>
                                <?php if (!empty($categorias)): ?>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Recurso *</label>
                            <select class="form-select" name="tipo" required>
                                <option value="libro">Libro Digital</option>
                                <option value="audiolibro">Audiolibro</option>
                                <option value="articulo">Articulo / Paper</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Año de Publicación</label>
                            <input type="number" class="form-control" name="anio_publicacion" value="<?= date('Y') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Disponibilidad (Stock)</label>
                            <input type="number" class="form-control" name="disponibilidad" value="5" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Precio Renta (Créditos ⛃)</label>
                            <input type="number" step="0.01" class="form-control" name="precio_renta" value="3.50" min="0" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-image me-1"></i> Imagen de Portada (Opcional)</label>
                            <input type="file" class="form-control" name="portada" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-file-earmark-pdf me-1"></i> Archivo de Lectura PDF (Opcional)</label>
                            <input type="file" class="form-control" name="archivo_pdf" accept=".pdf">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción Literaria</label>
                        <textarea class="form-control" name="descripcion" rows="3" placeholder="Resumen del contenido..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gradient-primary"><i class="bi bi-check-lg me-1"></i> Guardar Recurso</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Recurso -->
<div class="modal fade" id="editResourceModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold text-gradient" id="editModalLabel"><i class="bi bi-pencil-square me-2"></i>Editar Recurso Digital</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>admin/recursos" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Título del Recurso *</label>
                            <input type="text" class="form-control" id="edit_titulo" name="titulo" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Autor *</label>
                            <input type="text" class="form-control" id="edit_autor" name="autor" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">ISBN *</label>
                            <input type="text" class="form-control" id="edit_isbn" name="isbn" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoría *</label>
                            <select class="form-select" id="edit_categoria_id" name="categoria_id" required>
                                <?php if (!empty($categorias)): ?>
                                    <?php foreach ($categorias as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Recurso *</label>
                            <select class="form-select" id="edit_tipo" name="tipo" required>
                                <option value="libro">Libro Digital</option>
                                <option value="audiolibro">Audiolibro</option>
                                <option value="articulo">Articulo / Paper</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Año de Publicación</label>
                            <input type="number" class="form-control" id="edit_anio" name="anio_publicacion" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Disponibilidad (Stock)</label>
                            <input type="number" class="form-control" id="edit_disponibilidad" name="disponibilidad" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Precio Renta (Créditos ⛃)</label>
                            <input type="number" step="0.01" class="form-control" id="edit_precio" name="precio_renta" min="0" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-image me-1"></i> Cambiar Portada (Dejar vacío para conservar actual)</label>
                            <input type="file" class="form-control" name="portada" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-file-earmark-pdf me-1"></i> Cambiar PDF (Dejar vacío para conservar actual)</label>
                            <input type="file" class="form-control" name="archivo_pdf" accept=".pdf">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción Literaria</label>
                        <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gradient-secondary"><i class="bi bi-save me-1"></i> Actualizar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(id, titulo, autor, isbn, categoriaId, anio, tipo, disponibilidad, precio, descripcion) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_titulo').value = titulo;
    document.getElementById('edit_autor').value = autor;
    document.getElementById('edit_isbn').value = isbn;
    document.getElementById('edit_categoria_id').value = categoriaId;
    document.getElementById('edit_anio').value = anio;
    document.getElementById('edit_tipo').value = tipo;
    document.getElementById('edit_disponibilidad').value = disponibilidad;
    document.getElementById('edit_precio').value = precio;
    document.getElementById('edit_descripcion').value = descripcion;

    const modalEl = document.getElementById('editResourceModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
