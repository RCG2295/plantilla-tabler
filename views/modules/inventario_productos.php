<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Productos</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Gestión del catálogo de productos del inventario</div>
        </div>
        <?php if (puedo('inventario/productos', 'crear')): ?>
        <div class="col-auto">
            <button id="btn_nuevo_producto" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-plus me-1"></i>Nuevo producto
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Leyenda stock -->
<div class="d-flex gap-3 mb-3 flex-wrap" style="font-size:0.82rem;">
    <span><span class="badge" style="background:var(--color-exito);color:#fff;">Verde</span> Stock normal</span>
    <span><span class="badge bg-warning text-dark">Amarillo</span> Stock bajo (por debajo del mínimo)</span>
    <span><span class="badge bg-danger text-white">Rojo</span> Sin stock</span>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_productos" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Stock actual</th>
                        <th>Mín / Máx</th>
                        <th>P. Costo</th>
                        <th>P. Venta</th>
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
var PERMISOS_PRODUCTOS = <?= json_encode([
    'crear'    => puedo('inventario/productos', 'crear'),
    'editar'   => puedo('inventario/productos', 'editar'),
    'eliminar' => puedo('inventario/productos', 'eliminar'),
]) ?>;
</script>

<!-- Modal agregar/editar producto -->
<div class="modal fade" id="modal_producto" tabindex="-1" aria-labelledby="modal_producto_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="form_producto" autocomplete="off" enctype="multipart/form-data">
                <input type="hidden" id="producto_id" name="id" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_producto_titulo">Nuevo producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="producto_codigo">Código</label>
                            <input type="text" id="producto_codigo" name="codigo" class="form-control"
                                placeholder="Ej: PROD-001" required maxlength="100">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold" for="producto_nombre">Nombre</label>
                            <input type="text" id="producto_nombre" name="nombre" class="form-control"
                                placeholder="Nombre del producto" required maxlength="200">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="producto_descripcion">Descripción <small class="text-muted fw-normal">(opcional)</small></label>
                            <textarea id="producto_descripcion" name="descripcion" class="form-control" rows="2"
                                placeholder="Descripción del producto"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="producto_cat_padre">Categoría</label>
                            <select id="producto_cat_padre" class="form-select">
                                <option value="">— Sin categoría —</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="producto_id_categoria">Subcategoría</label>
                            <select id="producto_id_categoria" class="form-select">
                                <option value="">— Sin subcategoría —</option>
                            </select>
                        </div>
                        <input type="hidden" id="producto_id_categoria_final" name="id_categoria" value="">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="producto_id_unidad_medida">Unidad de medida</label>
                            <select id="producto_id_unidad_medida" name="id_unidad_medida" class="form-select">
                                <option value="">— Sin unidad —</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="wrap_fracciona" style="display:none;">
                            <label class="form-label fw-semibold">Producto fraccionado</label>
                            <div class="form-check mt-1">
                                <input type="checkbox" id="producto_se_fracciona" name="se_fracciona"
                                    class="form-check-input" value="1">
                                <label class="form-check-label" for="producto_se_fracciona">
                                    Se puede fraccionar
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="producto_cantidad_presentacion">Unidades base por presentación</label>
                            <input type="number" id="producto_cantidad_presentacion" name="cantidad_presentacion"
                                class="form-control" value="1" min="0.01" step="0.01" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="producto_estado">Estado</label>
                            <select id="producto_estado" name="estado" class="form-select">
                                <option value="0">Activo</option>
                                <option value="1">Inactivo</option>
                            </select>
                        </div>

                        <div class="col-12"><hr class="my-1"><div class="fw-semibold text-muted" style="font-size:0.82rem;">STOCK</div></div>

                        <div class="col-md-4" id="wrap_stock_inicial">
                            <label class="form-label fw-semibold" for="producto_stock_actual" id="lbl_stock_inicial">Stock inicial</label>
                            <input type="number" id="producto_stock_actual" name="stock_actual" class="form-control"
                                value="0" min="0" step="0.01">
                            <div id="hint_stock_presentacion" class="form-text fw-semibold" style="display:none;"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="producto_stock_minimo" id="lbl_stock_minimo">Stock mínimo</label>
                            <input type="number" id="producto_stock_minimo" name="stock_minimo" class="form-control"
                                value="0" min="0" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="producto_stock_maximo" id="lbl_stock_maximo">Stock máximo</label>
                            <input type="number" id="producto_stock_maximo" name="stock_maximo" class="form-control"
                                value="0" min="0" step="0.01">
                        </div>

                        <div class="col-12"><hr class="my-1"><div class="fw-semibold text-muted" style="font-size:0.82rem;">PRECIOS</div></div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="producto_precio_costo">Precio de compra <small class="text-muted fw-normal">(costo)</small></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="producto_precio_costo" name="precio_costo" class="form-control"
                                    value="0.00" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="producto_precio_venta">Precio de venta <small class="text-muted fw-normal">(por presentación)</small></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="producto_precio_venta" name="precio_venta" class="form-control"
                                    value="0.00" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6" id="wrap_precio_venta_unidad" style="display:none;">
                            <label class="form-label fw-semibold" for="producto_precio_venta_unidad">Precio de venta <small class="text-muted fw-normal">(por unidad base)</small></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="producto_precio_venta_unidad" name="precio_venta_unidad"
                                    class="form-control" min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>

                        <div class="col-12"><hr class="my-1"><div class="fw-semibold text-muted" style="font-size:0.82rem;">FOTOS <small class="fw-normal">(máx. 2 MB c/u — JPG, PNG, WEBP)</small></div></div>

                        <div class="col-12">
                            <input type="file" id="producto_fotos" name="fotos[]" class="form-control"
                                accept="image/jpeg,image/png,image/webp" multiple>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_producto" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
