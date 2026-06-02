<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Bitácora de movimientos</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Historial global de altas y bajas de inventario</div>
        </div>
        <?php if (puedo('inventario/productos', 'editar')): ?>
        <div class="col-auto">
            <button id="btn_nuevo_movimiento" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-transfer me-1"></i>Registrar movimiento
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold mb-1" style="font-size:0.82rem;">Producto</label>
                <select id="filtro_producto" class="form-select form-select-sm">
                    <option value="">Todos los productos</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold mb-1" style="font-size:0.82rem;">Tipo</label>
                <select id="filtro_tipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="entrada">Entrada</option>
                    <option value="salida">Salida</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold mb-1" style="font-size:0.82rem;">Desde</label>
                <input type="text" id="filtro_fecha_desde" class="form-control form-control-sm flatpickr-input"
                    placeholder="Fecha desde">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold mb-1" style="font-size:0.82rem;">Hasta</label>
                <input type="text" id="filtro_fecha_hasta" class="form-control form-control-sm flatpickr-input"
                    placeholder="Fecha hasta">
            </div>
            <div class="col-md-2">
                <button id="btn_filtrar" class="btn btn-outline-primary btn-sm w-100">
                    <i class="ti ti-search me-1"></i>Filtrar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_movimientos" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Stock anterior</th>
                        <th>Stock nuevo</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
var PERMISOS_MOVIMIENTOS = <?= json_encode([
    'ver'     => puedo('inventario/movimientos', 'ver'),
    'editar'  => puedo('inventario/productos', 'editar'),
]) ?>;
</script>

<!-- Modal registrar movimiento -->
<div class="modal fade" id="modal_movimiento" tabindex="-1" aria-labelledby="modal_movimiento_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form_movimiento" autocomplete="off">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_movimiento_titulo">Registrar movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="mov_id_producto">Producto</label>
                            <select id="mov_id_producto" name="id_producto" class="form-select" required>
                                <option value="">— Selecciona un producto —</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info py-2 mb-0" id="mov_info_stock" style="font-size:0.85rem;display:none;">
                                Stock actual: <strong id="mov_stock_actual">—</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="mov_tipo">Tipo</label>
                            <select id="mov_tipo" name="tipo" class="form-select">
                                <option value="entrada">Entrada (alta)</option>
                                <option value="salida">Salida (baja)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="mov_cantidad" id="lbl_mov_cantidad">Cantidad</label>
                            <input type="number" id="mov_cantidad" name="cantidad" class="form-control"
                                min="0.01" step="0.01" required placeholder="0.00">
                        </div>
                        <div class="col-12" id="wrap_mov_toggle" style="display:none;">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="mov_modo" id="mov_modo_cantidad" value="cantidad" checked>
                                <label class="btn btn-outline-secondary" for="mov_modo_cantidad">Por cantidad base</label>
                                <input type="radio" class="btn-check" name="mov_modo" id="mov_modo_pres" value="presentacion">
                                <label class="btn btn-outline-secondary" for="mov_modo_pres">Por presentación</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="mov_id_motivo">Motivo</label>
                            <select id="mov_id_motivo" name="id_motivo" class="form-select">
                                <option value="">— Sin motivo —</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="mov_notas">Notas <small class="text-muted fw-normal">(opcional)</small></label>
                            <textarea id="mov_notas" name="notas" class="form-control" rows="2"
                                placeholder="Observaciones del movimiento"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_movimiento" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
