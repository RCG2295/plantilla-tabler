<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Historial de turnos</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Registro de aperturas y cierres de caja</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <!-- Filtros -->
        <div class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label class="form-label mb-1 fw-semibold" style="font-size:0.82rem;">Desde</label>
                <input type="text" id="filtro_desde" class="form-control form-control-sm"
                    placeholder="dd/mm/aaaa" style="width:135px;">
            </div>
            <div class="col-auto">
                <label class="form-label mb-1 fw-semibold" style="font-size:0.82rem;">Hasta</label>
                <input type="text" id="filtro_hasta" class="form-control form-control-sm"
                    placeholder="dd/mm/aaaa" style="width:135px;">
            </div>
            <div class="col-auto">
                <button id="btn_buscar" class="btn btn-sm btn-primary"
                    style="background-color:var(--color-primario);border-color:var(--color-primario);">
                    <i class="ti ti-search me-1"></i>Buscar
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tabla_historial_turnos" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Sucursal</th>
                        <th>Fondo MXN</th>
                        <th>Fondo USD</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
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
var APP_URL_HIST = '<?= $app_url ?>';
</script>
