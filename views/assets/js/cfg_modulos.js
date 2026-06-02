$(document).ready(function () {

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_cfg_modulos.php';
    var tabla;
    var modal    = new bootstrap.Modal(document.getElementById('modal_modulo'));
    var permisos = window.PERMISOS_MODULOS || { crear: false, editar: false, eliminar: false };

    var tsArea   = new TomSelect('#modulo_id_area', { create: false });
    var tsEstado = new TomSelect('#modulo_estado',   { create: false });

    // Load areas into select
    $.getJSON(ajax_url + '?action=areas_select', function (res) {
        if (res.status !== 'ok') return;
        res.data.forEach(function (a) {
            tsArea.addOption({ value: a.id, text: a.nombre });
        });
        tsArea.refreshOptions(false);
    });

    // ── DataTables ─────────────────────────────────────────────────────────
    tabla = $('#tabla_modulos').DataTable({
        ajax: {
            url: ajax_url + '?action=list',
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id', width: '60px' },
            { data: 'area_nombre' },
            {
                data: 'clave',
                render: function (data) {
                    return '<code>' + data + '</code>';
                }
            },
            { data: 'nombre' },
            { data: 'orden', width: '80px' },
            {
                data: 'estado',
                render: function (data) {
                    if (data == 0) return '<span class="badge" style="background:var(--color-exito);color:#fff;">Activo</span>';
                    if (data == 1) return '<span class="badge bg-secondary" style="color:#fff;">Inactivo</span>';
                    return '<span class="badge bg-danger" style="color:#fff;">Eliminado</span>';
                }
            },
            {
                data: 'id',
                orderable: false,
                className: 'text-center',
                render: function (id) {
                    var html = '';
                    if (permisos.editar) {
                        html += '<button class="btn btn-sm btn-outline-primary me-1 btn-editar" data-id="' + id + '" title="Editar">'
                            + '<i class="ti ti-pencil"></i></button>';
                    }
                    if (permisos.eliminar) {
                        html += '<button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="' + id + '" title="Eliminar">'
                            + '<i class="ti ti-trash"></i></button>';
                    }
                    return html || '<span class="text-muted">—</span>';
                }
            }
        ],
        language: { url: window.DT_LANG_URL },
        pageLength: 10,
        order: [[1, 'asc'], [4, 'asc']],
        responsive: true
    });

    // ── Nuevo módulo ────────────────────────────────────────────────────────
    $('#btn_nuevo_modulo').on('click', function () {
        limpiarFormulario();
        $('#modal_modulo_titulo').text('Nuevo módulo');
        modal.show();
    });

    // ── Editar ──────────────────────────────────────────────────────────────
    $('#tabla_modulos').on('click', '.btn-editar', function () {
        var id = $(this).data('id');
        $.ajax({
            url: ajax_url,
            type: 'GET',
            data: { action: 'get', id: id },
            dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') { mostrarError(res.message); return; }
                var m = res.data;
                limpiarFormulario();
                $('#modal_modulo_titulo').text('Editar módulo');
                $('#modulo_id').val(m.id);
                tsArea.setValue(String(m.id_area));
                $('#modulo_clave').val(m.clave);
                $('#modulo_nombre').val(m.nombre);
                $('#modulo_icono').val(m.icono || '');
                $('#modulo_orden').val(m.orden);
                tsEstado.setValue(String(m.estado));
                modal.show();
            }
        });
    });

    // ── Submit ──────────────────────────────────────────────────────────────
    $('#form_modulo').on('submit', function (e) {
        e.preventDefault();

        if (!validarCamposRequeridos('#form_modulo')) {
            mostrarError('Completa los campos requeridos.');
            return;
        }

        var btn = $('#btn_guardar_modulo');
        btn.prop('disabled', true).text('Guardando...');

        ajaxPost({
            url: ajax_url,
            data: {
                action:   'save',
                id:       $('#modulo_id').val(),
                id_area:  $('#modulo_id_area').val(),
                clave:    $('#modulo_clave').val().trim(),
                nombre:   $('#modulo_nombre').val().trim(),
                icono:    $('#modulo_icono').val().trim(),
                orden:    $('#modulo_orden').val(),
                estado:   $('#modulo_estado').val()
            },
            onSuccess: function (res) {
                modal.hide();
                mostrarExito(res.message);
                tabla.ajax.reload(null, false);
            },
            onError: function (res) {
                mostrarError(res.message);
            },
            onComplete: function () {
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });

    // ── Eliminar ────────────────────────────────────────────────────────────
    $('#tabla_modulos').on('click', '.btn-eliminar', function () {
        var id = $(this).data('id');
        confirmarEliminacion('El módulo será marcado como eliminado.').then(function (result) {
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

    function limpiarFormulario() {
        $('#form_modulo')[0].reset();
        $('#modulo_id').val('');
        $('#form_modulo .is-invalid').removeClass('is-invalid');
        tsArea.setValue('');
        tsEstado.setValue('0');
    }

});
