<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Sucursales</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Gestión de sucursales del negocio</div>
        </div>
        <?php if (puedo('admin/sucursales', 'crear')): ?>
        <div class="col-auto">
            <button id="btn_nueva_sucursal" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-plus me-1"></i>Nueva sucursal
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_sucursales" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
var PERMISOS_SUCURSALES = <?= json_encode([
    'crear'    => puedo('admin/sucursales', 'crear'),
    'editar'   => puedo('admin/sucursales', 'editar'),
    'eliminar' => puedo('admin/sucursales', 'eliminar'),
]) ?>;
</script>

<!-- Modal agregar/editar sucursal -->
<div class="modal fade" id="modal_sucursal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form_sucursal" autocomplete="off">
                <input type="hidden" id="sucursal_id" name="id" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_sucursal_titulo">Nueva sucursal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="sucursal_nombre">Nombre</label>
                            <input type="text" id="sucursal_nombre" name="nombre" class="form-control"
                                placeholder="Ej: Sucursal Centro" required maxlength="150">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="sucursal_direccion">
                                Dirección <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <input type="text" id="sucursal_direccion" name="direccion" class="form-control"
                                placeholder="Calle, número, colonia" maxlength="300">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="sucursal_telefono">
                                Teléfono <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <input type="text" id="sucursal_telefono" name="telefono" class="form-control"
                                placeholder="10 dígitos" maxlength="20">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="sucursal_estado">Estado</label>
                            <select id="sucursal_estado" name="estado" class="form-select">
                                <option value="0">Activa</option>
                                <option value="1">Inactiva</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_sucursal" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        <i class="ti ti-check me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
