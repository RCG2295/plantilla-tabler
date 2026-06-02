$(document).ready(function () {

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_inventario_unidades.php';
    var tabla;
    var modal    = new bootstrap.Modal(document.getElementById('modal_unidad'));
    var permisos = window.PERMISOS_UNIDADES || { crear: false, editar: false, eliminar: false };

    var tsEstado = new TomSelect('#unidad_estado', { create: false });

    // ── DataTables ──────────────────────────────────────────────────────────
    tabla = $('#tabla_unidades').DataTable({
        ajax: { url: ajax_url + '?action=list', type: 'GET', dataSrc: 'data' },
        columns: [
            { data: 'id', width: '60px' },
            { data: 'nombre' },
            {
                data: 'abreviatura',
                render: function (d) { return '<code>' + d + '</code>'; }
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
        order: [[1, 'asc']],
        responsive: true
    });

    // ── Nueva unidad ────────────────────────────────────────────────────────
    $('#btn_nueva_unidad').on('click', function () {
        limpiarFormulario();
        $('#modal_unidad_titulo').text('Nueva unidad');
        modal.show();
    });

    // ── Editar ──────────────────────────────────────────────────────────────
    $('#tabla_unidades').on('click', '.btn-editar', function () {
        var id = $(this).data('id');
        $.ajax({
            url: ajax_url, type: 'GET',
            data: { action: 'get', id: id }, dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') { mostrarError(res.message); return; }
                var u = res.data;
                limpiarFormulario();
                $('#modal_unidad_titulo').text('Editar unidad');
                $('#unidad_id').val(u.id);
                $('#unidad_nombre').val(u.nombre);
                $('#unidad_abreviatura').val(u.abreviatura);
                tsEstado.setValue(String(u.estado));
                modal.show();
            }
        });
    });

    // ── Submit ──────────────────────────────────────────────────────────────
    $('#form_unidad').on('submit', function (e) {
        e.preventDefault();
        if (!validarCamposRequeridos('#form_unidad')) { mostrarError('Completa los campos requeridos.'); return; }

        var btn = $('#btn_guardar_unidad').prop('disabled', true).text('Guardando...');
        ajaxPost({
            url: ajax_url,
            data: { action: 'save', id: $('#unidad_id').val(), nombre: $('#unidad_nombre').val().trim(), abreviatura: $('#unidad_abreviatura').val().trim(), estado: tsEstado.getValue() },
            onSuccess: function (res) { modal.hide(); mostrarExito(res.message); tabla.ajax.reload(null, false); },
            onError:   function (res) { mostrarError(res.message); },
            onComplete: function ()   { btn.prop('disabled', false).text('Guardar'); }
        });
    });

    // ── Eliminar ────────────────────────────────────────────────────────────
    $('#tabla_unidades').on('click', '.btn-eliminar', function () {
        var id = $(this).data('id');
        confirmarEliminacion('La unidad será marcada como eliminada.').then(function (result) {
            if (!result.isConfirmed) return;
            ajaxPost({
                url: ajax_url, data: { action: 'delete', id: id },
                onSuccess: function (res) { mostrarExito(res.message); tabla.ajax.reload(null, false); },
                onError:   function (res) { mostrarError(res.message); }
            });
        });
    });

    function limpiarFormulario() {
        $('#form_unidad')[0].reset();
        $('#unidad_id').val('');
        tsEstado.setValue('0');
    }

});
