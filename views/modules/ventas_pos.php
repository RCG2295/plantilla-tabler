<?php $app_url = TemplateController::getUrlController(); ?>

<style>
* { box-sizing: border-box; }
html, body { height: 100%; overflow: hidden; }
#pos-root { display: flex; flex-direction: column; height: 100vh; background: #f1f5f9; }

/* Header */
#pos-header { background: var(--color-sidebar-bg); color: #fff; padding: .65rem 1rem; display: flex; align-items: center; gap: 1rem; flex-shrink: 0; }
#pos-header .pos-logo { font-size: 1.1rem; font-weight: 700; color: var(--color-primario); }
#pos-header .pos-info { flex: 1; font-size: .82rem; color: #aac; }
#pos-header .pos-info span { color: #e0eaf0; font-weight: 600; }

/* Body */
#pos-body { display: flex; flex: 1; overflow: hidden; gap: 0; }

/* Products panel */
#pos-products { flex: 0 0 62%; display: flex; flex-direction: column; overflow: hidden; border-right: 1px solid #dde2ea; background: #fff; }
#pos-search-bar { padding: .7rem 1rem; border-bottom: 1px solid #e8eaf0; display: flex; gap: .5rem; align-items: center; flex-shrink: 0; }
#pos-search-bar input { flex: 1; border: 1px solid #dde2ea; border-radius: .4rem; padding: .4rem .75rem; font-size: .9rem; outline: none; }
#pos-search-bar input:focus { border-color: var(--color-primario); }
#pos-cats { padding: .5rem 1rem; display: flex; gap: .4rem; flex-wrap: nowrap; overflow-x: auto; flex-shrink: 0; border-bottom: 1px solid #e8eaf0; }
#pos-cats::-webkit-scrollbar { height: 4px; }
#pos-cats::-webkit-scrollbar-thumb { background: #c0cdd6; border-radius: 4px; }
.pos-cat-btn { white-space: nowrap; padding: .28rem .75rem; border-radius: 999px; border: 1px solid #dde2ea; background: #f8fafc; font-size: .82rem; cursor: pointer; transition: all .15s; }
.pos-cat-btn:hover, .pos-cat-btn.active { background: var(--color-primario); border-color: var(--color-primario); color: #fff; }
#pos-grid { flex: 1; overflow-y: auto; padding: .75rem 1rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: .6rem; align-content: start; }
#pos-pagination { flex-shrink: 0; display: flex; align-items: center; justify-content: center; gap: .5rem; padding: .45rem .75rem; border-top: 1px solid #e8eaf0; background: #fff; }
#pos-pagination .pag-btn { width: 30px; height: 30px; border: 1px solid #dde2ea; border-radius: .35rem; background: #f8fafc; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .85rem; transition: all .12s; }
#pos-pagination .pag-btn:hover:not(:disabled) { background: var(--color-primario); border-color: var(--color-primario); color: #fff; }
#pos-pagination .pag-btn:disabled { opacity: .35; cursor: not-allowed; }
#pos-pagination .pag-info { font-size: .78rem; color: #555; padding: 0 .25rem; }
.pos-product-card { border: 1px solid #e2e8f0; border-radius: .5rem; padding: .6rem; cursor: pointer; transition: box-shadow .15s, border-color .15s; background: #fff; text-align: center; display: flex; flex-direction: column; }
.pos-product-card:hover { border-color: var(--color-primario); box-shadow: 0 2px 10px rgba(27,142,163,.15); }
.pos-product-card.sin-stock { opacity: .5; cursor: not-allowed; }
.pos-product-card .pos-prod-img { width: 60px; height: 60px; object-fit: cover; border-radius: .35rem; margin: 0 auto .4rem; display: block; }
.pos-product-card .pos-prod-icon { font-size: 2.2rem; color: #c0cdd6; margin-bottom: .2rem; }
.pos-product-card .pos-prod-name { font-size: .8rem; font-weight: 600; color: #2d3748; line-height: 1.2; margin-bottom: .2rem; }
.pos-product-card .pos-prod-price { font-size: .88rem; font-weight: 700; color: var(--color-primario); }
.pos-product-card .pos-prod-stock { font-size: .72rem; color: #888; }
.pos-no-results { grid-column: 1/-1; text-align: center; color: #aaa; padding: 2rem; font-size: .9rem; }

/* Cart panel */
#pos-cart { flex: 0 0 38%; display: flex; flex-direction: column; overflow: hidden; background: #f8fafc; }
#pos-cart-header { padding: .7rem 1rem; border-bottom: 1px solid #dde2ea; font-weight: 700; font-size: .95rem; color: #2d3748; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
#pos-cart-items { flex: 1; overflow-y: auto; padding: .5rem .75rem; }
.pos-cart-empty { text-align: center; color: #aaa; padding: 2rem 1rem; font-size: .88rem; }
.cart-item { display: flex; align-items: center; gap: .5rem; padding: .4rem .3rem; border-bottom: 1px solid #eef0f3; }
.cart-item-name { flex: 1; font-size: .82rem; font-weight: 600; color: #2d3748; }
.cart-item-tipo { font-size: .72rem; color: #888; }
.cart-item-qty { display: flex; align-items: center; gap: .3rem; }
.cart-qty-btn { width: 24px; height: 24px; border: 1px solid #dde2ea; background: #fff; border-radius: .3rem; font-size: .9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .12s; }
.cart-qty-btn:hover { background: var(--color-primario); color: #fff; border-color: var(--color-primario); }
.cart-item-qty input { width: 44px; text-align: center; border: 1px solid #dde2ea; border-radius: .3rem; padding: .15rem .2rem; font-size: .82rem; }
.cart-item-price { font-size: .88rem; font-weight: 700; color: var(--color-primario); min-width: 56px; text-align: right; }
.cart-item-remove { color: #e74c3c; cursor: pointer; font-size: 1rem; }

#pos-payment { border-top: 2px solid #dde2ea; padding: .65rem .85rem; flex-shrink: 0; background: #fff; }
.pay-label { font-size: .78rem; font-weight: 600; color: #555; margin-bottom: .15rem; }
.pay-input-group { display: flex; align-items: center; gap: .3rem; margin-bottom: .35rem; }
.pay-input-group .pay-prefix { font-size: .78rem; font-weight: 600; color: #888; width: 90px; }
.pay-input-group input { flex: 1; border: 1px solid #dde2ea; border-radius: .35rem; padding: .3rem .5rem; font-size: .88rem; }
.pay-input-group input:focus { outline: none; border-color: var(--color-primario); }
.pay-input-group input:disabled { background: #f5f5f5; color: #aaa; }
#pos-totals { display: flex; flex-direction: column; margin: .4rem 0; gap: .05rem; }
#pos-totals .total-row { display: flex; justify-content: space-between; align-items: center; }
#pos-totals .total-label { font-size: .85rem; color: #555; }
#pos-totals .total-value { font-size: 1.5rem; font-weight: 800; color: #2d3748; }
#pos-totals .total-label-usd { font-size: .75rem; color: #999; }
#pos-totals .total-value-usd { font-size: .82rem; color: #999; font-weight: 600; }
#pos-cambio-row { display: flex; justify-content: space-between; align-items: flex-end; font-size: .82rem; color: #555; margin-bottom: .15rem; }
#pos-cambio-row .cambio-val { font-weight: 700; color: #27ae60; }
#pos-faltante-row { display: none; justify-content: space-between; align-items: flex-end; font-size: .82rem; color: #555; margin-bottom: .15rem; }
#pos-faltante-row .faltante-val { font-weight: 700; color: #e74c3c; }
.cambio-vals { display: flex; flex-direction: column; align-items: flex-end; gap: .02rem; }
.cambio-usd-val { font-size: .72rem; color: #aaa; }
#btn_cobrar { width: 100%; padding: .65rem; font-size: 1rem; font-weight: 700; border: none; border-radius: .45rem; background: var(--color-primario); color: #fff; cursor: pointer; transition: opacity .15s; }
#btn_cobrar:hover { opacity: .9; }
#btn_cobrar:disabled { background: #aaa; cursor: not-allowed; }

</style>

<div id="pos-root">

    <!-- Header -->
    <div id="pos-header">
        <div class="pos-logo"><i class="ti ti-shopping-cart me-1"></i>POS</div>
        <div class="pos-info">
            Sucursal: <span id="pos-sucursal">—</span> &nbsp;|&nbsp;
            Turno: <span id="pos-turno-id">—</span> &nbsp;|&nbsp;
            Usuario: <span id="pos-usuario"><?= htmlspecialchars(($_SESSION['usuario_nombre'] ?? '') . ' ' . ($_SESSION['usuario_apellidos'] ?? '')) ?></span> &nbsp;|&nbsp;
            TC: <span id="pos-tc-val">$—</span> MXN/USD
        </div>
        <a href="<?= $app_url ?>/ventas/mi_caja" class="btn btn-sm btn-outline-light">
            <i class="ti ti-arrow-left me-1"></i>Salir
        </a>
    </div>

    <!-- Body -->
    <div id="pos-body">

        <!-- Products -->
        <div id="pos-products">
            <div id="pos-search-bar">
                <i class="ti ti-search" style="color:#888;"></i>
                <input type="text" id="pos-search" placeholder="Buscar producto...">
            </div>
            <div id="pos-cats">
                <button class="pos-cat-btn active" data-cat="all">Todos</button>
            </div>
            <div id="pos-grid">
                <div class="pos-no-results">Cargando productos...</div>
            </div>
            <div id="pos-pagination" style="display:none;">
                <button class="pag-btn" id="pag-first" title="Primera página"><i class="ti ti-chevrons-left"></i></button>
                <button class="pag-btn" id="pag-prev"  title="Anterior"><i class="ti ti-chevron-left"></i></button>
                <span class="pag-info" id="pag-info">Página 1 de 1</span>
                <button class="pag-btn" id="pag-next"  title="Siguiente"><i class="ti ti-chevron-right"></i></button>
                <button class="pag-btn" id="pag-last"  title="Última página"><i class="ti ti-chevrons-right"></i></button>
            </div>
        </div>

        <!-- Cart -->
        <div id="pos-cart">
            <div id="pos-cart-header">
                <span><i class="ti ti-shopping-bag me-1" style="color:var(--color-primario);"></i>Carrito</span>
                <span id="pos-cart-count" class="badge" style="background:var(--color-primario);color:#fff;">0</span>
            </div>

            <div id="pos-cart-items">
                <div class="pos-cart-empty">El carrito está vacío</div>
            </div>

            <div id="pos-payment">
                <div id="pos-totals">
                    <div class="total-row">
                        <span class="total-label">Total a cobrar</span>
                        <span class="total-value" id="pos-total-display">$0.00</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label-usd">Equivalente USD</span>
                        <span class="total-value-usd" id="pos-total-usd">—</span>
                    </div>
                </div>

                <div class="pay-input-group">
                    <span class="pay-prefix"><i class="ti ti-cash me-1"></i>Efectivo MXN</span>
                    <input type="number" id="pay_pesos" placeholder="0.00" min="0" step="1">
                </div>
                <div class="pay-input-group">
                    <span class="pay-prefix"><i class="ti ti-currency-dollar me-1"></i>Efectivo USD</span>
                    <input type="number" id="pay_dolares" placeholder="0.00" min="0" step="1">
                </div>
                <div class="pay-input-group">
                    <span class="pay-prefix"><i class="ti ti-credit-card me-1"></i>Tarjeta MXN</span>
                    <input type="number" id="pay_tarjeta" placeholder="0.00" min="0" step="1">
                </div>
                <div class="pay-input-group">
                    <span class="pay-prefix"><i class="ti ti-building-bank me-1"></i>Transferencia</span>
                    <input type="number" id="pay_transferencia" placeholder="0.00" min="0" step="1">
                </div>

                <div id="pos-faltante-row">
                    <span>Faltante</span>
                    <div class="cambio-vals">
                        <span class="faltante-val" id="pos-faltante-display">$0.00</span>
                        <span class="cambio-usd-val" id="pos-faltante-usd">—</span>
                    </div>
                </div>
                <div id="pos-cambio-row">
                    <span>Cambio</span>
                    <div class="cambio-vals">
                        <span class="cambio-val" id="pos-cambio-display">$0.00</span>
                        <span class="cambio-usd-val" id="pos-cambio-usd">—</span>
                    </div>
                </div>

                <button id="btn_cobrar" disabled>
                    <i class="ti ti-check me-1"></i>Cobrar
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Modal fraccionables: tipo + cantidad en un solo paso -->
<div class="modal fade" id="modal_fraccionar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modal_fraccionar_nombre">Agregar producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="fraccionar_tipo_wrap" class="mb-3">
                    <label class="form-label fw-semibold mb-2" style="font-size:.85rem;">Vender por</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="fraccionar_tipo" id="fraccionar_tipo_paquete" value="presentacion" checked>
                        <label class="btn btn-outline-primary" for="fraccionar_tipo_paquete" id="fraccionar_lbl_paquete">Paquete</label>
                        <input type="radio" class="btn-check" name="fraccionar_tipo" id="fraccionar_tipo_unidad" value="unidad">
                        <label class="btn btn-outline-primary" for="fraccionar_tipo_unidad" id="fraccionar_lbl_unidad">Por unidad</label>
                    </div>
                </div>
                <div class="alert alert-info py-2 mb-3" style="font-size:.85rem;">
                    Precio: <strong id="fraccionar_precio_preview">—</strong>
                </div>
                <label class="form-label fw-semibold" for="fraccionar_cantidad">Cantidad</label>
                <input type="number" id="fraccionar_cantidad" class="form-control" min="1" step="1" placeholder="0">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btn_fraccionar_confirmar" class="btn btn-primary"
                    style="background-color:var(--color-primario);border-color:var(--color-primario);">
                    Agregar al carrito
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var APP_URL_POS = '<?= $app_url ?>';
var POS_USUARIO = '<?= htmlspecialchars(($_SESSION['usuario_nombre'] ?? '') . ' ' . ($_SESSION['usuario_apellidos'] ?? '')) ?>';
</script>
