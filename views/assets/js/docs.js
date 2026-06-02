$(document).ready(function () {

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_docs.php';

    function loadDoc(name) {
        $('#doc_content').html(
            '<div class="text-center py-5">' +
            '<div class="spinner-border" style="color:var(--color-primario);"></div>' +
            '</div>'
        );

        $.getJSON(ajax_url + '?action=get&doc=' + encodeURIComponent(name), function (res) {
            if (res.status !== 'ok') {
                $('#doc_content').html('<p class="text-danger">No se pudo cargar el documento.</p>');
                return;
            }
            $('#doc_content').html(res.html);
            $('.doc-link').removeClass('active');
            $('.doc-link[data-doc="' + name + '"]').addClass('active');
            location.hash = name;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }).fail(function () {
            $('#doc_content').html('<p class="text-danger">Error de conexión.</p>');
        });
    }

    var initial = location.hash.replace('#', '') || 'index';
    loadDoc(initial);

    $(document).on('click', '.doc-link', function (e) {
        e.preventDefault();
        loadDoc($(this).data('doc'));
    });

    $('#btn_print_doc').on('click', function () {
        window.location.href = ajax_url + '?action=download_pdf';
    });

});
