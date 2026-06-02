<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Compras</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Registro y seguimiento de compras a proveedores</div>
        </div>
        <?php if (puedo('compras/compras', 'crear')): ?>
        <div class="col-auto">
            <button id="btn_nueva_compra" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-plus me-1"></i>Nueva compra
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <ul class="nav nav-tabs mb-3" id="tabs_compras" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab_activas_btn" data-bs-toggle="tab"
                    data-bs-target="#tab_activas" type="button" role="tab">
                    <i class="ti ti-check me-1"></i>Activas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab_canceladas_btn" data-bs-toggle="tab"
                    data-bs-target="#tab_canceladas" type="button" role="tab">
                    <i class="ti ti-ban me-1"></i>Canceladas
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab_activas" role="tabpanel">
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-auto">
                        <label class="form-label mb-1 fw-semibold" style="font-size:0.82rem;">Desde</label>
                        <input type="text" id="filtro_activas_desde" class="form-control form-control-sm" placeholder="dd/mm/aaaa" style="width:130px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 fw-semibold" style="font-size:0.82rem;">Hasta</label>
                        <input type="text" id="filtro_activas_hasta" class="form-control form-control-sm" placeholder="dd/mm/aaaa" style="width:130px;">
                    </div>
                    <div class="col-auto">
                        <button id="btn_buscar_activas" class="btn btn-sm btn-primary"
                            style="background-color:var(--color-primario);border-color:var(--color-primario);">
                            <i class="ti ti-search me-1"></i>Buscar
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tabla_compras_activas" class="table table-hover align-middle" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end">IVA</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Productos</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="tab_canceladas" role="tabpanel">
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-auto">
                        <label class="form-label mb-1 fw-semibold" style="font-size:0.82rem;">Desde</label>
                        <input type="text" id="filtro_canceladas_desde" class="form-control form-control-sm" placeholder="dd/mm/aaaa" style="width:130px;">
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 fw-semibold" style="font-size:0.82rem;">Hasta</label>
                        <input type="text" id="filtro_canceladas_hasta" class="form-control form-control-sm" placeholder="dd/mm/aaaa" style="width:130px;">
                    </div>
                    <div class="col-auto">
                        <button id="btn_buscar_canceladas" class="btn btn-sm btn-primary"
                            style="background-color:var(--color-primario);border-color:var(--color-primario);">
                            <i class="ti ti-search me-1"></i>Buscar
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tabla_compras_canceladas" class="table table-hover align-middle" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end">IVA</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Productos</th>
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
var PERMISOS_COMPRAS = <?= json_encode([
    'crear'    => puedo('compras/compras', 'crear'),
    'eliminar' => puedo('compras/compras', 'eliminar'),
]) ?>;
var APP_URL_COMPRAS = '<?= $app_url ?>';
</script>

<!-- Modal nueva compra -->
<div class="modal fade" id="modal_compra" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <form id="form_compra" autocomplete="off">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-semibold mb-0">Nueva compra</h5>
                        <small class="text-muted" id="span_folio_compra"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" style="overflow-y:auto; max-height:calc(80vh - 130px);">

                    <!-- Encabezado -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" for="compra_id_proveedor">
                                Proveedor <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <select id="compra_id_proveedor" name="id_proveedor" class="form-select">
                                <option value="">Sin proveedor</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" for="compra_fecha">Fecha de compra</label>
                            <input type="text" id="compra_fecha" name="fecha_compra" class="form-control"
                                placeholder="Seleccionar fecha" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="compra_notas">
                                Notas <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <input type="text" id="compra_notas" name="notas" class="form-control"
                                placeholder="Observaciones de la compra" maxlength="500">
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-semibold text-muted" style="font-size:0.82rem;">PRODUCTOS</div>
                        <button type="button" id="btn_agregar_producto" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-plus me-1"></i>Agregar producto
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0" style="min-width:750px;">
                            <thead class="table-light">
                                <tr>
                                    <th width="32">#</th>
                                    <th>Producto</th>
                                    <th width="145">Cantidad</th>
                                    <th width="130">Precio unit.</th>
                                    <th width="90">IVA %</th>
                                    <th width="110" class="text-end">Total línea</th>
                                    <th width="36"></th>
                                </tr>
                            </thead>
                            <tbody id="tbody_productos_compra"></tbody>
                        </table>
                        <div id="msg_sin_productos" class="text-center text-muted py-4" style="font-size:0.9rem;">
                            <i class="ti ti-package-off me-1"></i>Sin productos. Haz clic en "Agregar producto" para comenzar.
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="row justify-content-end mt-3">
                        <div class="col-md-4 col-lg-3">
                            <table class="table table-sm table-borderless mb-0" style="font-size:0.92rem;">
                                <tr>
                                    <td class="text-muted">Subtotal</td>
                                    <td class="text-end fw-semibold" id="resumen_subtotal">$0.00</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">IVA</td>
                                    <td class="text-end fw-semibold" id="resumen_iva">$0.00</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="fw-bold">Total</td>
                                    <td class="text-end fw-bold fs-5" id="resumen_total">$0.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <label class="form-label fw-semibold" for="compra_archivo">
                            Archivo adjunto <small class="text-muted fw-normal">(factura, ticket — PDF, JPG, PNG, WEBP · máx. 5 MB)</small>
                        </label>
                        <input type="file" id="compra_archivo" name="archivo" class="form-control form-control-sm"
                            accept=".pdf,.jpg,.jpeg,.png,.webp">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_compra" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        <i class="ti ti-check me-1"></i>Guardar compra
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
