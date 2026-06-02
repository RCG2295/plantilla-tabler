<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Egresos</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Registro y seguimiento de egresos</div>
        </div>
        <?php if (puedo('egresos/egresos', 'crear')): ?>
        <div class="col-auto">
            <button id="btn_nuevo_egreso" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-plus me-1"></i>Nuevo egreso
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <ul class="nav nav-tabs mb-3" id="tabs_egresos" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab_activos_btn" data-bs-toggle="tab"
                    data-bs-target="#tab_activos" type="button" role="tab">
                    <i class="ti ti-check me-1"></i>Activos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab_cancelados_btn" data-bs-toggle="tab"
                    data-bs-target="#tab_cancelados" type="button" role="tab">
                    <i class="ti ti-ban me-1"></i>Cancelados
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab_activos" role="tabpanel">
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-auto">
                        <label class="form-label mb-1 fw-semibold" style="font-size:0.82rem;">Desde</label>
                        <input type="text" id="filtro_activos_desde" class="form-control form-control-sm" placeholder="dd/mm/aaaa" style="width:130px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 fw-semibold" style="font-size:0.82rem;">Hasta</label>
                        <input type="text" id="filtro_activos_hasta" class="form-control form-control-sm" placeholder="dd/mm/aaaa" style="width:130px;">
                    </div>
                    <div class="col-auto">
                        <button id="btn_buscar_activos" class="btn btn-sm btn-primary"
                            style="background-color:var(--color-primario);border-color:var(--color-primario);">
                            <i class="ti ti-search me-1"></i>Buscar
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tabla_egresos_activos" class="table table-hover align-middle" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Subcategoría</th>
                                <th>Concepto</th>
                                <th>Fecha</th>
                                <th class="text-end">Monto</th>
                                <th>Método</th>
                                <th>Registrado por</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="tab_cancelados" role="tabpanel">
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-auto">
                        <label class="form-label mb-1 fw-semibold" style="font-size:0.82rem;">Desde</label>
                        <input type="text" id="filtro_cancelados_desde" class="form-control form-control-sm" placeholder="dd/mm/aaaa" style="width:130px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 fw-semibold" style="font-size:0.82rem;">Hasta</label>
                        <input type="text" id="filtro_cancelados_hasta" class="form-control form-control-sm" placeholder="dd/mm/aaaa" style="width:130px;">
                    </div>
                    <div class="col-auto">
                        <button id="btn_buscar_cancelados" class="btn btn-sm btn-primary"
                            style="background-color:var(--color-primario);border-color:var(--color-primario);">
                            <i class="ti ti-search me-1"></i>Buscar
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tabla_egresos_cancelados" class="table table-hover align-middle" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Subcategoría</th>
                                <th>Concepto</th>
                                <th>Fecha</th>
                                <th class="text-end">Monto</th>
                                <th>Método</th>
                                <th>Registrado por</th>
                                <th class="text-center">Acciones</th>
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
var PERMISOS_EGRESOS = <?= json_encode([
    'crear'    => puedo('egresos/egresos', 'crear'),
    'eliminar' => puedo('egresos/egresos', 'eliminar'),
]) ?>;
var APP_URL_EGRESOS = '<?= $app_url ?>';
</script>

<!-- Modal nuevo egreso -->
<div class="modal fade" id="modal_egreso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="form_egreso" autocomplete="off">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold">Nuevo egreso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="egreso_id_categoria">Categoría</label>
                            <select id="egreso_id_categoria" name="id_categoria" class="form-select" required>
                                <option value="">Seleccionar categoría</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="egreso_id_subcategoria">
                                Subcategoría <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <select id="egreso_id_subcategoria" name="id_subcategoria" class="form-select">
                                <option value="">Sin subcategoría</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="egreso_concepto">Concepto</label>
                            <input type="text" id="egreso_concepto" name="concepto" class="form-control"
                                placeholder="Descripción del egreso" required maxlength="300">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="egreso_fecha">Fecha</label>
                            <input type="text" id="egreso_fecha" name="fecha_egreso" class="form-control"
                                placeholder="Seleccionar fecha" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="egreso_monto">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="egreso_monto" name="monto" class="form-control"
                                    placeholder="0.00" min="0.01" step="any" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="egreso_metodo_pago">Método de pago</label>
                            <select id="egreso_metodo_pago" name="metodo_pago" class="form-select">
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                        </div>
                        <div class="col-12" id="grupo_referencia" style="display:none;">
                            <label class="form-label fw-semibold" for="egreso_referencia">
                                Número de referencia
                            </label>
                            <input type="text" id="egreso_referencia" name="referencia" class="form-control"
                                placeholder="Número de referencia o folio" maxlength="150">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="egreso_notas">
                                Notas <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <textarea id="egreso_notas" name="notas" class="form-control" rows="2"
                                placeholder="Observaciones del egreso"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="egreso_archivo">
                                Archivo adjunto <small class="text-muted fw-normal">(comprobante — PDF, JPG, PNG, WEBP · máx. 5 MB)</small>
                            </label>
                            <input type="file" id="egreso_archivo" name="archivo" class="form-control form-control-sm"
                                accept=".pdf,.jpg,.jpeg,.png,.webp">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_egreso" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        <i class="ti ti-check me-1"></i>Guardar egreso
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
