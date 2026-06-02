$(document).ready(function () {

    var app_url      = $('meta[name="app-url"]').attr('content');
    var ajax_url     = app_url + '/views/ajax/ajax_inventario_movimientos.php';
    var ajax_prods   = app_url + '/views/ajax/ajax_inventario_productos.php';
    var ajax_motivos = app_url + '/views/ajax/ajax_inventario_motivos.php';
    var tabla;
    var modal        = new bootstrap.Modal(document.getElementById('modal_movimiento'));
    var permisos     = window.PERMISOS_MOVIMIENTOS || { ver: false, editar: false };
    var prodSeFragciona    = false;
    var prodCantPresentacion = 1;

    var tsFiltProd = new TomSelect('#filtro_producto', { create: false });
    var tsFiltTipo = new TomSelect('#filtro_tipo',     { create: false });
    var tsProd     = new TomSelect('#mov_id_producto', { create: false });
    var tsTipo     = new TomSelect('#mov_tipo',        { create: false });
    var tsMotivo   = new TomSelect('#mov_id_motivo',   { create: false });

    var hoy30m   = new Date();
    var desde30m = new Date();
    desde30m.setDate(hoy30m.getDate() - 30);

    flatpickr('#filtro_fecha_desde', { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', defaultDate: desde30m, appendTo: document.body });
    flatpickr('#filtro_fecha_hasta', { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', defaultDate: hoy30m,   appendTo: document.body });

    // ── Cargar productos en filtros y modal ─────────────────────────────────
    $.getJSON(ajax_url + '?action=productos', function (res) {
        tsFiltProd.clearOptions();
        tsFiltProd.addOption({ value: '', text: 'Todos los productos' });
        tsProd.clearOptions();
        tsProd.addOption({ value: '', text: '— Selecciona un producto —' });
        $.each(res.data || [], function (_, p) {
            var label = p.codigo + ' — ' + p.nombre;
            tsFiltProd.addOption({ value: String(p.id), text: label });
            tsProd.addOption({ value: String(p.id), text: label });
        });
        tsFiltProd.refreshOptions(false);
        tsProd.refreshOptions(false);
    });

    // ── Al seleccionar producto en modal ────────────────────────────────────
    tsProd.on('change', function (val) {
        if (!val) {
            $('#mov_info_stock').hide();
            $('#wrap_mov_toggle').hide();
            prodSeFragciona = false;
            return;
        }
        $.getJSON(ajax_prods + '?action=get&id=' + val, function (res) {
            if (res.status !== 'ok') return;
            var p = res.data;
            prodSeFragciona      = !!parseInt(p.se_fracciona);
            prodCantPresentacion = parseFloat(p.cantidad_presentacion) || 1;

            var stockTxt = parseFloat(p.stock_actual).toFixed(2) + ' ' + (p.abreviatura || '');
            if (prodSeFragciona && prodCantPresentacion > 0) {
                var pres = Math.floor(parseFloat(p.stock_actual) / prodCantPresentacion);
                stockTxt += ' (' + pres + ' pza)';
            }
            $('#mov_stock_actual').text(stockTxt);
            $('#mov_info_stock').show();

            if (prodSeFragciona) {
                $('input[name="mov_modo"][value="cantidad"]').prop('checked', true);
                actualizarLabelCantidad('cantidad');
                $('#wrap_mov_toggle').show();
            } else {
                $('#wrap_mov_toggle').hide();
            }
        });
    });

    // ── Modo ingreso (por presentacion / por cantidad) ──────────────────────
    function actualizarLabelCantidad(modo) {
        if (modo === 'presentacion') {
            $('#lbl_mov_cantidad').text('Cantidad (presentaciones)');
            $('#mov_cantidad').attr('step', '1').attr('min', '1');
        } else {
            $('#lbl_mov_cantidad').text('Cantidad');
            $('#mov_cantidad').attr('step', '0.01').attr('min', '0.01');
        }
    }

    $('input[name="mov_modo"]').on('change', function () {
        actualizarLabelCantidad($(this).val());
    });

    // ── Al cambiar tipo: recargar motivos ───────────────────────────────────
    function cargarMotivos(tipo) {
        $.getJSON(ajax_motivos + '?action=list_by_tipo&tipo=' + tipo, function (res) {
            tsMotivo.clearOptions();
            tsMotivo.addOption({ value: '', text: '— Sin motivo —' });
            $.each(res.data || [], function (_, m) {
                tsMotivo.addOption({ value: String(m.id), text: m.nombre });
            });
            tsMotivo.setValue('');
            tsMotivo.refreshOptions(false);
        });
    }

    tsTipo.on('change', function (val) { cargarMotivos(val); });

    // ── DataTables ──────────────────────────────────────────────────────────
    tabla = $('#tabla_movimientos').DataTable({
        ajax: {
            url:     ajax_url,
            type:    'GET',
            dataSrc: 'data',
            data: function () {
                var d = { action: 'list' };
                if (tsFiltProd.getValue()) d.id_producto = tsFiltProd.getValue();
                if (tsFiltTipo.getValue()) d.tipo        = tsFiltTipo.getValue();
                var fd = $('#filtro_fecha_desde').val();
                var fh = $('#filtro_fecha_hasta').val();
                if (fd) d.fecha_desde = fd;
                if (fh) d.fecha_hasta = fh;
                return d;
            }
        },
        columns: [
            {
                data: 'fecha_alta',
                render: function (d) { return d ? d.substring(0, 16).replace('T', ' ') : '—'; }
            },
            {
                data: null,
                render: function (_, __, row) {
                    return '<a href="' + app_url + '/inventario/productos/detalle?id=' + row.id_producto + '" class="text-decoration-none">' + row.producto + '</a>';
                }
            },
            { data: 'codigo', render: function (d) { return '<code>' + d + '</code>'; } },
            {
                data: 'tipo',
                render: function (d) {
                    return d === 'entrada'
                        ? '<span class="badge bg-green-lt text-green"><i class="ti ti-arrow-up me-1"></i>Entrada</span>'
                        : '<span class="badge bg-red-lt text-red"><i class="ti ti-arrow-down me-1"></i>Salida</span>';
                }
            },
            { data: 'cantidad',       render: function (d) { return parseFloat(d).toFixed(2); } },
            { data: 'stock_anterior', render: function (d) { return parseFloat(d).toFixed(2); } },
            { data: 'stock_nuevo',    render: function (d) { return parseFloat(d).toFixed(2); } },
            { data: 'motivo',  render: function (d) { return d || '<span class="text-muted">—</span>'; } },
            { data: 'usuario', render: function (d) { return d || '<span class="text-muted">—</span>'; } },
            { data: 'notas',   render: function (d) { return d || '<span class="text-muted">—</span>'; } }
        ],
        language: { url: window.DT_LANG_URL },
        pageLength: 20,
        order: [[0, 'desc']],
        responsive: true
    });

    // ── Filtrar ─────────────────────────────────────────────────────────────
    $('#btn_filtrar').on('click', function () {
        tabla.ajax.reload(null, false);
    });

    // ── Modal nuevo movimiento ──────────────────────────────────────────────
    $('#btn_nuevo_movimiento').on('click', function () {
        $('#form_movimiento')[0].reset();
        tsProd.setValue('');
        tsTipo.setValue('entrada');
        cargarMotivos('entrada');
        $('#mov_info_stock').hide();
        $('#wrap_mov_toggle').hide();
        prodSeFragciona = false;
        actualizarLabelCantidad('cantidad');
        modal.show();
    });

    // ── Submit movimiento ───────────────────────────────────────────────────
    $('#form_movimiento').on('submit', function (e) {
        e.preventDefault();

        var id_prod = tsProd.getValue();
        if (!id_prod) { mostrarError('Selecciona un producto.'); return; }

        var modo      = $('input[name="mov_modo"]:checked').val() || 'cantidad';
        var cantInput = parseFloat($('#mov_cantidad').val()) || 0;
        var cantFinal = (modo === 'presentacion' && prodSeFragciona) ? cantInput * prodCantPresentacion : cantInput;

        if (cantFinal <= 0) { mostrarError('La cantidad debe ser mayor a 0.'); return; }

        var btn = $('#btn_guardar_movimiento').prop('disabled', true).text('Registrando...');

        ajaxPost({
            url: ajax_url,
            data: {
                action:      'movimiento',
                id_producto: id_prod,
                tipo:        tsTipo.getValue(),
                cantidad:    cantFinal,
                id_motivo:   tsMotivo.getValue(),
                notas:       $('#mov_notas').val().trim()
            },
            onSuccess: function (res) {
                modal.hide();
                mostrarExito(res.message);
                tabla.ajax.reload(null, false);
            },
            onError:    function (res) { mostrarError(res.message); },
            onComplete: function ()    { btn.prop('disabled', false).text('Registrar'); }
        });
    });

});
