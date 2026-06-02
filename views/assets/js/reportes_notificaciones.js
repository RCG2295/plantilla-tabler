$(document).ready(function () {

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_reportes_notificaciones.php';

    var tabla = $('#tabla_notificaciones').DataTable({
        ajax: {
            url: ajax_url + '?action=list',
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id', width: '55px' },
            { data: 'titulo' },
            {
                data: 'mensaje',
                render: function (data) {
                    var short = data.length > 80 ? data.substring(0, 80) + '…' : data;
                    return '<span title="' + $('<div>').text(data).html() + '">'
                        + $('<div>').text(short).html()
                        + '</span>';
                }
            },
            {
                data: 'enviada_por',
                render: function (data) {
                    return data || '<span class="text-muted">—</span>';
                }
            },
            { data: 'destinatarios' },
            {
                data: 'fecha_alta',
                render: function (data) {
                    if (!data) return '<span class="text-muted">—</span>';
                    var d = new Date(data.replace(' ', 'T'));
                    return d.toLocaleDateString('es-MX') + ' '
                        + d.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
                }
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (id) {
                    return '<button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="' + id + '" title="Eliminar">'
                        + '<i class="ti ti-trash"></i></button>';
                }
            }
        ],
        language: {
            url: window.DT_LANG_URL
        },
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });

    $('#tabla_notificaciones').on('click', '.btn-eliminar', function () {
        var id = $(this).data('id');

        confirmarEliminacion('Se eliminará esta notificación del historial.').then(function (result) {
            if (!result.isConfirmed) return;

            ajaxPost({
                url: ajax_url,
                data: { action: 'delete', id: id },
                onSuccess: function (res) {
                    mostrarExito(res.message);
                    tabla.ajax.reload(null, false);
                }
            });
        });
    });

});
