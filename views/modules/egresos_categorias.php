<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Categorías de egresos</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Gestión de categorías y subcategorías de egresos</div>
        </div>
    </div>
</div>

<!-- Categorías principales -->
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h4 class="card-title mb-0">Categorías principales</h4>
        <?php if (puedo('egresos/categorias', 'crear')): ?>
        <button id="btn_nueva_categoria" class="btn btn-primary btn-sm"
            style="background-color:var(--color-primario);border-color:var(--color-primario);">
            <i class="ti ti-plus me-1"></i>Nueva categoría
        </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_categorias_padres" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
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

<!-- Subcategorías (se muestra al seleccionar una categoría padre) -->
<div class="card d-none" id="card_subcategorias">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <h4 class="card-title mb-0">Subcategorías de <span id="lbl_padre_nombre" class="text-primary"></span></h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if (puedo('egresos/categorias', 'crear')): ?>
            <button id="btn_nueva_subcategoria" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-plus me-1"></i>Nueva subcategoría
            </button>
            <?php endif; ?>
            <button id="btn_cerrar_subcategorias" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-x"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_subcategorias" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
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
var PERMISOS_EG_CATEGORIAS = <?= json_encode([
    'crear'    => puedo('egresos/categorias', 'crear'),
    'editar'   => puedo('egresos/categorias', 'editar'),
    'eliminar' => puedo('egresos/categorias', 'eliminar'),
]) ?>;
var APP_URL_EG_CATEGORIAS = '<?= $app_url ?>';
</script>

<!-- Modal categoría/subcategoría -->
<div class="modal fade" id="modal_categoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form_categoria" autocomplete="off">
                <input type="hidden" id="categoria_id"       name="id"       value="">
                <input type="hidden" id="categoria_id_padre" name="id_padre" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_categoria_titulo">Nueva categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="info_padre" class="alert alert-light border mb-3 py-2 d-none" style="font-size:0.85rem;">
                        <i class="ti ti-tag me-1 text-muted"></i>
                        Subcategoría de: <strong id="info_padre_nombre"></strong>
                    </div>
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
                            <textarea id="categoria_descripcion" name="descripcion" class="form-control" rows="2"
                                placeholder="Descripción"></textarea>
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
