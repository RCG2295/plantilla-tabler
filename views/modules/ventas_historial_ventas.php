<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Historial de ventas</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Consulta de ventas por rango de fecha</div>
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
            <div class="col-auto ms-auto" id="resumen_ventas" style="display:none;">
                <span class="text-muted me-3" style="font-size:.85rem;">
                    Ventas activas: <strong id="res_count">0</strong>
                </span>
                <span class="text-muted" style="font-size:.85rem;">
                    Total: <strong id="res_total" style="color:var(--color-primario);">$0.00</strong>
                </span>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="tabs_historial_ventas" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-activas-btn" data-bs-toggle="tab"
                    data-bs-target="#tab_activas" type="button" role="tab">
                    <i class="ti ti-check me-1"></i>Activas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-canceladas-btn" data-bs-toggle="tab"
                    data-bs-target="#tab_canceladas" type="button" role="tab">
                    <i class="ti ti-ban me-1"></i>Canceladas
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <!-- Tab Activas -->
            <div class="tab-pane fade show active" id="tab_activas" role="tabpanel">
                <div class="table-responsive">
                    <table id="tabla_ventas_activas" class="table table-hover align-middle" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Cajero</th>
                                <th>Sucursal</th>
                                <th>Turno</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Ef. MXN</th>
                                <th class="text-end">Ef. USD</th>
                                <th class="text-end">Tarjeta</th>
                                <th class="text-end">Transferencia</th>
                                <th>Fecha</th>
                                <th class="text-center">Ticket</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Canceladas -->
            <div class="tab-pane fade" id="tab_canceladas" role="tabpanel">
                <div class="table-responsive">
                    <table id="tabla_ventas_canceladas" class="table table-hover align-middle" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Cajero</th>
                                <th>Sucursal</th>
                                <th>Turno</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Ef. MXN</th>
                                <th class="text-end">Ef. USD</th>
                                <th class="text-end">Tarjeta</th>
                                <th class="text-end">Transferencia</th>
                                <th>Fecha</th>
                                <th class="text-center">Ticket</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
var APP_URL_HV = '<?= $app_url ?>';
</script>
