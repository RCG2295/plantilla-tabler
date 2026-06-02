$(document).ready(function () {

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_cfg_perfil.php';
    var imagenActual = null;

    // ── Cargar datos del perfil ─────────────────────────────────────────────
    $.getJSON(ajax_url + '?action=get', function (res) {
        if (res.status !== 'ok') return;
        var u = res.data;
        imagenActual = u.imagen || null;
        $('#perfil_nombre').val(u.nombre || '');
        $('#perfil_apellidos').val(u.apellidos || '');
        $('#perfil_email').val(u.email || '');
        $('#perfil_telefono').val(u.telefono || '');
        mostrarAvatar(u.imagen, u.nombre, u.apellidos);
    });

    function mostrarAvatar(imagen, nombre, apellidos) {
        if (imagen) {
            $('#avatar_img')
                .attr('src', app_url + '/views/uploads/admin_usuarios/' + imagen)
                .show();
            $('#avatar_placeholder').hide();
        } else {
            var ini = ((nombre || '').charAt(0) + (apellidos || '').charAt(0)).toUpperCase();
            $('#avatar_initials').text(ini || '?');
            $('#avatar_img').hide();
            $('#avatar_placeholder').show();
        }
    }

    // ── Preview de foto ─────────────────────────────────────────────────────
    $('#perfil_imagen').on('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#avatar_img').attr('src', e.target.result).show();
            $('#avatar_placeholder').hide();
        };
        reader.readAsDataURL(file);
    });

    // ── Toggle visibilidad de contraseña ────────────────────────────────────
    $(document).on('click', '.btn-toggle-pw', function () {
        var id    = $(this).data('target');
        var $inp  = $('#' + id);
        var $icon = $(this).find('i');
        if ($inp.attr('type') === 'password') {
            $inp.attr('type', 'text');
            $icon.removeClass('ti-eye').addClass('ti-eye-off');
        } else {
            $inp.attr('type', 'password');
            $icon.removeClass('ti-eye-off').addClass('ti-eye');
        }
    });

    // ── Guardar informacion personal ────────────────────────────────────────
    $('#form_info').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#btn_guardar_info').prop('disabled', true).html('<i class="ti ti-loader me-1"></i>Guardando...');

        var fd = new FormData(this);
        fd.append('action', 'update_info');

        $.ajax({
            url: ajax_url, type: 'POST',
            data: fd, processData: false, contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') { mostrarError(res.message); return; }
                mostrarExito(res.message);
                if (res.imagen) imagenActual = res.imagen;
                mostrarAvatar(imagenActual, res.nombre, res.apellidos);
            },
            error: function () { mostrarError('Error de conexión.'); },
            complete: function () {
                btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Guardar cambios');
            }
        });
    });

    // ── Cambiar contraseña ──────────────────────────────────────────────────
    $('#form_password').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#btn_cambiar_pw').prop('disabled', true).html('<i class="ti ti-loader me-1"></i>Actualizando...');

        ajaxPost({
            url: ajax_url,
            data: {
                action:                  'update_password',
                password_actual:         $('#pw_actual').val(),
                password_nueva:          $('#pw_nueva').val(),
                password_confirmacion:   $('#pw_confirmacion').val()
            },
            onSuccess: function (res) {
                mostrarExito(res.message);
                $('#form_password')[0].reset();
                $('#form_password input[type="password"]').attr('type', 'password');
                $('#form_password .btn-toggle-pw i').removeClass('ti-eye-off').addClass('ti-eye');
            },
            onError: function (res) { mostrarError(res.message); },
            onComplete: function () {
                btn.prop('disabled', false).html('<i class="ti ti-lock me-1"></i>Actualizar contraseña');
            }
        });
    });

});
