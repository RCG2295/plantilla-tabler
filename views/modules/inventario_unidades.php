<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Unidades de medida</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Define las unidades utilizadas en los productos del inventario</div>
        </div>
        <?php if (puedo('inventario/unidades', 'crear')): ?>
        <div class="col-auto">
            <button id="btn_nueva_unidad" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-plus me-1"></i>Nueva unidad
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_unidades" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Abreviatura</th>
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
var PERMISOS_UNIDADES = <?= json_encode([
    'crear'    => puedo('inventario/unidades', 'crear'),
    'editar'   => puedo('inventario/unidades', 'editar'),
    'eliminar' => puedo('inventario/unidades', 'eliminar'),
]) ?>;
</script>

<!-- Modal agregar/editar unidad -->
<div class="modal fade" id="modal_unidad" tabindex="-1" aria-labelledby="modal_unidad_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form_unidad" autocomplete="off">
                <input type="hidden" id="unidad_id" name="id" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_unidad_titulo">Nueva unidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label fw-semibold" for="unidad_nombre">Nombre</label>
                            <input type="text" id="unidad_nombre" name="nombre" class="form-control"
                                placeholder="Ej: Kilogramo" required maxlength="100">
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold" for="unidad_abreviatura">Abreviatura</label>
                            <input type="text" id="unidad_abreviatura" name="abreviatura" class="form-control"
                                placeholder="Ej: kg" required maxlength="20">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="unidad_estado">Estado</label>
                            <select id="unidad_estado" name="estado" class="form-select">
                                <option value="0">Activo</option>
                                <option value="1">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_unidad" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
