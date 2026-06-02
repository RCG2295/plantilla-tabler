$(document).ready(function () {

    var app_url    = $('meta[name="app-url"]').attr('content');
    var ajax_url   = app_url + '/views/ajax/ajax_compras_compras.php';
    var ajax_provs = app_url + '/views/ajax/ajax_compras_proveedores.php';
    var ajax_prods = app_url + '/views/ajax/ajax_inventario_productos.php';
    var permisos   = window.PERMISOS_COMPRAS || { crear: false, eliminar: false };

    var tablaActivas, tablaCanceladas;
    var tablaCanceladas_inited = false;
    var modal = new bootstrap.Modal(document.getElementById('modal_compra'));
    var tsProv;
    var fpFecha, fpActivasDesde, fpActivasHasta, fpCancelDesde, fpCancelHasta;

    var hoy30   = new Date();
    var desde30 = new Date();
    desde30.setDate(hoy30.getDate() - 30);

    function mkFp(sel, def) {
        return flatpickr(sel, { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', defaultDate: def, appendTo: document.body });
    }

    fpActivasDesde = mkFp('#filtro_activas_desde', desde30);
    fpActivasHasta = mkFp('#filtro_activas_hasta', hoy30);
    fpCancelDesde  = mkFp('#filtro_canceladas_desde', desde30);
    fpCancelHasta  = mkFp('#filtro_canceladas_hasta', hoy30);

    var rowCounter   = 0;
    var rowSelectors = {}; // { rid: TomSelect }
    var rowData      = {}; // { rid: product data }
    var allProducts  = {}; // { String(id): product row }

    // ── Columnas compartidas ────────────────────────────────────────────────
    function colsBase() {
        return [
            { data: 'folio', render: function (d) { return '<code class="fw-semibold">' + d + '</code>'; } },
            {
                data: 'fecha_compra',
                render: function (d) {
                    var p = d.split('-'); return p[2] + '/' + p[1] + '/' + p[0];
                }
            },
            { data: 'proveedor_nombre', render: function (d) { return d || '<span class="text-muted">Sin proveedor</span>'; } },
            { data: 'subtotal',  className: 'text-end', render: function (d) { return '$' + parseFloat(d).toFixed(2); } },
            { data: 'iva_total', className: 'text-end', render: function (d) { return '$' + parseFloat(d).toFixed(2); } },
            { data: 'total',     className: 'text-end', render: function (d) { return '<span class="fw-bold">$' + parseFloat(d).toFixed(2) + '</span>'; } },
            { data: 'total_items', className: 'text-center', render: function (d) { return '<span class="badge bg-blue-lt text-blue">' + d + '</span>'; } }
        ];
    }

    // ── DataTable activas ───────────────────────────────────────────────────
    tablaActivas = $('#tabla_compras_activas').DataTable({
        ajax: function (_, callback) {
            $.ajax({
                url: ajax_url, type: 'GET', dataType: 'json',
                data: { action: 'list_activas', desde: $('#filtro_activas_desde').val(), hasta: $('#filtro_activas_hasta').val() },
                success: function (res) { callback({ data: res.data || [] }); },
                error:   function ()    { callback({ data: [] }); }
            });
        },
        columns: colsBase().concat([{
            data: null, orderable: false, className: 'text-center',
            render: function (_, __, row) {
                var html = '<button class="btn btn-sm btn-outline-secondary me-1 btn-ver-ticket" data-id="' + row.id + '" title="Ver ticket"><i class="ti ti-printer"></i></button>';
                if (row.archivo) {
                    html += '<a class="btn btn-sm btn-outline-info me-1" href="' + app_url + '/views/uploads/compras_compras/' + row.archivo + '" target="_blank" title="Ver adjunto"><i class="ti ti-paperclip"></i></a>';
                }
                if (permisos.eliminar) {
                    html += '<button class="btn btn-sm btn-outline-danger btn-cancelar" data-id="' + row.id + '" data-folio="' + row.folio + '" title="Cancelar"><i class="ti ti-ban"></i></button>';
                }
                return html;
            }
        }]),
        language: { url: window.DT_LANG_URL },
        pageLength: 15,
        order: [[0, 'desc']],
        responsive: true
    });

    // ── DataTable canceladas (lazy) ─────────────────────────────────────────
    $('#tab_canceladas_btn').on('shown.bs.tab', function () {
        if (tablaCanceladas_inited) return;
        tablaCanceladas_inited = true;
        tablaCanceladas = $('#tabla_compras_canceladas').DataTable({
            ajax: function (_, callback) {
                $.ajax({
                    url: ajax_url, type: 'GET', dataType: 'json',
                    data: { action: 'list_canceladas', desde: $('#filtro_canceladas_desde').val(), hasta: $('#filtro_canceladas_hasta').val() },
                    success: function (res) { callback({ data: res.data || [] }); },
                    error:   function ()    { callback({ data: [] }); }
                });
            },
            columns: colsBase().concat([{
                data: null, orderable: false, className: 'text-center',
                render: function (_, __, row) {
                    var html = '<button class="btn btn-sm btn-outline-secondary me-1 btn-ver-ticket" data-id="' + row.id + '" title="Ver ticket"><i class="ti ti-printer"></i></button>';
                    if (row.archivo) {
                        html += '<a class="btn btn-sm btn-outline-info" href="' + app_url + '/views/uploads/compras_compras/' + row.archivo + '" target="_blank" title="Ver adjunto"><i class="ti ti-paperclip"></i></a>';
                    }
                    return html;
                }
            }]),
            language: { url: window.DT_LANG_URL },
            pageLength: 15,
            order: [[0, 'desc']],
            responsive: true
        });
    });

    $('#btn_buscar_activas').on('click', function () {
        tablaActivas.ajax.reload(null, false);
    });

    $('#btn_buscar_canceladas').on('click', function () {
        if (!tablaCanceladas_inited) return;
        tablaCanceladas.ajax.reload(null, false);
    });

    // ── Tom Select proveedor ────────────────────────────────────────────────
    tsProv = new TomSelect('#compra_id_proveedor', { create: false });

    // ── Flatpickr fecha ─────────────────────────────────────────────────────
    fpFecha = flatpickr('#compra_fecha', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        defaultDate: new Date(),
        appendTo: document.body
    });

    // ── Nueva compra ────────────────────────────────────────────────────────
    $('#btn_nueva_compra').on('click', function () {
        limpiarFormulario();
        cargarProveedores();
        cargarProductos();
        modal.show();
    });

    function cargarProveedores() {
        $.getJSON(ajax_provs + '?action=for_select', function (r) {
            tsProv.clearOptions();
            tsProv.addOption({ value: '', text: 'Sin proveedor' });
            $.each(r.data || [], function (_, p) {
                tsProv.addOption({ value: String(p.id), text: p.nombre });
            });
            tsProv.setValue('');
            tsProv.refreshOptions(false);
        });
    }

    function cargarProductos() {
        $.getJSON(ajax_prods + '?action=list', function (r) {
            allProducts = {};
            $.each(r.data || [], function (_, p) {
                if (parseInt(p.estado) === 0) {
                    allProducts[String(p.id)] = p;
                }
            });
        });
    }

    // ── Agregar fila de producto ────────────────────────────────────────────
    $('#btn_agregar_producto').on('click', function () {
        addRow();
    });

    function addRow() {
        rowCounter++;
        var rid = 'r' + rowCounter;
        $('#tbody_productos_compra').append(buildRowHtml(rid));
        $('#msg_sin_productos').hide();
        updateRowNumbers();

        var options = Object.values(allProducts).map(function (p) {
            return { value: String(p.id), text: p.codigo + ' — ' + p.nombre };
        });

        var ts = new TomSelect('#prod_sel_' + rid, {
            options: options,
            valueField: 'value',
            labelField: 'text',
            searchField: ['text'],
            placeholder: 'Buscar producto...',
            maxOptions: 100,
            onChange: function (val) { onProductChange(rid, val); }
        });

        ts.positionDropdown = function () {
            var rect = this.control.getBoundingClientRect();
            this.dropdown.style.position = 'fixed';
            this.dropdown.style.top      = rect.bottom + 'px';
            this.dropdown.style.left     = rect.left + 'px';
            this.dropdown.style.width    = rect.width + 'px';
            this.dropdown.style.zIndex   = '9999';
        };

        rowSelectors[rid] = ts;
    }

    function buildRowHtml(rid) {
        return '<tr id="tr_' + rid + '">' +
            '<td class="text-muted row-num" style="width:32px;"></td>' +
            '<td style="min-width:220px;">' +
                '<select id="prod_sel_' + rid + '" class="form-select form-select-sm"></select>' +
                '<div id="prod_info_' + rid + '" class="text-muted mt-1" style="font-size:0.78rem;"></div>' +
            '</td>' +
            '<td style="width:145px;">' +
                '<div class="input-group input-group-sm">' +
                    '<input type="number" id="prod_qty_' + rid + '" class="form-control prod-input" ' +
                        'min="0.001" step="any" value="1" style="max-width:80px;">' +
                    '<span class="input-group-text px-2" id="prod_qty_lbl_' + rid + '">pza</span>' +
                '</div>' +
            '</td>' +
            '<td style="width:130px;">' +
                '<div class="input-group input-group-sm">' +
                    '<span class="input-group-text">$</span>' +
                    '<input type="number" id="prod_price_' + rid + '" class="form-control prod-input" ' +
                        'min="0" step="any" value="0.00" style="max-width:90px;">' +
                '</div>' +
            '</td>' +
            '<td style="width:90px;">' +
                '<div class="input-group input-group-sm">' +
                    '<input type="number" id="prod_iva_' + rid + '" class="form-control prod-input" ' +
                        'min="0" max="100" step="any" value="8" style="max-width:60px;">' +
                    '<span class="input-group-text px-1">%</span>' +
                '</div>' +
            '</td>' +
            '<td class="text-end fw-semibold" id="prod_total_' + rid + '" style="width:110px;">$0.00</td>' +
            '<td style="width:36px;">' +
                '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" data-rid="' + rid + '">' +
                    '<i class="ti ti-x"></i>' +
                '</button>' +
            '</td>' +
            '</tr>';
    }

    function onProductChange(rid, val) {
        var p = allProducts[val] || null;
        rowData[rid] = p;

        if (!p) {
            $('#prod_qty_lbl_' + rid).text('pza');
            $('#prod_info_' + rid).text('');
            $('#prod_price_' + rid).val('0.00');
            updateRowTotal(rid);
            return;
        }

        var ab      = p.abreviatura || 'pza';
        var seFragc = parseInt(p.se_fracciona);
        var cantPres = parseFloat(p.cantidad_presentacion) || 1;

        if (seFragc) {
            $('#prod_qty_lbl_' + rid).text('pres.');
            $('#prod_info_' + rid).html(
                '<i class="ti ti-scissors me-1"></i>Fraccionado — 1 presentación = ' + cantPres + ' ' + ab
            );
        } else {
            $('#prod_qty_lbl_' + rid).text(ab);
            $('#prod_info_' + rid).text('');
        }

        $('#prod_price_' + rid).val(parseFloat(p.precio_costo).toFixed(2));
        updateRowTotal(rid);
    }

    function updateRowTotal(rid) {
        var qty   = parseFloat($('#prod_qty_' + rid).val()) || 0;
        var price = parseFloat($('#prod_price_' + rid).val()) || 0;
        var iva   = parseFloat($('#prod_iva_' + rid).val()) || 0;
        var sub   = qty * price;
        var total = sub + (sub * iva / 100);
        $('#prod_total_' + rid).text('$' + total.toFixed(2));
        updateGrandTotals();
    }

    function updateGrandTotals() {
        var subtotal  = 0;
        var iva_total = 0;

        $('#tbody_productos_compra tr').each(function () {
            var rid   = $(this).attr('id').replace('tr_', '');
            var qty   = parseFloat($('#prod_qty_' + rid).val()) || 0;
            var price = parseFloat($('#prod_price_' + rid).val()) || 0;
            var iva   = parseFloat($('#prod_iva_' + rid).val()) || 0;
            var sub   = qty * price;
            subtotal  += sub;
            iva_total += sub * iva / 100;
        });

        $('#resumen_subtotal').text('$' + subtotal.toFixed(2));
        $('#resumen_iva').text('$' + iva_total.toFixed(2));
        $('#resumen_total').text('$' + (subtotal + iva_total).toFixed(2));
    }

    function updateRowNumbers() {
        $('#tbody_productos_compra tr').each(function (i) {
            $(this).find('.row-num').text(i + 1);
        });
    }

    // ── Eventos delegados en filas ──────────────────────────────────────────
    $(document).on('input', '.prod-input', function () {
        var rid = $(this).closest('tr').attr('id').replace('tr_', '');
        updateRowTotal(rid);
    });

    $(document).on('click', '.btn-remove-row', function () {
        var rid = $(this).data('rid');
        if (rowSelectors[rid]) { rowSelectors[rid].destroy(); delete rowSelectors[rid]; }
        delete rowData[rid];
        $('#tr_' + rid).remove();
        updateRowNumbers();
        updateGrandTotals();
        if ($('#tbody_productos_compra tr').length === 0) {
            $('#msg_sin_productos').show();
        }
    });

    // ── Submit nueva compra ─────────────────────────────────────────────────
    $('#form_compra').on('submit', function (e) {
        e.preventDefault();

        var filas = $('#tbody_productos_compra tr');
        if (filas.length === 0) {
            mostrarError('Agrega al menos un producto.');
            return;
        }

        var detalle   = [];
        var valido    = true;
        var subtotal  = 0;
        var iva_total = 0;

        filas.each(function () {
            var rid     = $(this).attr('id').replace('tr_', '');
            var ts      = rowSelectors[rid];
            var id_prod = ts ? ts.getValue() : '';
            if (!id_prod) { valido = false; return; }

            var qty   = parseFloat($('#prod_qty_'   + rid).val()) || 0;
            if (qty <= 0) { valido = false; return; }

            var price = parseFloat($('#prod_price_' + rid).val()) || 0;
            var iva   = parseFloat($('#prod_iva_'   + rid).val()) || 0;
            var p     = rowData[rid];
            var seFragc  = p && parseInt(p.se_fracciona);
            var cantPres = p ? (parseFloat(p.cantidad_presentacion) || 1) : 1;
            var cant_base = seFragc ? qty * cantPres : qty;

            var line_sub = qty * price;
            subtotal  += line_sub;
            iva_total += line_sub * (iva / 100);

            detalle.push({
                id_producto:     id_prod,
                cantidad:        qty,
                cantidad_base:   cant_base,
                precio_unitario: price,
                iva:             iva
            });
        });

        if (!valido || detalle.length === 0) {
            mostrarError('Verifica que todos los productos tengan cantidad válida.');
            return;
        }

        var fecha = $('#compra_fecha').val();
        if (!fecha) {
            mostrarError('Selecciona la fecha de compra.');
            return;
        }

        var total = subtotal + iva_total;
        if (total <= 0) {
            mostrarError('El total de la compra no puede ser $0.00. Ingresa precios válidos.');
            return;
        }

        Swal.fire({
            title: 'Confirmar compra',
            html:
                '<table class="table table-sm table-borderless mb-0 mx-auto" style="font-size:0.95rem;min-width:220px;">' +
                    '<tr><td class="text-muted text-start">Subtotal</td><td class="text-end fw-semibold">$' + subtotal.toFixed(2) + '</td></tr>' +
                    '<tr><td class="text-muted text-start">IVA</td><td class="text-end fw-semibold">$' + iva_total.toFixed(2) + '</td></tr>' +
                    '<tr class="border-top"><td class="fw-bold text-start">Total</td><td class="text-end fw-bold" style="font-size:1.15rem;">$' + total.toFixed(2) + '</td></tr>' +
                '</table>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-check me-1"></i>Confirmar',
            cancelButtonText:  'Revisar',
            confirmButtonColor: '#1b8ea3'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            var btn = $('#btn_guardar_compra').prop('disabled', true).html('<i class="ti ti-loader me-1"></i>Guardando...');

            var fd = new FormData();
            fd.append('action',       'save');
            fd.append('id_proveedor', tsProv.getValue());
            fd.append('fecha_compra', fecha);
            fd.append('notas',        $('#compra_notas').val().trim());
            fd.append('detalle',      JSON.stringify(detalle));
            var archivoInput = document.getElementById('compra_archivo');
            if (archivoInput && archivoInput.files[0]) fd.append('archivo', archivoInput.files[0]);

            $.ajax({
                url: ajax_url, type: 'POST',
                data: fd, processData: false, contentType: false,
                dataType: 'json',
                success: function (res) {
                    if (res.status !== 'ok') { mostrarError(res.message); return; }
                    modal.hide();
                    mostrarExito(res.message);
                    tablaActivas.ajax.reload(null, false);
                    window.open(app_url + '/views/tickets/compra_ticket.php?id=' + res.id, '_blank');
                },
                error:    function () { mostrarError('Error de conexión.'); },
                complete: function () { btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Guardar compra'); }
            });
        });
    });

    // ── Ver ticket (ambas tablas) ───────────────────────────────────────────
    $(document).on('click', '.btn-ver-ticket', function () {
        var id = $(this).data('id');
        window.open(app_url + '/views/tickets/compra_ticket.php?id=' + id, '_blank');
    });

    // ── Cancelar compra ─────────────────────────────────────────────────────
    $('#tabla_compras_activas').on('click', '.btn-cancelar', function () {
        var id    = $(this).data('id');
        var folio = $(this).data('folio');
        Swal.fire({
            title: '¿Cancelar compra ' + folio + '?',
            text:  'Se revertirá el stock de todos los productos de esta compra.',
            icon:  'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText:  'No',
            confirmButtonColor: '#d33'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            ajaxPost({
                url: ajax_url, data: { action: 'cancelar', id: id },
                onSuccess: function (res) {
                    mostrarExito(res.message);
                    tablaActivas.ajax.reload(null, false);
                    if (tablaCanceladas_inited) tablaCanceladas.ajax.reload(null, false);
                },
                onError: function (res) { mostrarError(res.message); }
            });
        });
    });

    // ── Limpiar formulario ──────────────────────────────────────────────────
    function limpiarFormulario() {
        $('#form_compra')[0].reset();
        $('#compra_archivo').val('');
        Object.keys(rowSelectors).forEach(function (rid) {
            rowSelectors[rid].destroy();
            delete rowSelectors[rid];
            delete rowData[rid];
        });
        $('#tbody_productos_compra').empty();
        $('#msg_sin_productos').show();
        updateGrandTotals();
        fpFecha.setDate(new Date(), false);
        if (tsProv) tsProv.setValue('');
    }

});
