<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Mi caja</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Turno activo</div>
        </div>
        <div class="col-auto d-flex gap-2 d-none" id="btns_turno_activo">
            <?php if (puedo('ventas/mi_caja', 'editar')): ?>
            <button id="btn_retiro" class="btn btn-outline-danger btn-sm">
                <i class="ti ti-arrow-up-circle me-1"></i>Retiro
            </button>
            <button id="btn_ingreso" class="btn btn-outline-success btn-sm">
                <i class="ti ti-arrow-down-circle me-1"></i>Ingreso
            </button>
            <?php endif; ?>
            <button id="btn_abrir_cerrar_turno" class="btn btn-danger btn-sm">
                <i class="ti ti-lock me-1"></i>Cerrar turno
            </button>
        </div>
    </div>
</div>

<!-- Estado: cargando -->
<div id="bloque_cargando" class="text-center py-5">
    <div class="spinner-border" style="color:var(--color-primario);"></div>
    <div class="text-muted mt-2">Verificando turno activo...</div>
</div>

<!-- Estado: sin turno activo -->
<div id="bloque_sin_turno" style="display:none;">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ti ti-lock-open me-2" style="color:var(--color-primario);"></i>Iniciar turno
                    </h4>
                </div>
                <div class="card-body">
                    <form id="form_iniciar_turno" autocomplete="off">

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fondo inicial en pesos (MXN)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="fondo_pesos" name="fondo_pesos"
                                        class="form-control" placeholder="0.00" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fondo inicial en dólares (USD)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" id="fondo_dolares" name="fondo_dolares"
                                        class="form-control" placeholder="0.00" min="0" step="0.01">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <p class="fw-semibold mb-3">Denominaciones en pesos <span class="fw-normal text-muted">(opcional)</span></p>
                        <div id="den_pesos_apertura" class="row g-2 mb-4"></div>

                        <hr>
                        <p class="fw-semibold mb-3">Denominaciones en dólares <span class="fw-normal text-muted">(opcional)</span></p>
                        <div id="den_dolares_apertura" class="row g-2 mb-4"></div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-lg"
                                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                                <i class="ti ti-lock-open me-2"></i>Iniciar turno
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estado: turno activo -->
<div id="bloque_con_turno" style="display:none;">

    <!-- Info del turno -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-3">
            <div class="card">
                <div class="card-body text-center py-3">
                    <div class="text-muted mb-1" style="font-size:.78rem;">Fondo inicial MXN</div>
                    <div id="info_fondo_pesos" class="fw-bold" style="font-size:1.2rem;color:var(--color-primario);">$0.00</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card">
                <div class="card-body text-center py-3">
                    <div class="text-muted mb-1" style="font-size:.78rem;">Fondo inicial USD</div>
                    <div id="info_fondo_dolares" class="fw-bold" style="font-size:1.2rem;color:var(--color-primario);">$0.00</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card">
                <div class="card-body text-center py-3">
                    <div class="text-muted mb-1" style="font-size:.78rem;">Apertura</div>
                    <div id="info_fecha_alta" class="fw-bold" style="font-size:.88rem;">—</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card">
                <div class="card-body text-center py-3">
                    <div class="text-muted mb-1" style="font-size:.78rem;">Estado</div>
                    <span class="badge bg-success-lt text-success fw-semibold">Activo</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen ventas -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <div class="text-muted mb-1" style="font-size:.78rem;">Ventas</div>
                    <div id="res_num_ventas" class="fw-bold" style="font-size:1.4rem;color:var(--color-primario);">0</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <div class="text-muted mb-1" style="font-size:.78rem;">Total ventas</div>
                    <div id="res_total_ventas" class="fw-bold" style="font-size:1.1rem;color:var(--color-primario);">$0.00</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <div class="text-muted mb-1" style="font-size:.78rem;">Efectivo MXN</div>
                    <div id="res_efectivo_pesos" class="fw-bold" style="font-size:1.1rem;">$0.00</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <div class="text-muted mb-1" style="font-size:.78rem;">Efectivo USD</div>
                    <div id="res_efectivo_dolares" class="fw-bold" style="font-size:1.1rem;">$0.00</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <div class="text-muted mb-1" style="font-size:.78rem;">Tarjeta</div>
                    <div id="res_tarjeta" class="fw-bold" style="font-size:1.1rem;">$0.00</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-2">
            <div class="card text-center h-100">
                <div class="card-body py-3">
                    <div class="text-muted mb-1" style="font-size:.78rem;">Transferencia</div>
                    <div id="res_transferencia" class="fw-bold" style="font-size:1.1rem;">$0.00</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Efectivo esperado -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-primary" style="border-color:var(--color-primario)!important;">
                <div class="card-body text-center py-4">
                    <div class="text-muted mb-1" style="font-size:.85rem;">Efectivo esperado en caja (MXN)</div>
                    <div id="res_esperado_pesos" class="fw-bold" style="font-size:1.8rem;color:var(--color-primario);">$0.00</div>
                    <div class="text-muted mt-1" style="font-size:.78rem;">Fondo + ventas + ingresos − retiros</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-primary" style="border-color:var(--color-primario)!important;">
                <div class="card-body text-center py-4">
                    <div class="text-muted mb-1" style="font-size:.85rem;">Efectivo esperado en caja (USD)</div>
                    <div id="res_esperado_dolares" class="fw-bold" style="font-size:1.8rem;color:var(--color-primario);">$0.00</div>
                    <div class="text-muted mt-1" style="font-size:.78rem;">Fondo + ventas + ingresos − retiros</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="tabs_micaja" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab_ventas_mc" type="button">
                        <i class="ti ti-shopping-bag me-1"></i>Ventas
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab_movimientos_mc" type="button">
                        <i class="ti ti-arrows-exchange me-1"></i>Movimientos
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab_ventas_mc">
                    <div class="table-responsive">
                        <table id="tabla_ventas_mc" class="table table-hover align-middle" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Total</th>
                                    <th>Formas de pago</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab_movimientos_mc">
                    <div class="table-responsive">
                        <table id="tabla_movimientos_mc" class="table table-hover align-middle" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Moneda</th>
                                    <th>Monto</th>
                                    <th>Descripción</th>
                                    <th>Registrado por</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
