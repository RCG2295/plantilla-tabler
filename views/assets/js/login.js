$(document).ready(function () {

    var app_url = $('meta[name="app-url"]').attr('content');

    $('#form_login').on('submit', function (e) {
        e.preventDefault();

        var btn = $('#btn_ingresar');
        btn.prop('disabled', true).text('Ingresando...');

        $.ajax({
            url: app_url + '/views/ajax/ajax_login.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.status === 'ok') {
                    window.location.href = app_url + '/dashboard';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Acceso denegado',
                        text: res.message || 'Credenciales incorrectas.',
                        confirmButtonColor: '#4f46e5'
                    });
                    btn.prop('disabled', false).text('Ingresar');
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión. Intenta nuevamente.',
                    confirmButtonColor: '#4f46e5'
                });
                btn.prop('disabled', false).text('Ingresar');
            }
        });
    });

});
