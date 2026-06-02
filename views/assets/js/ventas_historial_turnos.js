$(function () {
    var ajax_url = APP_URL_HIST + '/views/ajax/ajax_ventas_historial_turnos.php';
    var tabla;
    var fpDesde, fpHasta;

    function fmtMXN(n) { return '$' + parseFloat(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function fmtDate(s) { if (!s) return '—'; var d = new Date(s.replace(' ', 'T')); return d.toLocaleString('es-MX'); }

    var hoy30t   = new Date();
    var desde30t = new Date();
    desde30t.setDate(hoy30t.getDate() - 30);

    // ── Flatpickr ────────────────────────────────────────────────────────────

    fpDesde = flatpickr('#filtro_desde', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        defaultDate: desde30t,
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

    // ── DataTable ────────────────────────────────────────────────────────────

    tabla = $('#tabla_historial_turnos').DataTable({
        ajax: {
            url: ajax_url,
            type: 'GET',
            dataSrc: 'data',
            data: function () {
                return {
                    action: 'list',
                    desde:  fpDesde.input.value,
                    hasta:  fpHasta.input.value
                };
            }
        },
        language: { url: window.DT_LANG_URL },
        order: [[0, 'desc']],
        pageLength: 25,
        columns: [
            { data: 'id' },
            { data: null, render: function (r) { return r.usuario_nombre + ' ' + r.usuario_apellidos; } },
            { data: 'sucursal_nombre', defaultContent: '—' },
            { data: 'fondo_pesos',   render: function (v) { return fmtMXN(v); } },
            { data: 'fondo_dolares', render: function (v) { return '$' + parseFloat(v || 0).toFixed(2); } },
            { data: 'fecha_alta',   render: fmtDate },
            { data: 'fecha_cierre', render: fmtDate },
            { data: 'estado', render: function (v) {
                if (v == 0) return '<span class="badge" style="background:var(--color-exito);color:#fff;">Activo</span>';
                if (v == 1) return '<span class="badge bg-secondary" style="color:#fff;">Cerrado</span>';
                return '<span class="badge bg-danger" style="color:#fff;">Eliminado</span>';
            }},
            { data: null, className: 'text-center', orderable: false, render: function (r) {
                if (r.estado == 1) {
                    return '<button class="btn btn-sm btn-outline-secondary btn-ver-corte" data-id="' + r.id + '" title="Imprimir corte"><i class="ti ti-printer"></i></button>';
                }
                return '';
            }},
        ],
    });

    // ── Buscar ───────────────────────────────────────────────────────────────

    $('#btn_buscar').on('click', function () {
        var desde = fpDesde.input.value;
        var hasta = fpHasta.input.value;
        if (!desde || !hasta) {
            mostrarError('Selecciona el rango de fechas para buscar.');
            return;
        }
        tabla.ajax.reload(null, false);
    });

    // ── Imprimir corte ───────────────────────────────────────────────────────

    $(document).on('click', '.btn-ver-corte', function () {
        var id = $(this).data('id');
        window.open(APP_URL_HIST + '/views/tickets/corte_turno_ticket.php?id=' + id, '_blank');
    });
});
