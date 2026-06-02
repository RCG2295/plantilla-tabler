// ── Ajax progress bar ────────────────────────────────────────────────────────
(function () {
    var bar      = null;
    var hideTimer = null;

    function getBar() {
        if (!bar) bar = document.getElementById('ajax-loader');
        return bar;
    }

    $(document).on('ajaxStart', function () {
        clearTimeout(hideTimer);
        var b = getBar();
        b.className = '';
        b.style.width = '15%';
        b.offsetWidth;
        b.className = 'loading';
    });

    $(document).on('ajaxStop', function () {
        var b = getBar();
        b.className = 'done';
        hideTimer = setTimeout(function () {
            b.className = 'hide';
            setTimeout(function () { b.className = ''; b.style.width = '0'; }, 300);
        }, 150);
    });
}());

function cargarNotificacionesNavbar() {
    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_reportes_notificaciones.php?action=navbar_list';

    $.ajax({
        url: ajax_url,
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            if (res.status !== 'ok') return;

            var data  = res.data;
            var badge = $('.badge-notification');

            if (data.count > 0) {
                badge.text(data.count).removeClass('d-none');
            } else {
                badge.addClass('d-none');
            }

            var lista = $('#notificaciones_lista');
            lista.empty();

            if (data.items.length === 0) {
                lista.html('<div class="dropdown-item text-muted text-center py-3" style="font-size:0.82rem;">Sin notificaciones</div>');
                return;
            }

            $.each(data.items, function (i, item) {
                var fecha  = new Date(item.fecha_alta.replace(' ', 'T'));
                var ahora  = new Date();
                var diff   = Math.round((ahora - fecha) / 60000);
                var tiempo;

                if (diff < 60) {
                    tiempo = diff <= 1 ? 'Hace 1 minuto' : 'Hace ' + diff + ' minutos';
                } else if (diff < 1440) {
                    var h  = Math.round(diff / 60);
                    tiempo = h === 1 ? 'Hace 1 hora' : 'Hace ' + h + ' horas';
                } else {
                    var d  = Math.round(diff / 1440);
                    tiempo = d === 1 ? 'Hace 1 día' : 'Hace ' + d + ' días';
                }

                lista.append(
                    '<a href="#" class="dropdown-item py-2">'
                    + '<div class="fw-semibold" style="font-size:0.85rem;">' + $('<div>').text(item.titulo).html() + '</div>'
                    + '<div class="text-muted" style="font-size:0.75rem;">' + tiempo + '</div>'
                    + '</a>'
                );
            });
        }
    });
}

$(document).ready(function () {

    // Restaurar estado del sidebar desde localStorage
    if (localStorage.getItem('sidebar_collapsed') === 'true') {
        $('body').addClass('sidebar-collapsed');
    }

    // Toggle sidebar collapse (desktop)
    $('#btn_toggle_sidebar').on('click', function () {
        if (window.innerWidth >= 992) {
            $('body').toggleClass('sidebar-collapsed');
            localStorage.setItem('sidebar_collapsed', $('body').hasClass('sidebar-collapsed'));
        } else {
            // Mobile: mostrar/ocultar offcanvas
            $('.navbar-vertical').toggleClass('show');
            $('.sidebar-overlay').toggleClass('show');
        }
    });

    // Cerrar sidebar en mobile al hacer click en overlay
    $(document).on('click', '.sidebar-overlay', function () {
        $('.navbar-vertical').removeClass('show');
        $('.sidebar-overlay').removeClass('show');
    });

    // Activar enlace del submenu si la ruta lo requiere
    $('.nav-submenu .nav-link').each(function () {
        if (window.location.href.indexOf($(this).attr('href')) !== -1) {
            $(this).addClass('active');
            $(this).closest('.collapse').addClass('show');
        }
    });

    // Cargar notificaciones del navbar al abrir el dropdown
    if ($('#btn_notificaciones').length) {
        cargarNotificacionesNavbar();
        $('#btn_notificaciones').on('show.bs.dropdown', function () {
            cargarNotificacionesNavbar();
        });
    }

});
