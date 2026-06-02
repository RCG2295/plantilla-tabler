$(function () {
    var ajax_url = APP_URL_HV + '/views/ajax/ajax_ventas_historial_ventas.php';
    var tablaActivas, tablaCanceladas;
    var fpDesde, fpHasta;

    function fmtMXN(n) { return '$' + parseFloat(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function fmtDate(s) { if (!s) return '—'; var d = new Date(s.replace(' ', 'T')); return d.toLocaleString('es-MX'); }

    function columnasPago() {
        return [
            { data: 'efectivo_pesos',   className: 'text-end', render: function (v) { return parseFloat(v) > 0 ? fmtMXN(v) : '<span class="text-muted">—</span>'; } },
            { data: 'efectivo_dolares', className: 'text-end', render: function (v) { return parseFloat(v) > 0 ? '$' + parseFloat(v).toFixed(2) : '<span class="text-muted">—</span>'; } },
            { data: 'tarjeta',          className: 'text-end', render: function (v) { return parseFloat(v) > 0 ? fmtMXN(v) : '<span class="text-muted">—</span>'; } },
            { data: 'transferencia',    className: 'text-end', render: function (v) { return parseFloat(v) > 0 ? fmtMXN(v) : '<span class="text-muted">—</span>'; } },
        ];
    }

    function columnasBase() {
        return [
            { data: 'folio' },
            { data: null, render: function (r) { return r.cajero_nombre + ' ' + r.cajero_apellidos; } },
            { data: 'sucursal_nombre', defaultContent: '—' },
            { data: 'turno_id', render: function (v) { return v ? '#' + String(v).padStart(4, '0') : '—'; } },
            { data: 'total', className: 'text-end', render: function (v) { return fmtMXN(v); } },
        ].concat(columnasPago()).concat([
            { data: 'fecha_alta', render: fmtDate },
            { data: null, className: 'text-center', orderable: false, render: function (r) {
                return '<button class="btn btn-sm btn-outline-secondary btn-ver-ticket" data-id="' + r.id + '" title="Ver ticket"><i class="ti ti-printer"></i></button>';
            }},
        ]);
    }

    var hoy30hv   = new Date();
    var desde30hv = new Date();
    desde30hv.setDate(hoy30hv.getDate() - 30);

    // ── Flatpickr ────────────────────────────────────────────────────────────

    fpDesde = flatpickr('#filtro_desde', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        defaultDate: desde30hv,
        appendTo: document.body,
        onChange: function () { fpHasta.set('minDate', fpDesde.selectedDates[0] || null); }
    });

    fpHasta = flatpickr('#filtro_hasta', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        defaultDate: new Date(),
        appendTo: document.body,
        onChange: function () { fpDesde.set('maxDate', fpHasta.selectedDates[0] || null); }
    });

    // ── DataTable Activas ────────────────────────────────────────────────────

    tablaActivas = $('#tabla_ventas_activas').DataTable({
        ajax: {
            url: ajax_url,
            type: 'GET',
            dataSrc: function (json) {
                var data = json.data || [];
                actualizarResumen(data);
                return data;
            },
            data: function () {
                return { action: 'list', estado: '0', desde: fpDesde.input.value, hasta: fpHasta.input.value };
            }
        },
        language: { url: window.DT_LANG_URL },
        order: [[9, 'desc']],
        pageLength: 25,
        columns: columnasBase(),
    });

    // ── DataTable Canceladas ─────────────────────────────────────────────────

    tablaCanceladas = $('#tabla_ventas_canceladas').DataTable({
        ajax: {
            url: ajax_url,
            type: 'GET',
            dataSrc: function (json) { return json.data || []; },
            data: function () {
                return { action: 'list', estado: '1', desde: fpDesde.input.value, hasta: fpHasta.input.value };
            }
        },
        language: { url: window.DT_LANG_URL },
        order: [[9, 'desc']],
        pageLength: 25,
        columns: columnasBase(),
    });

    // ── Resumen ──────────────────────────────────────────────────────────────

    function actualizarResumen(data) {
        if (!data || !data.length) {
            $('#resumen_ventas').hide();
            return;
        }
        var total = data.reduce(function (s, r) { return s + parseFloat(r.total || 0); }, 0);
        $('#res_count').text(data.length);
        $('#res_total').text(fmtMXN(total));
        $('#resumen_ventas').show();
    }

    // ── Buscar ───────────────────────────────────────────────────────────────

    $('#btn_buscar').on('click', function () {
        var desde = fpDesde.input.value;
        var hasta = fpHasta.input.value;
        if (!desde || !hasta) {
            mostrarError('Selecciona el rango de fechas para buscar.');
            return;
        }
        tablaActivas.ajax.reload(null, false);
        tablaCanceladas.ajax.reload(null, false);
    });

    // ── Ticket ───────────────────────────────────────────────────────────────

    $(document).on('click', '.btn-ver-ticket', function () {
        var id = $(this).data('id');
        window.open(APP_URL_HV + '/views/tickets/venta_ticket.php?id=' + id, '_blank');
    });
});
