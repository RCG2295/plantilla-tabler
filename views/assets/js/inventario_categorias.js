$(document).ready(function () {

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_inventario_categorias.php';
    var permisos = window.PERMISOS_CATEGORIAS || { crear: false, editar: false, eliminar: false };
    var modal    = new bootstrap.Modal(document.getElementById('modal_categoria'));
    var tsEstado = new TomSelect('#categoria_estado', { create: false });

    var tablaPadres;
    var tablaHijos;
    var idPadreActual  = null;
    var modoSubcategoria = false;

    // ── Tabla padres ────────────────────────────────────────────────────────
    tablaPadres = $('#tabla_categorias').DataTable({
        ajax: { url: ajax_url + '?action=list', type: 'GET', dataSrc: 'data' },
        columns: [
            { data: 'id', width: '60px' },
            { data: 'nombre' },
            { data: 'descripcion', render: function (d) { return d || '<span class="text-muted">—</span>'; } },
            {
                data: 'total_subcategorias',
                className: 'text-center',
                render: function (d, _, row) {
                    var count = parseInt(d) || 0;
                    var badge = count > 0
                        ? '<span class="badge bg-blue-lt text-blue">' + count + '</span>'
                        : '<span class="text-muted">0</span>';
                    return badge + ' &nbsp;<button class="btn btn-xs btn-outline-secondary btn-ver-subs ms-1" data-id="' + row.id + '" data-nombre="' + row.nombre + '" title="Ver subcategorías"><i class="ti ti-layout-list"></i></button>';
                }
            },
            {
                data: 'estado',
                render: function (d) {
                    if (d == 0) return '<span class="badge" style="background:var(--color-exito);color:#fff;">Activo</span>';
                    return '<span class="badge bg-secondary">Inactivo</span>';
                }
            },
            {
                data: 'id', orderable: false, className: 'text-center',
                render: function (id) {
                    var html = '';
                    if (permisos.editar)   html += '<button class="btn btn-sm btn-outline-primary me-1 btn-editar-padre" data-id="' + id + '" title="Editar"><i class="ti ti-pencil"></i></button>';
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

    // ── Tabla subcategorías ─────────────────────────────────────────────────
    tablaHijos = $('#tabla_subcategorias').DataTable({
        ajax: { url: ajax_url + '?action=list_by_padre&id_padre=0', type: 'GET', dataSrc: 'data' },
        columns: [
            { data: 'id', width: '60px' },
            { data: 'nombre' },
            { data: 'descripcion', render: function (d) { return d || '<span class="text-muted">—</span>'; } },
            {
                data: 'estado',
                render: function (d) {
                    if (d == 0) return '<span class="badge" style="background:var(--color-exito);color:#fff;">Activo</span>';
                    return '<span class="badge bg-secondary">Inactivo</span>';
                }
            },
            {
                data: 'id', orderable: false, className: 'text-center',
                render: function (id) {
                    var html = '';
                    if (permisos.editar)   html += '<button class="btn btn-sm btn-outline-primary me-1 btn-editar-hijo" data-id="' + id + '" title="Editar"><i class="ti ti-pencil"></i></button>';
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

    // ── Ver subcategorías de un padre ───────────────────────────────────────
    $('#tabla_categorias').on('click', '.btn-ver-subs', function () {
        idPadreActual    = $(this).data('id');
        modoSubcategoria = true;
        $('#label_padre').text($(this).data('nombre'));
        tablaHijos.ajax.url(ajax_url + '?action=list_by_padre&id_padre=' + idPadreActual).load();
        $('#card_padres').hide();
        $('#card_subcategorias').show();
        // Scroll suave hacia la tabla
        $('html, body').animate({ scrollTop: $('#card_subcategorias').offset().top - 80 }, 200);
    });

    // ── Volver a padres ─────────────────────────────────────────────────────
    $('#btn_volver_padres').on('click', function () {
        modoSubcategoria = false;
        idPadreActual    = null;
        $('#card_subcategorias').hide();
        $('#card_padres').show();
    });

    // ── Nueva categoría (nivel 1) ───────────────────────────────────────────
    $('#btn_nueva_categoria').on('click', function () {
        limpiarFormulario();
        $('#categoria_id_padre').val('');
        $('#modal_categoria_titulo').text('Nueva categoría');
        modal.show();
    });

    // ── Nueva subcategoría (nivel 2) ───────────────────────────────────────
    $('#btn_nueva_subcategoria').on('click', function () {
        limpiarFormulario();
        $('#categoria_id_padre').val(idPadreActual);
        $('#modal_categoria_titulo').text('Nueva subcategoría de: ' + $('#label_padre').text());
        modal.show();
    });

    // ── Editar padre ────────────────────────────────────────────────────────
    $('#tabla_categorias').on('click', '.btn-editar-padre', function () {
        cargarParaEditar($(this).data('id'), false);
    });

    // ── Editar hijo ─────────────────────────────────────────────────────────
    $('#tabla_subcategorias').on('click', '.btn-editar-hijo', function () {
        cargarParaEditar($(this).data('id'), true);
    });

    function cargarParaEditar(id, esHijo) {
        $.ajax({
            url: ajax_url, type: 'GET',
            data: { action: 'get', id: id }, dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') { mostrarError(res.message); return; }
                var c = res.data;
                limpiarFormulario();
                $('#modal_categoria_titulo').text(esHijo ? 'Editar subcategoría' : 'Editar categoría');
                $('#categoria_id').val(c.id);
                $('#categoria_id_padre').val(c.id_padre || '');
                $('#categoria_nombre').val(c.nombre);
                $('#categoria_descripcion').val(c.descripcion);
                tsEstado.setValue(String(c.estado));
                modal.show();
            }
        });
    }

    // ── Eliminar (en ambas tablas) ──────────────────────────────────────────
    $('body').on('click', '.btn-eliminar', function () {
        var id       = $(this).data('id');
        var esHijo   = modoSubcategoria;
        confirmarEliminacion('La categoría será marcada como eliminada.').then(function (r) {
            if (!r.isConfirmed) return;
            ajaxPost({
                url: ajax_url, data: { action: 'delete', id: id },
                onSuccess: function (res) {
                    mostrarExito(res.message);
                    if (esHijo) {
                        tablaHijos.ajax.reload(null, false);
                        tablaPadres.ajax.reload(null, false);
                    } else {
                        tablaPadres.ajax.reload(null, false);
                    }
                },
                onError: function (res) { mostrarError(res.message); }
            });
        });
    });

    // ── Submit ──────────────────────────────────────────────────────────────
    $('#form_categoria').on('submit', function (e) {
        e.preventDefault();
        if (!validarCamposRequeridos('#form_categoria')) { mostrarError('Completa los campos requeridos.'); return; }

        var esHijo = $('#categoria_id_padre').val() !== '';
        var btn    = $('#btn_guardar_categoria').prop('disabled', true).text('Guardando...');

        ajaxPost({
            url: ajax_url,
            data: {
                action:      'save',
                id:          $('#categoria_id').val(),
                nombre:      $('#categoria_nombre').val().trim(),
                descripcion: $('#categoria_descripcion').val().trim(),
                id_padre:    $('#categoria_id_padre').val(),
                estado:      tsEstado.getValue()
            },
            onSuccess: function (res) {
                modal.hide();
                mostrarExito(res.message);
                if (esHijo) {
                    tablaHijos.ajax.reload(null, false);
                    tablaPadres.ajax.reload(null, false);
                } else {
                    tablaPadres.ajax.reload(null, false);
                }
            },
            onError:    function (res) { mostrarError(res.message); },
            onComplete: function ()    { btn.prop('disabled', false).text('Guardar'); }
        });
    });

    function limpiarFormulario() {
        $('#form_categoria')[0].reset();
        $('#categoria_id').val('');
        $('#categoria_id_padre').val('');
        tsEstado.setValue('0');
    }

});
