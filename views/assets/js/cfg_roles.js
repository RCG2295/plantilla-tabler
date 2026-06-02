$(document).ready(function () {

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_cfg_roles.php';
    var tabla;
    var modalRol      = new bootstrap.Modal(document.getElementById('modal_rol'));
    var modalPermisos = new bootstrap.Modal(document.getElementById('modal_permisos'));
    var permisos      = window.PERMISOS_ROLES || { crear: false, editar: false, eliminar: false };

    var tsEstado = new TomSelect('#rol_estado', { create: false });

    // ── DataTables ─────────────────────────────────────────────────────────
    tabla = $('#tabla_roles').DataTable({
        ajax: {
            url: ajax_url + '?action=list',
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id', width: '60px' },
            { data: 'nombre' },
            {
                data: 'descripcion',
                render: function (data) {
                    return data || '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'es_superadmin',
                className: 'text-center',
                render: function (data) {
                    return data == 1
                        ? '<span class="badge" style="background:var(--color-primario);color:#fff;">Sí</span>'
                        : '<span class="badge bg-secondary" style="color:#fff;">No</span>';
                }
            },
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
                        html += '<button class="btn btn-sm btn-outline-secondary me-1 btn-permisos" data-id="' + id + '" title="Permisos">'
                            + '<i class="ti ti-lock"></i></button>';
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
        order: [[0, 'asc']],
        responsive: true
    });

    // ── Nuevo rol ───────────────────────────────────────────────────────────
    $('#btn_nuevo_rol').on('click', function () {
        limpiarFormulario();
        $('#modal_rol_titulo').text('Nuevo rol');
        modalRol.show();
    });

    // ── Editar rol ──────────────────────────────────────────────────────────
    $('#tabla_roles').on('click', '.btn-editar', function () {
        var id = $(this).data('id');
        $.ajax({
            url: ajax_url,
            type: 'GET',
            data: { action: 'get', id: id },
            dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') { mostrarError(res.message); return; }
                var r = res.data;
                limpiarFormulario();
                $('#modal_rol_titulo').text('Editar rol');
                $('#rol_id').val(r.id);
                $('#rol_nombre').val(r.nombre);
                $('#rol_descripcion').val(r.descripcion || '');
                $('#rol_es_superadmin').prop('checked', r.es_superadmin == 1);
                tsEstado.setValue(String(r.estado));
                modalRol.show();
            }
        });
    });

    // ── Submit rol ──────────────────────────────────────────────────────────
    $('#form_rol').on('submit', function (e) {
        e.preventDefault();

        if (!validarCamposRequeridos('#form_rol')) {
            mostrarError('Completa los campos requeridos.');
            return;
        }

        var btn = $('#btn_guardar_rol');
        btn.prop('disabled', true).text('Guardando...');

        ajaxPost({
            url: ajax_url,
            data: {
                action:        'save',
                id:            $('#rol_id').val(),
                nombre:        $('#rol_nombre').val().trim(),
                descripcion:   $('#rol_descripcion').val().trim(),
                es_superadmin: $('#rol_es_superadmin').is(':checked') ? '1' : '0',
                estado:        $('#rol_estado').val()
            },
            onSuccess: function (res) {
                modalRol.hide();
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

    // ── Eliminar rol ────────────────────────────────────────────────────────
    $('#tabla_roles').on('click', '.btn-eliminar', function () {
        var id = $(this).data('id');
        confirmarEliminacion('El rol será marcado como eliminado.').then(function (result) {
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

    // ── Abrir matriz de permisos ────────────────────────────────────────────
    $('#tabla_roles').on('click', '.btn-permisos', function () {
        var id_rol = $(this).data('id');
        var nombre = $(this).closest('tr').find('td:nth-child(2)').text();

        $('#modal_permisos_titulo').text('Permisos: ' + nombre);
        $('#btn_guardar_permisos').data('rol-id', id_rol);
        $('#permisos_loading').removeClass('d-none');
        $('#permisos_contenido').addClass('d-none');
        $('#permisos_tbody').empty();

        modalPermisos.show();

        $.ajax({
            url: ajax_url,
            type: 'GET',
            data: { action: 'get_permisos', id_rol: id_rol },
            dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') {
                    mostrarError(res.message);
                    modalPermisos.hide();
                    return;
                }
                renderizarMatriz(res.data);
                $('#permisos_loading').addClass('d-none');
                $('#permisos_contenido').removeClass('d-none');
            }
        });
    });

    // ── Guardar permisos ────────────────────────────────────────────────────
    $('#btn_guardar_permisos').on('click', function () {
        var id_rol   = $(this).data('rol-id');
        var permisos = [];

        $('#permisos_tbody tr[data-modulo-id]').each(function () {
            var mid = $(this).data('modulo-id');
            permisos.push({
                id_modulo: mid,
                ver:       $(this).find('.cb-ver').is(':checked') ? 1 : 0,
                crear:     $(this).find('.cb-crear').is(':checked') ? 1 : 0,
                editar:    $(this).find('.cb-editar').is(':checked') ? 1 : 0,
                eliminar:  $(this).find('.cb-eliminar').is(':checked') ? 1 : 0
            });
        });

        var btn = $(this);
        btn.prop('disabled', true).text('Guardando...');

        ajaxPost({
            url: ajax_url,
            data: {
                action:   'save_permisos',
                id_rol:   id_rol,
                permisos: JSON.stringify(permisos)
            },
            onSuccess: function (res) {
                modalPermisos.hide();
                mostrarExito(res.message);
            },
            onError: function (res) {
                mostrarError(res.message);
            },
            onComplete: function () {
                btn.prop('disabled', false).text('Guardar permisos');
            }
        });
    });

    // ── Lógica crear/editar/eliminar implica ver ────────────────────────────
    $('#permisos_tbody').on('change', '.cb-crear, .cb-editar, .cb-eliminar', function () {
        var mid = $(this).data('mid');
        if ($(this).is(':checked')) {
            $('#permisos_tbody').find('.cb-ver[data-mid="' + mid + '"]').prop('checked', true);
        }
    });

    $('#permisos_tbody').on('change', '.cb-ver', function () {
        var mid = $(this).data('mid');
        if (!$(this).is(':checked')) {
            $('#permisos_tbody').find('[data-mid="' + mid + '"].cb-crear, [data-mid="' + mid + '"].cb-editar, [data-mid="' + mid + '"].cb-eliminar')
                .prop('checked', false);
        }
    });

    // ── Helpers ────────────────────────────────────────────────────────────
    function renderizarMatriz(areas) {
        var tbody = $('#permisos_tbody');
        tbody.empty();

        areas.forEach(function (area) {
            // Area header row
            tbody.append(
                '<tr class="table-secondary">'
                + '<td colspan="5" class="fw-semibold py-2">'
                + '<i class="ti ti-folder me-1"></i>' + $('<span>').text(area.nombre).html()
                + '</td></tr>'
            );

            area.modulos.forEach(function (mod) {
                tbody.append(
                    '<tr data-modulo-id="' + mod.id + '">'
                    + '<td class="ps-4">' + $('<span>').text(mod.nombre).html()
                    + ' <small class="text-muted">(' + $('<span>').text(mod.clave).html() + ')</small></td>'
                    + '<td class="text-center"><input type="checkbox" class="form-check-input cb-ver"     data-mid="' + mod.id + '" ' + (mod.ver      ? 'checked' : '') + '></td>'
                    + '<td class="text-center"><input type="checkbox" class="form-check-input cb-crear"   data-mid="' + mod.id + '" ' + (mod.crear    ? 'checked' : '') + '></td>'
                    + '<td class="text-center"><input type="checkbox" class="form-check-input cb-editar"  data-mid="' + mod.id + '" ' + (mod.editar   ? 'checked' : '') + '></td>'
                    + '<td class="text-center"><input type="checkbox" class="form-check-input cb-eliminar"data-mid="' + mod.id + '" ' + (mod.eliminar ? 'checked' : '') + '></td>'
                    + '</tr>'
                );
            });
        });
    }

    function limpiarFormulario() {
        $('#form_rol')[0].reset();
        $('#rol_id').val('');
        $('#form_rol .is-invalid').removeClass('is-invalid');
        $('#rol_es_superadmin').prop('checked', false);
        tsEstado.setValue('0');
    }

});
