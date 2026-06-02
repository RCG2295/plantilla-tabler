$(document).ready(function () {

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_cfg_areas.php';
    var tabla;
    var modal    = new bootstrap.Modal(document.getElementById('modal_area'));
    var permisos = window.PERMISOS_AREAS || { crear: false, editar: false, eliminar: false };

    var tsEstado = new TomSelect('#area_estado', { create: false });

    // ── Preview ícono en tiempo real ─────────────────────────────────────────
    $('#area_icono').on('input', function () {
        var val = $(this).val().trim();
        $('#area_icono_preview').attr('class', val || 'ti ti-circle');
    });

    // ── DataTables ─────────────────────────────────────────────────────────
    tabla = $('#tabla_areas').DataTable({
        ajax: {
            url: ajax_url + '?action=list',
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id', width: '60px' },
            { data: 'nombre' },
            {
                data: 'icono',
                orderable: false,
                render: function (data) {
                    return '<i class="' + data + ' me-1"></i><code class="text-muted">' + data + '</code>';
                }
            },
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
        order: [[3, 'asc']],
        responsive: true
    });

    // ── Nueva área ──────────────────────────────────────────────────────────
    $('#btn_nueva_area').on('click', function () {
        limpiarFormulario();
        $('#modal_area_titulo').text('Nueva área');
        modal.show();
    });

    // ── Editar ──────────────────────────────────────────────────────────────
    $('#tabla_areas').on('click', '.btn-editar', function () {
        var id = $(this).data('id');
        $.ajax({
            url: ajax_url,
            type: 'GET',
            data: { action: 'get', id: id },
            dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') { mostrarError(res.message); return; }
                var a = res.data;
                limpiarFormulario();
                $('#modal_area_titulo').text('Editar área');
                $('#area_id').val(a.id);
                $('#area_nombre').val(a.nombre);
                $('#area_icono').val(a.icono).trigger('input');
                $('#area_orden').val(a.orden);
                tsEstado.setValue(String(a.estado));
                modal.show();
            }
        });
    });

    // ── Submit ──────────────────────────────────────────────────────────────
    $('#form_area').on('submit', function (e) {
        e.preventDefault();

        if (!validarCamposRequeridos('#form_area')) {
            mostrarError('Completa los campos requeridos.');
            return;
        }

        var btn = $('#btn_guardar_area');
        btn.prop('disabled', true).text('Guardando...');

        var data = {
            action: 'save',
            id:     $('#area_id').val(),
            nombre: $('#area_nombre').val().trim(),
            icono:  $('#area_icono').val().trim(),
            orden:  $('#area_orden').val(),
            estado: $('#area_estado').val()
        };

        ajaxPost({
            url: ajax_url,
            data: data,
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
    $('#tabla_areas').on('click', '.btn-eliminar', function () {
        var id = $(this).data('id');
        confirmarEliminacion('El área será marcada como eliminada.').then(function (result) {
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
        $('#form_area')[0].reset();
        $('#area_id').val('');
        $('#form_area .is-invalid').removeClass('is-invalid');
        $('#area_icono_preview').attr('class', 'ti ti-circle');
        tsEstado.setValue('0');
    }

});
