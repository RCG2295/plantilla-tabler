$(document).ready(function () {

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_inventario_motivos.php';
    var tabla;
    var modal    = new bootstrap.Modal(document.getElementById('modal_motivo'));
    var permisos = window.PERMISOS_MOTIVOS || { crear: false, editar: false, eliminar: false };

    var tsTipo   = new TomSelect('#motivo_tipo',   { create: false });
    var tsEstado = new TomSelect('#motivo_estado', { create: false });

    var TIPO_LABELS = {
        entrada: '<span class="badge bg-green-lt text-green">Entrada</span>',
        salida:  '<span class="badge bg-red-lt text-red">Salida</span>',
        ambos:   '<span class="badge bg-blue-lt text-blue">Ambos</span>'
    };

    // ── DataTables ──────────────────────────────────────────────────────────
    tabla = $('#tabla_motivos').DataTable({
        ajax: { url: ajax_url + '?action=list', type: 'GET', dataSrc: 'data' },
        columns: [
            { data: 'id', width: '60px' },
            { data: 'nombre' },
            {
                data: 'tipo',
                render: function (d) { return TIPO_LABELS[d] || d; }
            },
            {
                data: 'estado',
                render: function (d) {
                    if (d == 0) return '<span class="badge" style="background:var(--color-exito);color:#fff;">Activo</span>';
                    if (d == 1) return '<span class="badge bg-secondary">Inactivo</span>';
                    return '<span class="badge bg-danger text-white">Eliminado</span>';
                }
            },
            {
                data: 'id', orderable: false, className: 'text-center',
                render: function (id) {
                    var html = '';
                    if (permisos.editar)   html += '<button class="btn btn-sm btn-outline-primary me-1 btn-editar" data-id="' + id + '" title="Editar"><i class="ti ti-pencil"></i></button>';
                    if (permisos.eliminar) html += '<button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="' + id + '" title="Eliminar"><i class="ti ti-trash"></i></button>';
                    return html || '<span class="text-muted">—</span>';
                }
            }
        ],
        language: { url: window.DT_LANG_URL },
        pageLength: 15,
        order: [[2, 'asc'], [1, 'asc']],
        responsive: true
    });

    // ── Nuevo motivo ────────────────────────────────────────────────────────
    $('#btn_nuevo_motivo').on('click', function () {
        limpiarFormulario();
        $('#modal_motivo_titulo').text('Nuevo motivo');
        modal.show();
    });

    // ── Editar ──────────────────────────────────────────────────────────────
    $('#tabla_motivos').on('click', '.btn-editar', function () {
        var id = $(this).data('id');
        $.ajax({
            url: ajax_url, type: 'GET',
            data: { action: 'get', id: id }, dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') { mostrarError(res.message); return; }
                var m = res.data;
                limpiarFormulario();
                $('#modal_motivo_titulo').text('Editar motivo');
                $('#motivo_id').val(m.id);
                $('#motivo_nombre').val(m.nombre);
                tsTipo.setValue(m.tipo);
                tsEstado.setValue(String(m.estado));
                modal.show();
            }
        });
    });

    // ── Submit ──────────────────────────────────────────────────────────────
    $('#form_motivo').on('submit', function (e) {
        e.preventDefault();
        if (!validarCamposRequeridos('#form_motivo')) { mostrarError('Completa los campos requeridos.'); return; }

        var btn = $('#btn_guardar_motivo').prop('disabled', true).text('Guardando...');
        ajaxPost({
            url: ajax_url,
            data: { action: 'save', id: $('#motivo_id').val(), nombre: $('#motivo_nombre').val().trim(), tipo: tsTipo.getValue(), estado: tsEstado.getValue() },
            onSuccess: function (res) { modal.hide(); mostrarExito(res.message); tabla.ajax.reload(null, false); },
            onError:   function (res) { mostrarError(res.message); },
            onComplete: function ()   { btn.prop('disabled', false).text('Guardar'); }
        });
    });

    // ── Eliminar ────────────────────────────────────────────────────────────
    $('#tabla_motivos').on('click', '.btn-eliminar', function () {
        var id = $(this).data('id');
        confirmarEliminacion('El motivo será marcado como eliminado.').then(function (result) {
            if (!result.isConfirmed) return;
            ajaxPost({
                url: ajax_url, data: { action: 'delete', id: id },
                onSuccess: function (res) { mostrarExito(res.message); tabla.ajax.reload(null, false); },
                onError:   function (res) { mostrarError(res.message); }
            });
        });
    });

    function limpiarFormulario() {
        $('#form_motivo')[0].reset();
        $('#motivo_id').val('');
        tsTipo.setValue('entrada');
        tsEstado.setValue('0');
    }

});
