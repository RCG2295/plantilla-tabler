<?php
$app_url    = TemplateController::getUrlController();
$id_producto = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$id_producto) {
    header('Location: ' . $app_url . '/inventario/productos');
    exit;
}

// Load product server-side for breadcrumb and meta
$model   = new InventarioProductosModel();
$producto = $model->getById($id_producto);

if (!$producto) {
    header('Location: ' . $app_url . '/inventario/productos');
    exit;
}
?>

<!-- Breadcrumb -->
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <ol class="breadcrumb mb-1" style="font-size:0.82rem;">
                <li class="breadcrumb-item"><a href="<?= $app_url ?>/inventario/productos">Productos</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($producto['nombre']) ?></li>
            </ol>
            <h2 class="page-title fw-bold mb-0"><?= htmlspecialchars($producto['nombre']) ?></h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">
                <code><?= htmlspecialchars($producto['codigo']) ?></code>
                <?php if ($producto['categoria']): ?>
                    &nbsp;&bull;&nbsp; <?= htmlspecialchars($producto['categoria']) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="<?= $app_url ?>/inventario/productos" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i>Volver
            </a>
            <?php if (puedo('inventario/productos', 'editar')): ?>
            <button id="btn_editar_producto" class="btn btn-outline-primary" data-id="<?= $producto['id'] ?>">
                <i class="ti ti-pencil me-1"></i>Editar
            </button>
            <button id="btn_nuevo_movimiento" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-transfer me-1"></i>Registrar movimiento
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Columna izquierda: galería + ficha -->
    <div class="col-lg-4">

        <!-- Galería -->
        <div class="card mb-4">
            <div class="card-body p-3">
                <div id="galeria_principal" class="mb-2 text-center" style="min-height:200px;background:#f4f6fa;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                    <span class="text-muted" id="galeria_placeholder"><i class="ti ti-photo" style="font-size:3rem;"></i></span>
                    <img id="foto_principal_img" src="" alt="Foto principal"
                        style="display:none;max-height:260px;max-width:100%;border-radius:6px;object-fit:contain;">
                </div>
                <div id="galeria_thumbs" class="d-flex flex-wrap gap-2 justify-content-center"></div>

                <?php if (puedo('inventario/productos', 'editar')): ?>
                <div class="mt-3 border-top pt-3">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.82rem;">Agregar fotos</label>
                    <input type="file" id="input_fotos_extra" class="form-control form-control-sm"
                        accept="image/jpeg,image/png,image/webp" multiple>
                    <button id="btn_subir_fotos" class="btn btn-sm btn-outline-primary mt-2 w-100">
                        <i class="ti ti-upload me-1"></i>Subir fotos
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ficha de precios -->
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">Precios</h4></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-muted" style="font-size:0.78rem;">Precio de compra</div>
                        <div class="fw-bold fs-5" id="ficha_precio_costo">—</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted" style="font-size:0.78rem;">Precio venta (presentación)</div>
                        <div class="fw-bold fs-5" style="color:var(--color-primario);" id="ficha_precio_venta">—</div>
                    </div>
                    <div class="col-12 mt-1" id="wrap_ficha_precio_unidad" style="display:none;">
                        <div class="text-muted" style="font-size:0.78rem;">Precio venta (unidad base)</div>
                        <div class="fw-semibold" style="color:var(--color-primario);" id="ficha_precio_venta_unidad">—</div>
                    </div>
                    <div class="col-12 mt-1">
                        <div class="text-muted" style="font-size:0.78rem;">Margen (presentación)</div>
                        <div class="fw-semibold" id="ficha_margen">—</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Columna derecha: stock + descripción + historial -->
    <div class="col-lg-8">

        <!-- Indicador de stock -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="card text-center" id="card_stock">
                    <div class="card-body py-3">
                        <div class="text-muted mb-1" style="font-size:0.78rem;">STOCK ACTUAL</div>
                        <div class="fw-bold" style="font-size:2rem;" id="ficha_stock_actual">—</div>
                        <div class="text-muted" style="font-size:0.78rem;" id="ficha_unidad">—</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card text-center">
                    <div class="card-body py-3">
                        <div class="text-muted mb-1" style="font-size:0.78rem;">STOCK MÍNIMO</div>
                        <div class="fw-bold fs-4" id="ficha_stock_minimo">—</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card text-center">
                    <div class="card-body py-3">
                        <div class="text-muted mb-1" style="font-size:0.78rem;">STOCK MÁXIMO</div>
                        <div class="fw-bold fs-4" id="ficha_stock_maximo">—</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Descripción -->
        <div class="card mb-4" id="card_descripcion" style="display:none!important;">
            <div class="card-header"><h4 class="card-title mb-0">Descripción</h4></div>
            <div class="card-body" id="ficha_descripcion" style="white-space:pre-line;"></div>
        </div>

        <!-- Historial de movimientos -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Historial de movimientos</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tabla_historial" class="table table-hover align-middle mb-0" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Fecha</th>
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

    </div>
</div>

<script>
var PRODUCTO_ID      = <?= $id_producto ?>;
var PERMISOS_PRODUCTOS = <?= json_encode([
    'crear'    => puedo('inventario/productos', 'crear'),
    'editar'   => puedo('inventario/productos', 'editar'),
    'eliminar' => puedo('inventario/productos', 'eliminar'),
]) ?>;
</script>

<!-- Modal editar producto (reutiliza la misma estructura) -->
<div class="modal fade" id="modal_producto" tabindex="-1" aria-labelledby="modal_producto_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="form_producto" autocomplete="off" enctype="multipart/form-data">
                <input type="hidden" id="producto_id" name="id" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_producto_titulo">Editar producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="producto_codigo">Código</label>
                            <input type="text" id="producto_codigo" name="codigo" class="form-control" required maxlength="100">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold" for="producto_nombre">Nombre</label>
                            <input type="text" id="producto_nombre" name="nombre" class="form-control" required maxlength="200">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="producto_descripcion">Descripción</label>
                            <textarea id="producto_descripcion" name="descripcion" class="form-control" rows="2"></textarea>
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
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="producto_stock_minimo" id="lbl_stock_minimo">Stock mínimo</label>
                            <input type="number" id="producto_stock_minimo" name="stock_minimo" class="form-control" value="0" min="0" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="producto_stock_maximo" id="lbl_stock_maximo">Stock máximo</label>
                            <input type="number" id="producto_stock_maximo" name="stock_maximo" class="form-control" value="0" min="0" step="0.01">
                        </div>
                        <div class="col-12"><hr class="my-1"><div class="fw-semibold text-muted" style="font-size:0.82rem;">PRECIOS</div></div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="producto_precio_costo">Precio de compra (Costo)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="producto_precio_costo" name="precio_costo" class="form-control" value="0.00" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="producto_precio_venta">Precio de venta <small class="text-muted fw-normal">(por presentación)</small></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="producto_precio_venta" name="precio_venta" class="form-control" value="0.00" min="0" step="0.01">
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
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_producto" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal registrar movimiento -->
<div class="modal fade" id="modal_movimiento" tabindex="-1" aria-labelledby="modal_movimiento_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form_movimiento" autocomplete="off">
                <input type="hidden" name="id_producto" value="<?= $id_producto ?>">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_movimiento_titulo">Registrar movimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="alert alert-info py-2 mb-0" style="font-size:0.85rem;">
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
