$(document).ready(function () {

    var app_url      = $('meta[name="app-url"]').attr('content');
    var ajax_url     = app_url + '/views/ajax/ajax_admin_usuarios.php';
    var uploads_url  = app_url + '/views/uploads/admin_usuarios/';
    var ajax_roles   = app_url + '/views/ajax/ajax_cfg_roles.php';
    var ajax_suc     = app_url + '/views/ajax/ajax_admin_sucursales.php';
    var tabla;
    var modal        = new bootstrap.Modal(document.getElementById('modal_usuario'));
    var permisos     = window.PERMISOS_USUARIOS || { crear: false, editar: false, eliminar: false };

    // ── Tom Select ─────────────────────────────────────────────────────────
    var tsRol    = new TomSelect('#usuario_id_rol',  { create: false });
    var tsEstado = new TomSelect('#usuario_estado',  { create: false });
    var tsSucursal;

    // Load roles
    $.getJSON(ajax_roles + '?action=select', function (res) {
        if (res.status !== 'ok') return;
        res.data.forEach(function (r) {
            tsRol.addOption({ value: r.id, text: r.nombre });
        });
        tsRol.refreshOptions(false);
    });

    // Load sucursales — init TomSelect after data arrives so options render correctly
    $.getJSON(ajax_suc + '?action=list_select', function (res) {
        var opts = [];
        if (res.status === 'ok' && res.data) {
            res.data.forEach(function (s) {
                opts.push({ value: String(s.id), text: s.nombre });
            });
        }
        tsSucursal = new TomSelect('#usuario_id_sucursal', {
            create: false,
            options: opts,
            valueField: 'value',
            labelField: 'text',
            placeholder: 'Seleccionar sucursal...'
        });
    });

    // ── DataTables ─────────────────────────────────────────────────────────
    tabla = $('#tabla_usuarios').DataTable({
        ajax: {
            url: ajax_url + '?action=list',
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id', width: '60px' },
            {
                data: 'imagen',
                orderable: false,
                width: '48px',
                render: function (data) {
                    if (data) {
                        return '<img src="' + uploads_url + data + '" style="width:36px;height:36px;object-fit:cover;border-radius:50%;">';
                    }
                    return '<div style="width:36px;height:36px;border-radius:50%;background:var(--color-primario);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.8rem;">?</div>';
                }
            },
            { data: 'nombre' },
            { data: 'apellidos' },
            { data: 'email' },
            {
                data: 'rol_nombre',
                render: function (data) {
                    return data
                        ? '<span class="badge" style="background:var(--color-primario);color:#fff;">' + data + '</span>'
                        : '<span class="text-muted">—</span>';
                }
            },
            {
                data: 'telefono',
                render: function (data) {
                    return data || '<span class="text-muted">—</span>';
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
        language: {
            url: window.DT_LANG_URL
        },
        pageLength: 10,
        order: [[0, 'desc']],
        responsive: true
    });

    // ── Nuevo usuario ───────────────────────────────────────────────────────
    $('#btn_nuevo_usuario').on('click', function () {
        limpiarFormulario();
        $('#modal_usuario_titulo').text('Nuevo usuario');
        $('#password_hint').hide();
        $('#usuario_password').prop('required', true);
        $('#usuario_password_confirmar').prop('required', true);
        modal.show();
    });

    // ── Editar usuario ──────────────────────────────────────────────────────
    $('#tabla_usuarios').on('click', '.btn-editar', function () {
        var id = $(this).data('id');

        $.ajax({
            url: ajax_url,
            type: 'GET',
            data: { action: 'get', id: id },
            dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') { mostrarError(res.message); return; }
                var u = res.data;

                limpiarFormulario();
                $('#modal_usuario_titulo').text('Editar usuario');
                $('#password_hint').show();
                $('#usuario_password').prop('required', false);
                $('#usuario_password_confirmar').prop('required', false);

                $('#usuario_id').val(u.id);
                $('#usuario_nombre').val(u.nombre);
                $('#usuario_apellidos').val(u.apellidos);
                $('#usuario_email').val(u.email);
                $('#usuario_telefono').val(u.telefono || '');
                tsRol.setValue(String(u.id_rol));
                tsEstado.setValue(String(u.estado));
                if (tsSucursal) tsSucursal.setValue(u.id_sucursal ? String(u.id_sucursal) : '');

                if (u.imagen) {
                    $('#usuario_imagen_preview').attr('src', uploads_url + u.imagen);
                    $('#usuario_imagen_actual').removeClass('d-none');
                }

                modal.show();
            }
        });
    });

    // ── Submit formulario ──────────────────────────────────────────────────
    $('#form_usuario').on('submit', function (e) {
        e.preventDefault();

        if (!validarCamposRequeridos('#form_usuario')) {
            mostrarError('Completa los campos requeridos.');
            return;
        }

        var email = $('#usuario_email').val().trim();
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            mostrarError('El formato del email no es válido.');
            return;
        }

        if (!tsSucursal || !tsSucursal.getValue()) {
            mostrarError('Selecciona una sucursal.');
            return;
        }

        var pass    = $('#usuario_password').val();
        var passCon = $('#usuario_password_confirmar').val();
        if (pass || passCon) {
            if (pass !== passCon) {
                mostrarError('Las contraseñas no coinciden.');
                $('#usuario_password_confirmar').addClass('is-invalid');
                return;
            }
        }

        var btn = $('#btn_guardar_usuario');
        btn.prop('disabled', true).text('Guardando...');

        var formData = new FormData(document.getElementById('form_usuario'));
        formData.append('action', 'add');

        $.ajax({
            url: ajax_url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.status === 'ok') {
                    modal.hide();
                    mostrarExito(res.message);
                    tabla.ajax.reload(null, false);
                } else {
                    mostrarError(res.message);
                }
                btn.prop('disabled', false).text('Guardar');
            },
            error: function () {
                mostrarError('Error de conexión. Intenta nuevamente.');
                btn.prop('disabled', false).text('Guardar');
            }
        });
    });

    // ── Eliminar usuario ───────────────────────────────────────────────────
    $('#tabla_usuarios').on('click', '.btn-eliminar', function () {
        var id = $(this).data('id');

        confirmarEliminacion('El usuario será marcado como eliminado.').then(function (result) {
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

    // ── Helpers ────────────────────────────────────────────────────────────
    function limpiarFormulario() {
        $('#form_usuario')[0].reset();
        $('#usuario_id').val('');
        $('#form_usuario .is-invalid').removeClass('is-invalid');
        $('#usuario_imagen_actual').addClass('d-none');
        $('#usuario_imagen_preview').attr('src', '');
        tsRol.setValue('');
        tsEstado.setValue('0');
        if (tsSucursal) tsSucursal.clear();
    }

});