var APP_URL_MC = '<?= $app_url ?>';
var PERMISOS_MC = <?= json_encode([
    'editar' => puedo('ventas/mi_caja', 'editar'),
]) ?>;
</script>

<!-- Modal: cerrar turno -->
<div class="modal fade" id="modal_cerrar_turno" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="form_cerrar_turno" autocomplete="off">
                <input type="hidden" id="cierre_id_turno" name="id_turno">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold text-danger">
                        <i class="ti ti-lock me-1"></i>Cerrar turno
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Efectivo declarado en pesos (MXN)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="declarado_pesos" name="declarado_pesos"
                                    class="form-control" placeholder="0.00" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Efectivo declarado en dólares (USD)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="declarado_dolares" name="declarado_dolares"
                                    class="form-control" placeholder="0.00" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <p class="fw-semibold mb-3">Denominaciones al cierre en pesos <span class="fw-normal text-muted">(opcional)</span></p>
                    <div id="den_pesos_cierre" class="row g-2 mb-4"></div>

                    <hr>
                    <p class="fw-semibold mb-3">Denominaciones al cierre en dólares <span class="fw-normal text-muted">(opcional)</span></p>
                    <div id="den_dolares_cierre" class="row g-2 mb-4"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-lock me-1"></i>Confirmar cierre
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: movimiento (retiro / ingreso) -->
<div class="modal fade" id="modal_movimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form id="form_movimiento" autocomplete="off">
                <input type="hidden" id="mov_id_turno" name="id_turno">
                <input type="hidden" id="mov_tipo" name="tipo">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_movimiento_titulo">Movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Moneda</label>
                        <select id="mov_moneda" name="moneda" class="form-select" required>
                            <option value="pesos">Pesos (MXN)</option>
                            <option value="dolares">Dólares (USD)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Monto</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" id="mov_monto" name="monto" class="form-control"
                                placeholder="0.00" min="0.01" step="0.01" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Descripción <span class="text-muted fw-normal">(opcional)</span></label>
                        <textarea id="mov_descripcion" name="descripcion" class="form-control" rows="2"
                            placeholder="Motivo del movimiento"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_mov" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        <i class="ti ti-check me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
