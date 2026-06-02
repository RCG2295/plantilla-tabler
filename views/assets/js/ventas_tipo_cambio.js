$(function () {
    var ajax_url = APP_URL_TC + '/views/ajax/ajax_ventas_tipo_cambio.php';
    var tabla;

    function fmt(n) { return '$' + parseFloat(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function fmtDate(s) { if (!s) return '—'; var d = new Date(s.replace(' ', 'T')); return d.toLocaleString('es-MX'); }

    function cargarVigente() {
        $.getJSON(ajax_url + '?action=vigente', function (res) {
            if (res.status === 'ok' && res.data) {
                $('#tc_valor_vigente').text(fmt(res.data.valor));
                $('#tc_fecha_vigente').text(fmtDate(res.data.fecha_alta));
            } else {
                $('#tc_valor_vigente').text('Sin registrar');
            }
        });
    }

    function initTabla() {
        tabla = $('#tabla_tipo_cambio').DataTable({
            language: { url: window.DT_LANG_URL },
            order: [[0, 'desc']],
            columns: [
                { data: 'id' },
                { data: 'valor', render: function (v) { return fmt(v); } },
                { data: 'usuario', defaultContent: '—' },
                { data: 'fecha_alta', render: fmtDate },
            ],
        });
    }

    function cargarTabla() {
        $.getJSON(ajax_url + '?action=list', function (res) {
            if (res.status === 'ok') {
                tabla.clear().rows.add(res.data).draw();
            }
        });
    }

    initTabla();
    cargarVigente();
    cargarTabla();

    if (PERMISOS_TC.crear) {
        $('#btn_nuevo_tc').on('click', function () {
            $('#tc_valor').val('');
            var modal = new bootstrap.Modal(document.getElementById('modal_tc'));
            modal.show();
        });

        $('#form_tc').on('submit', function (e) {
            e.preventDefault();
            $.post(ajax_url, { action: 'save', valor: $('#tc_valor').val() }, function (res) {
                if (res.status === 'ok') {
                    bootstrap.Modal.getInstance(document.getElementById('modal_tc')).hide();
                    cargarVigente();
                    cargarTabla();
                    mostrarExito(res.message);
                } else {
                    mostrarError(res.message);
                }
            }, 'json');
        });
    }
});
