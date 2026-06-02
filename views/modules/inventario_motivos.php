<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Motivos de movimiento</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Define los conceptos usados en las altas y bajas de inventario</div>
        </div>
        <?php if (puedo('inventario/motivos', 'crear')): ?>
        <div class="col-auto">
            <button id="btn_nuevo_motivo" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-plus me-1"></i>Nuevo motivo
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_motivos" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
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
var PERMISOS_MOTIVOS = <?= json_encode([
    'crear'    => puedo('inventario/motivos', 'crear'),
    'editar'   => puedo('inventario/motivos', 'editar'),
    'eliminar' => puedo('inventario/motivos', 'eliminar'),
]) ?>;
</script>

<!-- Modal agregar/editar motivo -->
<div class="modal fade" id="modal_motivo" tabindex="-1" aria-labelledby="modal_motivo_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form_motivo" autocomplete="off">
                <input type="hidden" id="motivo_id" name="id" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_motivo_titulo">Nuevo motivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="motivo_nombre">Nombre</label>
                            <input type="text" id="motivo_nombre" name="nombre" class="form-control"
                                placeholder="Ej: Compra" required maxlength="150">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="motivo_tipo">Tipo de movimiento</label>
                            <select id="motivo_tipo" name="tipo" class="form-select">
                                <option value="entrada">Entrada</option>
                                <option value="salida">Salida</option>
                                <option value="ambos">Ambos</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="motivo_estado">Estado</label>
                            <select id="motivo_estado" name="estado" class="form-select">
                                <option value="0">Activo</option>
                                <option value="1">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_motivo" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
