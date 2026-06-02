<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0" style="color:var(--color-texto-principal);">Áreas del sistema</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Agrupa los módulos del sidebar en secciones visuales</div>
        </div>
        <?php if (puedo('cfg/areas', 'crear')): ?>
        <div class="col-auto">
            <button id="btn_nueva_area" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-plus me-1"></i>
                Nueva área
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_areas" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Ícono</th>
                        <th>Orden</th>
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
var PERMISOS_AREAS = <?= json_encode([
    'crear'    => puedo('cfg/areas', 'crear'),
    'editar'   => puedo('cfg/areas', 'editar'),
    'eliminar' => puedo('cfg/areas', 'eliminar'),
]) ?>;
</script>

<!-- Modal agregar/editar área -->
<div class="modal fade" id="modal_area" tabindex="-1" aria-labelledby="modal_area_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form_area" autocomplete="off">
                <input type="hidden" id="area_id" name="id" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_area_titulo">Nueva área</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="area_nombre">Nombre</label>
                            <input type="text" id="area_nombre" name="nombre" class="form-control"
                                placeholder="Ej: Administración" required maxlength="100">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="area_icono">
                                Ícono <small class="text-muted fw-normal">(class CSS completa de Tabler Icons)</small>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" id="area_icono_preview_wrap">
                                    <i id="area_icono_preview" class="ti ti-circle"></i>
                                </span>
                                <input type="text" id="area_icono" name="icono" class="form-control"
                                    placeholder="Ej: ti ti-settings" required maxlength="100">
                            </div>
                            <div class="form-text">Consulta los íconos en <a href="https://tabler.io/icons" target="_blank">tabler.io/icons</a></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="area_orden">Orden</label>
                            <input type="number" id="area_orden" name="orden" class="form-control"
                                value="0" min="0" max="999">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="area_estado">Estado</label>
                            <select id="area_estado" name="estado" class="form-select">
                                <option value="0">Activo</option>
                                <option value="1">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_area" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
