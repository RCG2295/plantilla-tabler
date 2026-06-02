$(function () {
    var ajax_url = APP_URL_POS + '/views/ajax/ajax_ventas_pos.php';

    var productos = [];
    var carrito = [];
    var tipoCambio = 0;
    var turnoId = null;
    var pendingProduct = null; // product waiting for tipo-precio selection

    // ── Helpers ─────────────────────────────────────────────────────────────

    function fmtMXN(n) {
        return '$' + parseFloat(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtDate(s) {
        if (!s) return '—';
        var d = new Date(s.replace(' ', 'T'));
        return d.toLocaleString('es-MX');
    }

    // ── Load initial data ────────────────────────────────────────────────────

    function loadTipoCambio(cb) {
        $.getJSON(ajax_url + '?action=tipo_cambio', function (res) {
            if (res.status === 'ok' && res.data) {
                tipoCambio = parseFloat(res.data.valor) || 0;
                $('#pos-tc-val').text('$' + tipoCambio.toFixed(2));
            }
            if (cb) cb();
        });
    }

    function loadProductos() {
        $.getJSON(ajax_url + '?action=productos', function (res) {
            if (res.status !== 'ok') return;
            productos = res.data;
            filtrarProductos();
        });
    }

    function loadCategorias() {
        $.getJSON(ajax_url + '?action=categorias', function (res) {
            if (res.status !== 'ok') return;
            res.data.forEach(function (c) {
                $('#pos-cats').append('<button class="pos-cat-btn" data-cat="' + c.id + '">' + escHtml(c.nombre) + '</button>');
            });
        });
    }

    function checkTurno() {
        $.getJSON(APP_URL_POS + '/views/ajax/ajax_ventas_caja.php?action=turno_activo', function (res) {
            if (res.status === 'ok' && res.data) {
                turnoId = res.data.id;
                $('#pos-turno-id').text('#' + String(turnoId).padStart(4, '0'));
                $('#pos-sucursal').text(res.data.sucursal_nombre || '—');
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin turno activo',
                    text: 'Necesitas abrir un turno antes de usar el punto de venta.',
                    confirmButtonText: 'Ir a Control de caja',
                    allowOutsideClick: false,
                }).then(function () {
                    window.location.href = APP_URL_POS + '/ventas/mi_caja';
                });
            }
        });
    }

    function escHtml(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Product grid ─────────────────────────────────────────────────────────

    function renderGrid(prods) {
        var $g = $('#pos-grid');
        $g.empty();
        if (!prods.length) {
            $g.append('<div class="pos-no-results">Sin productos disponibles</div>');
            return;
        }
        prods.forEach(function (p) {
            var stock = parseFloat(p.stock_actual || 0);
            var sinStock = stock <= 0;
            var imgHtml = p.foto
                ? '<img class="pos-prod-img" src="' + APP_URL_POS + '/views/uploads/inventario_productos/' + escHtml(p.foto) + '" alt="">'
                : '<div class="pos-prod-icon"><i class="ti ti-box"></i></div>';
            var precio = '';
            if (p.se_fracciona && p.precio_venta_unidad > 0 && p.precio_venta > 0) {
                precio = fmtMXN(p.precio_venta) + ' / ' + fmtMXN(p.precio_venta_unidad) + ' ' + escHtml(p.unidad || '');
            } else if (p.precio_venta > 0) {
                precio = fmtMXN(p.precio_venta);
            } else {
                precio = fmtMXN(p.precio_venta_unidad) + ' / ' + escHtml(p.unidad || '');
            }

            $g.append(
                '<div class="pos-product-card' + (sinStock ? ' sin-stock' : '') + '" data-id="' + p.id + '">' +
                    imgHtml +
                    '<div class="pos-prod-name">' + escHtml(p.nombre) + '</div>' +
                    '<div class="pos-prod-price">' + precio + '</div>' +
                    '<div class="pos-prod-stock">Stock: ' + parseFloat(stock).toLocaleString('es-MX', { maximumFractionDigits: 2 }) + ' ' + escHtml(p.unidad || '') + '</div>' +
                '</div>'
            );
        });
    }

    // ── Paginación ───────────────────────────────────────────────────────────

    var PAGE_SIZE         = 35;
    var paginaActual      = 1;
    var productosFiltrados = [];

    function renderPagina() {
        var total  = productosFiltrados.length;
        var paginas = Math.max(1, Math.ceil(total / PAGE_SIZE));
        if (paginaActual > paginas) paginaActual = paginas;

        var inicio = (paginaActual - 1) * PAGE_SIZE;
        renderGrid(productosFiltrados.slice(inicio, inicio + PAGE_SIZE));

        if (paginas <= 1) {
            $('#pos-pagination').hide();
        } else {
            $('#pos-pagination').show();
            $('#pag-info').text('Página ' + paginaActual + ' de ' + paginas + ' (' + total + ' productos)');
            $('#pag-first, #pag-prev').prop('disabled', paginaActual === 1);
            $('#pag-next, #pag-last').prop('disabled', paginaActual === paginas);
        }
    }

    $('#pag-first').on('click', function () { paginaActual = 1; renderPagina(); });
    $('#pag-prev').on('click',  function () { if (paginaActual > 1) { paginaActual--; renderPagina(); } });
    $('#pag-next').on('click',  function () { paginaActual++; renderPagina(); });
    $('#pag-last').on('click',  function () {
        paginaActual = Math.max(1, Math.ceil(productosFiltrados.length / PAGE_SIZE));
        renderPagina();
    });

    // ── Category filter ──────────────────────────────────────────────────────

    var activeCat = 'all';
    var searchText = '';

    function filtrarProductos() {
        productosFiltrados = productos.filter(function (p) {
            var matchCat = activeCat === 'all' || String(p.id_categoria) === String(activeCat);
            var matchText = !searchText || p.nombre.toLowerCase().indexOf(searchText.toLowerCase()) >= 0 ||
                            (p.codigo && p.codigo.toLowerCase().indexOf(searchText.toLowerCase()) >= 0);
            return matchCat && matchText;
        });
        paginaActual = 1;
        renderPagina();
    }

    $(document).on('click', '.pos-cat-btn', function () {
        $('.pos-cat-btn').removeClass('active');
        $(this).addClass('active');
        activeCat = $(this).data('cat');
        filtrarProductos();
    });

    $('#pos-search').on('input', function () {
        searchText = $(this).val().trim();
        filtrarProductos();
    });

    // ── Add to cart ──────────────────────────────────────────────────────────

    $(document).on('click', '.pos-product-card:not(.sin-stock)', function () {
        var id = $(this).data('id');
        var p = productos.find(function (x) { return x.id == id; });
        if (!p) return;

        if (parseInt(p.se_fracciona)) {
            mostrarModalFraccionar(p);
        } else {
            agregarAlCarrito(p, 'presentacion', 1);
        }
    });

    function mostrarModalFraccionar(p) {
        pendingProduct = p;
        var tienePaquete = parseFloat(p.precio_venta || 0) > 0;
        var tieneUnidad  = parseFloat(p.precio_venta_unidad || 0) > 0;

        $('#modal_fraccionar_nombre').text(p.nombre);

        // Siempre mostrar el selector; ocultar la opción que no aplica
        $('#fraccionar_tipo_wrap').show();
        $('#fraccionar_tipo_paquete').toggleClass('d-none', !tienePaquete);
        $('#fraccionar_lbl_paquete').toggleClass('d-none', !tienePaquete);
        $('#fraccionar_tipo_unidad').toggleClass('d-none', !tieneUnidad);
        $('#fraccionar_lbl_unidad').toggleClass('d-none', !tieneUnidad);

        if (tienePaquete) {
            $('#fraccionar_lbl_paquete').text('Paquete — ' + fmtMXN(p.precio_venta));
        }
        if (tieneUnidad) {
            $('#fraccionar_lbl_unidad').text('Por ' + escHtml(p.unidad || 'unidad') + ' — ' + fmtMXN(p.precio_venta_unidad));
        }

        // Seleccionar la primera opción disponible
        if (tienePaquete) {
            $('#fraccionar_tipo_paquete').prop('checked', true);
            actualizarPrecioPreview(p, 'presentacion');
            $('#fraccionar_cantidad').attr('step', '1');
        } else {
            $('#fraccionar_tipo_unidad').prop('checked', true);
            actualizarPrecioPreview(p, 'unidad');
            $('#fraccionar_cantidad').attr('step', '1');
        }

        $('#fraccionar_cantidad').val('');
        var modal = new bootstrap.Modal(document.getElementById('modal_fraccionar'));
        modal.show();
        document.getElementById('modal_fraccionar').addEventListener('shown.bs.modal', function handler() {
            document.getElementById('fraccionar_cantidad').focus();
            this.removeEventListener('shown.bs.modal', handler);
        });
    }

    function actualizarPrecioPreview(p, tipo) {
        var precio = tipo === 'unidad' ? parseFloat(p.precio_venta_unidad) : parseFloat(p.precio_venta);
        var label  = tipo === 'unidad' ? 'por ' + (p.unidad || 'unidad') : 'por paquete';
        $('#fraccionar_precio_preview').text(fmtMXN(precio) + ' ' + label);
    }

    $('input[name="fraccionar_tipo"]').on('change', function () {
        if (!pendingProduct) return;
        var tipo = $(this).val();
        actualizarPrecioPreview(pendingProduct, tipo);
        $('#fraccionar_cantidad').attr('step', '1').val('').focus();
    });

    $('#btn_fraccionar_confirmar').on('click', confirmarFraccionar);

    $('#modal_fraccionar').on('keydown', function (e) {
        if (e.key === 'Enter') confirmarFraccionar();
    });

    function confirmarFraccionar() {
        if (!pendingProduct) return;
        var p = pendingProduct;
        var tipo = $('input[name="fraccionar_tipo"]:checked').val() || 'presentacion';
        var qty  = parseFloat($('#fraccionar_cantidad').val()) || 0;
        if (qty <= 0) { mostrarError('Ingresa una cantidad válida.'); return; }
        bootstrap.Modal.getInstance(document.getElementById('modal_fraccionar')).hide();
        pendingProduct = null;
        agregarAlCarrito(p, tipo, qty);
    }

    function itemABase(item, cantidad) {
        var cp = item.cantidad_presentacion || 1;
        return item.tipo_precio === 'presentacion' ? cantidad * cp : cantidad;
    }

    function carritoBaseExcluido(id_producto, excluirIdx) {
        return carrito.reduce(function (s, it, i) {
            if (i === excluirIdx || it.id_producto != id_producto) return s;
            return s + itemABase(it, it.cantidad);
        }, 0);
    }

    function agregarAlCarrito(p, tipo, qty) {
        var cantPres = parseFloat(p.cantidad_presentacion) || 1;
        var stockAct = parseFloat(p.stock_actual || 0);
        var qtyBase  = tipo === 'presentacion' ? qty * cantPres : qty;

        var existIdx = carrito.findIndex(function (x) { return x.id_producto == p.id && x.tipo_precio === tipo; });
        var yaBase   = carritoBaseExcluido(p.id, existIdx) +
                       (existIdx >= 0 ? itemABase(carrito[existIdx], carrito[existIdx].cantidad) : 0);

        if (yaBase + qtyBase > stockAct + 0.001) {
            var disponible = Math.max(0, stockAct - yaBase);
            mostrarError('Stock insuficiente. Disponible: ' +
                disponible.toLocaleString('es-MX', { maximumFractionDigits: 2 }) + ' ' + (p.unidad || ''));
            return;
        }

        var precio = tipo === 'unidad' ? parseFloat(p.precio_venta_unidad) : parseFloat(p.precio_venta);
        if (existIdx >= 0) {
            carrito[existIdx].cantidad += qty;
            carrito[existIdx].subtotal = carrito[existIdx].cantidad * carrito[existIdx].precio_unitario;
        } else {
            carrito.push({
                id_producto:           p.id,
                nombre_producto:       p.nombre,
                unidad:                p.unidad || '',
                tipo_precio:           tipo,
                cantidad:              qty,
                precio_unitario:       precio,
                subtotal:              qty * precio,
                stock_max:             stockAct,
                cantidad_presentacion: cantPres,
            });
        }
        renderCarrito();
    }

    // ── Render cart ──────────────────────────────────────────────────────────

    function renderCarrito() {
        var $items = $('#pos-cart-items');
        $items.empty();

        if (!carrito.length) {
            $items.append('<div class="pos-cart-empty">El carrito está vacío</div>');
            $('#pos-cart-count').text(0);
            recalcTotal();
            return;
        }

        carrito.forEach(function (item, idx) {
            var tipoLabel = item.tipo_precio === 'unidad' ? 'Por ' + item.unidad : 'Presentación';
            $items.append(
                '<div class="cart-item" data-idx="' + idx + '">' +
                    '<div class="d-flex flex-column flex-grow-1 min-width-0">' +
                        '<div class="cart-item-name">' + escHtml(item.nombre_producto) + '</div>' +
                        '<div class="cart-item-tipo text-muted">' + tipoLabel + ' — ' + fmtMXN(item.precio_unitario) + '</div>' +
                    '</div>' +
                    '<div class="cart-item-qty">' +
                        '<button class="cart-qty-btn btn-qty-dec" data-idx="' + idx + '">-</button>' +
                        '<input class="cart-qty-input" type="number" value="' + item.cantidad + '" min="1" step="1" data-idx="' + idx + '">' +
                        '<button class="cart-qty-btn btn-qty-inc" data-idx="' + idx + '">+</button>' +
                    '</div>' +
                    '<div class="cart-item-price">' + fmtMXN(item.subtotal) + '</div>' +
                    '<div class="cart-item-remove" data-idx="' + idx + '" title="Quitar"><i class="ti ti-x"></i></div>' +
                '</div>'
            );
        });

        $('#pos-cart-count').text(carrito.length);
        recalcTotal();
    }

    // Qty controls
    $(document).on('click', '.btn-qty-dec', function () {
        var idx = $(this).data('idx');
        var nueva = carrito[idx].cantidad - 1;
        if (nueva <= 0) { carrito.splice(idx, 1); renderCarrito(); return; }
        carrito[idx].cantidad = nueva;
        carrito[idx].subtotal = nueva * carrito[idx].precio_unitario;
        renderCarrito();
    });

    $(document).on('click', '.btn-qty-inc', function () {
        var idx     = parseInt($(this).data('idx'));
        var item    = carrito[idx];
        var nuevaCant = item.cantidad + 1;
        var nuevaBase = itemABase(item, nuevaCant);
        var otrosBase = carritoBaseExcluido(item.id_producto, idx);
        if (nuevaBase + otrosBase > item.stock_max + 0.001) {
            mostrarError('Stock insuficiente.');
            return;
        }
        item.cantidad = nuevaCant;
        item.subtotal = nuevaCant * item.precio_unitario;
        renderCarrito();
    });

    $(document).on('change', '.cart-qty-input', function () {
        var idx  = parseInt($(this).data('idx'));
        var val  = parseFloat($(this).val()) || 0;
        if (val <= 0) { carrito.splice(idx, 1); renderCarrito(); return; }
        var item      = carrito[idx];
        var nuevaBase = itemABase(item, val);
        var otrosBase = carritoBaseExcluido(item.id_producto, idx);
        if (nuevaBase + otrosBase > item.stock_max + 0.001) {
            mostrarError('Stock insuficiente. Máximo: ' +
                Math.max(0, item.stock_max - otrosBase).toLocaleString('es-MX', { maximumFractionDigits: 2 }) +
                ' ' + item.unidad);
            $(this).val(item.cantidad);
            return;
        }
        item.cantidad = val;
        item.subtotal = val * item.precio_unitario;
        renderCarrito();
    });

    $(document).on('click', '.cart-item-remove', function () {
        var idx = $(this).data('idx');
        carrito.splice(idx, 1);
        renderCarrito();
    });

    // ── Payment & totals ─────────────────────────────────────────────────────

    function recalcTotal() {
        var total = carrito.reduce(function (s, i) { return s + i.subtotal; }, 0);
        $('#pos-total-display').text(fmtMXN(total));
        $('#pos-total-usd').text(tipoCambio > 0 ? '$' + (total / tipoCambio).toFixed(2) : '—');

        // Validate dollar cash constraint
        var pagosPesos = parseFloat($('#pay_pesos').val()) || 0;
        if (pagosPesos >= total && total > 0) {
            $('#pay_dolares').val('').prop('disabled', true).css('background', '#f5f5f5');
        } else {
            $('#pay_dolares').prop('disabled', false).css('background', '');
        }

        calcCambio();
    }

    function calcCambio() {
        var total = carrito.reduce(function (s, i) { return s + i.subtotal; }, 0);
        var pesos     = parseFloat($('#pay_pesos').val())         || 0;
        var dolares   = parseFloat($('#pay_dolares').val())       || 0;
        var tarjeta   = parseFloat($('#pay_tarjeta').val())       || 0;
        var transf    = parseFloat($('#pay_transferencia').val()) || 0;

        var dolaresEnPesos = dolares * tipoCambio;
        var totalPagado    = pesos + dolaresEnPesos + tarjeta + transf;
        var diferencia     = totalPagado - total;

        var usdStr = function (mxn) {
            return tipoCambio > 0 ? '$' + (mxn / tipoCambio).toFixed(2) + ' USD' : '';
        };

        if (diferencia >= 0) {
            $('#pos-faltante-row').hide();
            $('#pos-cambio-row').css('display', 'flex');
            $('#pos-cambio-display').text(fmtMXN(diferencia));
            $('#pos-cambio-usd').text(usdStr(diferencia));
        } else {
            $('#pos-cambio-row').hide();
            $('#pos-faltante-row').css('display', 'flex');
            $('#pos-faltante-display').text(fmtMXN(-diferencia));
            $('#pos-faltante-usd').text(usdStr(-diferencia));
        }

        var tarjetaExcede    = tarjeta > total;
        var transfExcede     = transf > total;
        var sinProductos     = !carrito.length;
        var pagoInsuficiente = totalPagado < total - 0.001;
        var hayPago          = pesos > 0 || dolares > 0 || tarjeta > 0 || transf > 0;

        $('#btn_cobrar').prop('disabled', sinProductos || pagoInsuficiente || !hayPago || tarjetaExcede || transfExcede);
    }

    $('#pay_pesos, #pay_dolares, #pay_tarjeta, #pay_transferencia').on('input', function () {
        calcCambio();
        // Reapply dollar constraint when pesos changes
        if ($(this).is('#pay_pesos')) recalcTotal();
    });

    // ── Cobrar ───────────────────────────────────────────────────────────────

    $('#btn_cobrar').on('click', function () {
        if (!turnoId) {
            mostrarError('No hay turno activo.');
            return;
        }

        var total    = carrito.reduce(function (s, i) { return s + i.subtotal; }, 0);
        var pesos    = parseFloat($('#pay_pesos').val()) || 0;
        var dolares  = parseFloat($('#pay_dolares').val()) || 0;
        var tarjeta  = parseFloat($('#pay_tarjeta').val()) || 0;
        var transf   = parseFloat($('#pay_transferencia').val()) || 0;
        var dolaresEnPesos = dolares * tipoCambio;
        var totalPagado    = pesos + dolaresEnPesos + tarjeta + transf;
        var cambio         = totalPagado - total;

        var filas = '<table style="width:100%;font-size:.9rem;border-collapse:collapse;">';
        filas += '<tr><td style="padding:3px 6px;color:#555;">Total a cobrar</td><td style="padding:3px 6px;text-align:right;font-weight:700;">' + fmtMXN(total) + '</td></tr>';
        if (pesos > 0)   filas += '<tr><td style="padding:3px 6px;color:#555;">Efectivo MXN</td><td style="padding:3px 6px;text-align:right;">' + fmtMXN(pesos) + '</td></tr>';
        if (dolares > 0) filas += '<tr><td style="padding:3px 6px;color:#555;">Efectivo USD</td><td style="padding:3px 6px;text-align:right;">$' + dolares.toFixed(2) + ' <small style="color:#999;">(= ' + fmtMXN(dolaresEnPesos) + ')</small></td></tr>';
        if (tarjeta > 0) filas += '<tr><td style="padding:3px 6px;color:#555;">Tarjeta MXN</td><td style="padding:3px 6px;text-align:right;">' + fmtMXN(tarjeta) + '</td></tr>';
        if (transf > 0)  filas += '<tr><td style="padding:3px 6px;color:#555;">Transferencia</td><td style="padding:3px 6px;text-align:right;">' + fmtMXN(transf) + '</td></tr>';
        filas += '<tr style="border-top:1px solid #dee2e6;"><td style="padding:6px 6px 3px;font-weight:700;">Cambio</td><td style="padding:6px 6px 3px;text-align:right;font-weight:700;color:#1b8ea3;">' + fmtMXN(cambio) + (tipoCambio > 0 ? ' <small style="color:#999;">(≈ $' + (cambio / tipoCambio).toFixed(2) + ' USD)</small>' : '') + '</td></tr>';
        filas += '</table>';

        Swal.fire({
            title: 'Confirmar cobro',
            html: filas,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-check me-1"></i>Cobrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: 'var(--color-primario)',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var pagos = [];
            if (pesos > 0)   pagos.push({ forma_pago: 'efectivo_pesos',   monto: pesos,   monto_pesos: pesos });
            if (dolares > 0) pagos.push({ forma_pago: 'efectivo_dolares', monto: dolares, monto_pesos: dolaresEnPesos });
            if (tarjeta > 0) pagos.push({ forma_pago: 'tarjeta',          monto: tarjeta, monto_pesos: tarjeta });
            if (transf > 0)  pagos.push({ forma_pago: 'transferencia',    monto: transf,  monto_pesos: transf });

            $('#btn_cobrar').prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>Procesando...');

            $.ajax({
                url: ajax_url,
                method: 'POST',
                data: {
                    action: 'registrar',
                    subtotal: total,
                    total: total,
                    tipo_cambio: tipoCambio,
                    items: JSON.stringify(carrito),
                    pagos: JSON.stringify(pagos),
                },
                dataType: 'json',
                success: function (res) {
                    $('#btn_cobrar').prop('disabled', false).html('<i class="ti ti-check me-1"></i>Cobrar');
                    if (res.status === 'ok') {
                        imprimirTicket(res.id_venta, res.folio, function () {
                            carrito = [];
                            $('#pay_pesos, #pay_dolares, #pay_tarjeta, #pay_transferencia').val('');
                            renderCarrito();
                            loadProductos();
                        });
                    } else {
                        mostrarError(res.message);
                    }
                },
                error: function () {
                    $('#btn_cobrar').prop('disabled', false).html('<i class="ti ti-check me-1"></i>Cobrar');
                    mostrarError('Error de conexión.');
                }
            });
        });
    });

    // ── Ticket ───────────────────────────────────────────────────────────────

    function imprimirTicket(id_venta, folio, cb) {
        window.open(APP_URL_POS + '/views/tickets/venta_ticket.php?id=' + id_venta, '_blank');
        if (cb) cb();
    }

    // ── Init ─────────────────────────────────────────────────────────────────

    loadTipoCambio(function () {
        checkTurno();
        loadCategorias();
        loadProductos();
    });
});
