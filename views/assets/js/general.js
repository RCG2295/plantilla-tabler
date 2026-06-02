/**
 * Funciones utilitarias reutilizables en todos los módulos.
 */

window.DT_LANG_URL = (document.querySelector('meta[name="app-url"]') || {}).content + '/node_modules/datatables.net-plugins/i18n/es-MX.json';

/**
 * Valida que los campos requeridos de un formulario no estén vacíos.
 * Devuelve true si todo está completo, false si falta algún campo.
 */
function validarCamposRequeridos(formSelector) {
    var valido = true;
    $(formSelector).find('[required]').each(function () {
        if ($.trim($(this).val()) === '') {
            $(this).addClass('is-invalid');
            valido = false;
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    return valido;
}

/**
 * Elimina el marcador de error al escribir en un campo.
 */
$(document).on('input', '.is-invalid', function () {
    $(this).removeClass('is-invalid');
});

/**
 * Muestra un toast de éxito con SweetAlert2.
 */
function mostrarExito(mensaje) {
    Swal.fire({
        icon: 'success',
        title: mensaje,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

/**
 * Muestra un toast de error con SweetAlert2.
 */
function mostrarError(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        confirmButtonColor: '#4f46e5'
    });
}

/**
 * Muestra un diálogo de confirmación antes de eliminar.
 * Devuelve una Promise (resultado de Swal.fire).
 */
function confirmarEliminacion(mensaje) {
    mensaje = mensaje || '¿Estás seguro? Esta acción no se puede deshacer.';
    return Swal.fire({
        title: '¿Eliminar registro?',
        text: mensaje,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });
}

/**
 * Dropdown de cambio de sucursal (solo superadmin).
 */
$(document).ready(function () {
    var $menu = $('#dropdown_sucursales_menu');
    if (!$menu.length) return;

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_suc = app_url + '/views/ajax/ajax_admin_sucursales.php';
    var loaded   = false;

    $('#btn_sucursal_dropdown').on('click', function () {
        if (loaded) return;
        loaded = true;
        $.getJSON(ajax_suc + '?action=for_select', function (res) {
            var $lista = $('#lista_sucursales_navbar').empty();
            if (res.status !== 'ok' || !res.data.length) {
                $lista.html('<div class="dropdown-item text-muted" style="font-size:0.82rem;">Sin sucursales disponibles</div>');
                return;
            }
            $.each(res.data, function (_, s) {
                $lista.append(
                    '<a href="#" class="dropdown-item btn-switch-sucursal" data-id="' + s.id + '" style="font-size:0.85rem;">' +
                    '<i class="ti ti-building me-2 text-muted"></i>' + $('<span>').text(s.nombre).html() +
                    '</a>'
                );
            });
        });
    });

    $(document).on('click', '.btn-switch-sucursal', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        $.post(ajax_suc, { action: 'switch', id: id }, function (res) {
            if (res.status === 'ok') {
                window.location.reload();
            } else {
                mostrarError(res.message || 'Error al cambiar de sucursal.');
            }
        }, 'json');
    });
});

/**
 * Envía un formulario via Ajax y ejecuta callbacks según resultado.
 * options: { url, data, onSuccess, onError }
 */
function ajaxPost(options) {
    $.ajax({
        url: options.url,
        type: 'POST',
        data: options.data,
        dataType: 'json',
        success: function (res) {
            if (res.status === 'ok') {
                if (typeof options.onSuccess === 'function') options.onSuccess(res);
            } else {
                if (typeof options.onError === 'function') {
                    options.onError(res);
                } else {
                    mostrarError(res.message || 'Error al procesar la solicitud.');
                }
            }
        },
        error: function () {
            mostrarError('Error de conexión. Intenta nuevamente.');
        },
        complete: function () {
            if (typeof options.onComplete === 'function') options.onComplete();
        }
    });
}
