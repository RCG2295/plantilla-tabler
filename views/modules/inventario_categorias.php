<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Categorías de inventario</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Organiza los productos en categorías y subcategorías</div>
        </div>
        <?php if (puedo('inventario/categorias', 'crear')): ?>
        <div class="col-auto">
            <button id="btn_nueva_categoria" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-plus me-1"></i>Nueva categoría
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tabla de categorías principales (nivel 1) -->
<div class="card" id="card_padres">
    <div class="card-header">
        <h4 class="card-title mb-0">Categorías principales</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_categorias" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Subcategorías</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tabla de subcategorías (nivel 2) — oculta hasta seleccionar padre -->
<div class="card mt-4" id="card_subcategorias" style="display:none;">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <button id="btn_volver_padres" class="btn btn-sm btn-outline-secondary me-2">
                <i class="ti ti-arrow-left me-1"></i>Volver
            </button>
            <span class="fw-semibold">Subcategorías de: <span id="label_padre" class="text-primary"></span></span>
        </div>
        <?php if (puedo('inventario/categorias', 'crear')): ?>
        <button id="btn_nueva_subcategoria" class="btn btn-sm btn-primary"
            style="background-color:var(--color-primario);border-color:var(--color-primario);">
            <i class="ti ti-plus me-1"></i>Nueva subcategoría
        </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_subcategorias" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
var PERMISOS_CATEGORIAS = <?= json_encode([
    'crear'    => puedo('inventario/categorias', 'crear'),
    'editar'   => puedo('inventario/categorias', 'editar'),
    'eliminar' => puedo('inventario/categorias', 'eliminar'),
]) ?>;
</script>

<!-- Modal agregar/editar categoría -->
<div class="modal fade" id="modal_categoria" tabindex="-1" aria-labelledby="modal_categoria_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form_categoria" autocomplete="off">
                <input type="hidden" id="categoria_id"      name="id"       value="">
                <input type="hidden" id="categoria_id_padre" name="id_padre" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_categoria_titulo">Nueva categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="categoria_nombre">Nombre</label>
                            <input type="text" id="categoria_nombre" name="nombre" class="form-control"
                                placeholder="Nombre" required maxlength="150">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="categoria_descripcion">
                                Descripción <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <input type="text" id="categoria_descripcion" name="descripcion" class="form-control"
                                placeholder="Descripción breve" maxlength="255">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="categoria_estado">Estado</label>
                            <select id="categoria_estado" name="estado" class="form-select">
                                <option value="0">Activo</option>
                                <option value="1">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_categoria" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
