$(document).ready(function () {

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_egresos_categorias.php';
    var permisos = window.PERMISOS_EG_CATEGORIAS || { crear: false, editar: false, eliminar: false };
    var modal    = new bootstrap.Modal(document.getElementById('modal_categoria'));

    var tablaPadres;
    var tablaSubs;
    var tablaSubs_inited = false;
    var selectedPadre = { id: null, nombre: '' };

    // ── Columnas compartidas ──────────────────────────────────────────────────
    function badgeEstado(d) {
        if (d == 0) return '<span class="badge" style="background:var(--color-exito);color:#fff;">Activa</span>';
        return '<span class="badge bg-secondary">Inactiva</span>';
    }

    // ── DataTable padres ─────────────────────────────────────────────────────
    tablaPadres = $('#tabla_categorias_padres').DataTable({
        ajax: { url: ajax_url + '?action=list_padres', type: 'GET', dataSrc: 'data' },
        columns: [
            { data: 'nombre', render: function (d) { return '<span class="fw-semibold">' + d + '</span>'; } },
            { data: 'descripcion', render: function (d) { return d || '<span class="text-muted">—</span>'; } },
            { data: 'estado', render: badgeEstado },
            {
                data: 'id', orderable: false, className: 'text-center',
                render: function (id, _, row) {
                    var html = '<button class="btn btn-sm btn-outline-secondary me-1 btn-ver-subs" data-id="' + id + '" data-nombre="' + row.nombre + '" title="Ver subcategorías"><i class="ti ti-list"></i></button>';
                    if (permisos.editar)   html += '<button class="btn btn-sm btn-outline-primary me-1 btn-editar" data-id="' + id + '" data-tipo="padre" title="Editar"><i class="ti ti-pencil"></i></button>';
                    if (permisos.eliminar) html += '<button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="' + id + '" title="Eliminar"><i class="ti ti-trash"></i></button>';
                    return html;
                }
            }
        ],
        language: { url: window.DT_LANG_URL },
        pageLength: 15,
        order: [[0, 'asc']],
        responsive: true
    });

    // ── Ver subcategorías ────────────────────────────────────────────────────
    $('#tabla_categorias_padres').on('click', '.btn-ver-subs', function () {
        var id     = $(this).data('id');
        var nombre = $(this).data('nombre');
        abrirSubcategorias(id, nombre);
    });

    function abrirSubcategorias(id, nombre) {
        selectedPadre = { id: id, nombre: nombre };
        $('#lbl_padre_nombre').text(nombre);
        $('#card_subcategorias').removeClass('d-none');
        $('html, body').animate({ scrollTop: $('#card_subcategorias').offset().top - 20 }, 300);

        if (!tablaSubs_inited) {
            tablaSubs = $('#tabla_subcategorias').DataTable({
                ajax: {
                    url: ajax_url + '?action=list_subcategorias&id_padre=' + id,
                    type: 'GET',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'nombre', render: function (d) { return '<span class="fw-semibold">' + d + '</span>'; } },
                    { data: 'descripcion', render: function (d) { return d || '<span class="text-muted">—</span>'; } },
                    { data: 'estado', render: badgeEstado },
                    {
                        data: 'id', orderable: false, className: 'text-center',
                        render: function (id) {
                            var html = '';
                            if (permisos.editar)   html += '<button class="btn btn-sm btn-outline-primary me-1 btn-editar-sub" data-id="' + id + '" title="Editar"><i class="ti ti-pencil"></i></button>';
                            if (permisos.eliminar) html += '<button class="btn btn-sm btn-outline-danger btn-eliminar-sub" data-id="' + id + '" title="Eliminar"><i class="ti ti-trash"></i></button>';
                            return html || '<span class="text-muted">—</span>';
                        }
                    }
                ],
                language: { url: window.DT_LANG_URL },
                pageLength: 10,
                order: [[0, 'asc']],
                responsive: true
            });
            tablaSubs_inited = true;
        } else {
            tablaSubs.ajax.url(ajax_url + '?action=list_subcategorias&id_padre=' + id).load();
        }
    }

    // ── Cerrar panel subcategorías ────────────────────────────────────────────
    $('#btn_cerrar_subcategorias').on('click', function () {
        $('#card_subcategorias').addClass('d-none');
        selectedPadre = { id: null, nombre: '' };
    });

    // ── Nueva categoría padre ────────────────────────────────────────────────
    $('#btn_nueva_categoria').on('click', function () {
        limpiarFormulario(null, null);
        $('#modal_categoria_titulo').text('Nueva categoría');
        modal.show();
    });

    // ── Nueva subcategoría ───────────────────────────────────────────────────
    $('#btn_nueva_subcategoria').on('click', function () {
        limpiarFormulario(null, selectedPadre);
        $('#modal_categoria_titulo').text('Nueva subcategoría');
        modal.show();
    });

    // ── Editar categoría padre ───────────────────────────────────────────────
    $('#tabla_categorias_padres').on('click', '.btn-editar', function () {
        cargarYEditar($(this).data('id'), null);
    });

    // ── Editar subcategoría ──────────────────────────────────────────────────
    $('#tabla_subcategorias').on('click', '.btn-editar-sub', function () {
        cargarYEditar($(this).data('id'), selectedPadre);
    });

    function cargarYEditar(id, padre) {
        $.ajax({
            url: ajax_url, type: 'GET',
            data: { action: 'get', id: id }, dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') { mostrarError(res.message); return; }
                var c = res.data;
                var contexto = padre || (c.id_padre ? { id: c.id_padre, nombre: '' } : null);
                limpiarFormulario(c, contexto);
                $('#modal_categoria_titulo').text(padre ? 'Editar subcategoría' : 'Editar categoría');
                modal.show();
            }
        });
    }

    // ── Eliminar categoría padre ─────────────────────────────────────────────
    $('#tabla_categorias_padres').on('click', '.btn-eliminar', function () {
        var id = $(this).data('id');
        confirmarEliminacion('La categoría y sus subcategorías serán eliminadas.').then(function (r) {
            if (!r.isConfirmed) return;
            ajaxPost({
                url: ajax_url, data: { action: 'delete', id: id },
                onSuccess: function (res) {
                    mostrarExito(res.message);
                    tablaPadres.ajax.reload(null, false);
                    if (selectedPadre.id == id) {
                        $('#card_subcategorias').addClass('d-none');
                        selectedPadre = { id: null, nombre: '' };
                    }
                },
                onError: function (res) { mostrarError(res.message); }
            });
        });
    });

    // ── Eliminar subcategoría ────────────────────────────────────────────────
    $('#tabla_subcategorias').on('click', '.btn-eliminar-sub', function () {
        var id = $(this).data('id');
        confirmarEliminacion('La subcategoría será eliminada.').then(function (r) {
            if (!r.isConfirmed) return;
            ajaxPost({
                url: ajax_url, data: { action: 'delete', id: id },
                onSuccess: function (res) {
                    mostrarExito(res.message);
                    tablaSubs.ajax.reload(null, false);
                },
                onError: function (res) { mostrarError(res.message); }
            });
        });
    });

    // ── Submit ───────────────────────────────────────────────────────────────
    $('#form_categoria').on('submit', function (e) {
        e.preventDefault();
        if (!validarCamposRequeridos('#form_categoria')) { mostrarError('Completa los campos requeridos.'); return; }

        var btn = $('#btn_guardar_categoria').prop('disabled', true).text('Guardando...');

        ajaxPost({
            url: ajax_url,
            data: {
                action:      'save',
                id:          $('#categoria_id').val(),
                nombre:      $('#categoria_nombre').val().trim(),
                descripcion: $('#categoria_descripcion').val().trim(),
                id_padre:    $('#categoria_id_padre').val()
            },
            onSuccess: function (res) {
                modal.hide();
                mostrarExito(res.message);
                var esSub = !!$('#categoria_id_padre').val();
                if (esSub && tablaSubs_inited) {
                    tablaSubs.ajax.reload(null, false);
                } else {
                    tablaPadres.ajax.reload(null, false);
                }
            },
            onError:    function (res) { mostrarError(res.message); },
            onComplete: function ()    { btn.prop('disabled', false).text('Guardar'); }
        });
    });

    // ── Helpers ──────────────────────────────────────────────────────────────
    function limpiarFormulario(data, padre) {
        $('#form_categoria')[0].reset();
        $('#categoria_id').val(data ? data.id : '');
        $('#categoria_nombre').val(data ? data.nombre : '');
        $('#categoria_descripcion').val(data ? (data.descripcion || '') : '');

        if (padre && padre.id) {
            $('#categoria_id_padre').val(padre.id);
            $('#info_padre_nombre').text(padre.nombre || '');
            $('#info_padre').removeClass('d-none');
        } else {
            $('#categoria_id_padre').val('');
            $('#info_padre').addClass('d-none');
        }
    }

});
