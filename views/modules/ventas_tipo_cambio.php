<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Tipo de cambio</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Historial y valor vigente USD / MXN</div>
        </div>
        <?php if (puedo('ventas/tipo_cambio', 'crear')): ?>
        <div class="col-auto">
            <button id="btn_nuevo_tc" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-plus me-1"></i>Registrar tipo de cambio
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card text-center" id="card_tc_vigente">
            <div class="card-body py-4">
                <div class="text-muted mb-1" style="font-size:.85rem;">Tipo de cambio vigente</div>
                <div id="tc_valor_vigente" class="fw-bold" style="font-size:2.2rem;color:var(--color-primario);">—</div>
                <div id="tc_fecha_vigente" class="text-muted mt-1" style="font-size:.8rem;"></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_tipo_cambio" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Valor (MXN por USD)</th>
                        <th>Registrado por</th>
                        <th>Fecha de registro</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
var PERMISOS_TC = <?= json_encode([
    'crear' => puedo('ventas/tipo_cambio', 'crear'),
]) ?>;
var APP_URL_TC = '<?= $app_url ?>';
</script>

<!-- Modal nuevo tipo de cambio -->
<div class="modal fade" id="modal_tc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form id="form_tc" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold">Registrar tipo de cambio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold" for="tc_valor">Valor (MXN por 1 USD)</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="tc_valor" name="valor" class="form-control"
                            placeholder="Ej. 17.50" min="0.01" step="0.01" required>
                        <span class="input-group-text">MXN</span>
                    </div>
                    <div class="form-text">Este valor se usará para convertir pagos en dólares a pesos.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        <i class="ti ti-check me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
