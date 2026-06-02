$(document).ready(function () {

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_egresos_egresos.php';
    var permisos = window.PERMISOS_EGRESOS || { crear: false, eliminar: false };
    var modal    = new bootstrap.Modal(document.getElementById('modal_egreso'));
    var tablaActivos, tablaCancelados;
    var tsCategoria, tsSubcategoria, tsMetodo, fpFecha;
    var fpActivosDesde, fpActivosHasta, fpCancelDesde, fpCancelHasta;
    var tabCanceladosInited = false;

    // ── Flatpickr ────────────────────────────────────────────────────────────
    fpFecha = flatpickr('#egreso_fecha', {
        dateFormat: 'Y-m-d',
        locale: 'es',
        allowInput: true
    });

    // ── Flatpickr filtros (default: ultimos 30 dias) ─────────────────────────
    var hoy30e   = new Date();
    var desde30e = new Date();
    desde30e.setDate(hoy30e.getDate() - 30);

    function mkFpE(sel, def) {
        return flatpickr(sel, { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd/m/Y', defaultDate: def, appendTo: document.body });
    }

    fpActivosDesde = mkFpE('#filtro_activos_desde', desde30e);
    fpActivosHasta = mkFpE('#filtro_activos_hasta', hoy30e);
    fpCancelDesde  = mkFpE('#filtro_cancelados_desde', desde30e);
    fpCancelHasta  = mkFpE('#filtro_cancelados_hasta', hoy30e);

    // ── TomSelect metodo pago ────────────────────────────────────────────────
    tsMetodo = new TomSelect('#egreso_metodo_pago', { create: false });

    tsMetodo.on('change', function (val) {
        if (val === 'efectivo') {
            $('#grupo_referencia').hide();
            $('#egreso_referencia').val('');
        } else {
            $('#grupo_referencia').show();
        }
    });

    // ── TomSelect categoria ──────────────────────────────────────────────────
    function initTsCategoria(options) {
        if (tsCategoria) tsCategoria.destroy();
        tsCategoria = new TomSelect('#egreso_id_categoria', {
            create: false,
            options: options,
            valueField: 'id',
            labelField: 'nombre',
            searchField: 'nombre',
            placeholder: 'Seleccionar categoría',
            onChange: function (val) {
                cargarSubcategorias(val ? parseInt(val) : null);
            }
        });
    }

    function initTsSubcategoria(options) {
        if (tsSubcategoria) tsSubcategoria.destroy();
        tsSubcategoria = new TomSelect('#egreso_id_subcategoria', {
            create: false,
            options: options,
            valueField: 'id',
            labelField: 'nombre',
            searchField: 'nombre',
            placeholder: 'Sin subcategoría'
        });
    }

    function cargarCategorias(callback) {
        $.ajax({
            url: ajax_url, type: 'GET',
            data: { action: 'padres' }, dataType: 'json',
            success: function (res) {
                var opts = res.status === 'ok' ? res.data : [];
                initTsCategoria(opts);
                if (callback) callback();
            }
        });
    }

    function cargarSubcategorias(id_padre) {
        if (tsSubcategoria) tsSubcategoria.destroy();
        var $sel = $('#egreso_id_subcategoria').empty().append('<option value="">Sin subcategoría</option>');

        if (!id_padre) {
            initTsSubcategoria([]);
            return;
        }

        $.ajax({
            url: ajax_url, type: 'GET',
            data: { action: 'subcategorias', id_padre: id_padre }, dataType: 'json',
            success: function (res) {
                var opts = res.status === 'ok' ? res.data : [];
                $.each(opts, function (_, s) {
                    $sel.append('<option value="' + s.id + '">' + s.nombre + '</option>');
                });
                initTsSubcategoria(opts);
            }
        });
    }

    // ── DataTable Activos ────────────────────────────────────────────────────
    tablaActivos = $('#tabla_egresos_activos').DataTable({
        ajax: function (_, callback) {
            $.ajax({
                url: ajax_url, type: 'GET', dataType: 'json',
                data: { action: 'list_activos', desde: $('#filtro_activos_desde').val(), hasta: $('#filtro_activos_hasta').val() },
                success: function (res) { callback({ data: res.data || [] }); },
                error:   function ()    { callback({ data: [] }); }
            });
        },
        columns: buildColumns(false),
        language: { url: window.DT_LANG_URL },
        pageLength: 15,
        order: [[3, 'desc']],
        responsive: true
    });

    // ── DataTable Cancelados (lazy init al primer click de tab) ──────────────
    $('#tab_cancelados_btn').on('shown.bs.tab', function () {
        if (tabCanceladosInited) return;
        tabCanceladosInited = true;
        tablaCancelados = $('#tabla_egresos_cancelados').DataTable({
            ajax: function (_, callback) {
                $.ajax({
                    url: ajax_url, type: 'GET', dataType: 'json',
                    data: { action: 'list_cancelados', desde: $('#filtro_cancelados_desde').val(), hasta: $('#filtro_cancelados_hasta').val() },
                    success: function (res) { callback({ data: res.data || [] }); },
                    error:   function ()    { callback({ data: [] }); }
                });
            },
            columns: buildColumns(true),
            language: { url: window.DT_LANG_URL },
            pageLength: 15,
            order: [[3, 'desc']],
            responsive: true
        });
    });

    $('#btn_buscar_activos').on('click', function () {
        tablaActivos.ajax.reload(null, false);
    });

    $('#btn_buscar_cancelados').on('click', function () {
        if (!tabCanceladosInited) return;
        tablaCancelados.ajax.reload(null, false);
    });

    function buildColumns(isCancelados) {
        var cols = [
            { data: 'categoria_nombre',    render: function (d) { return d || '<span class="text-muted">—</span>'; } },
            { data: 'subcategoria_nombre', render: function (d) { return d || '<span class="text-muted">—</span>'; } },
            { data: 'concepto' },
            {
                data: 'fecha_egreso',
                render: function (d, type) {
                    if (!d) return (type === 'sort' || type === 'type') ? '' : '<span class="text-muted">—</span>';
                    if (type === 'sort' || type === 'type') return d;
                    var parts = d.split('-');
                    return parts[2] + '/' + parts[1] + '/' + parts[0];
                }
            },
            {
                data: 'monto', className: 'text-end',
                render: function (d) { return '$' + parseFloat(d).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
            },
            {
                data: 'metodo_pago',
                render: function (d) {
                    var map = { efectivo: 'Efectivo', transferencia: 'Transferencia', tarjeta: 'Tarjeta' };
                    return map[d] || d;
                }
            },
            {
                data: null,
                render: function (_, __, row) {
                    var nombre = ((row.usuario_nombre || '') + ' ' + (row.usuario_apellidos || '')).trim();
                    return nombre || 'Sistema';
                }
            }
        ];

        if (isCancelados) {
            cols.push({
                data: null, orderable: false, className: 'text-center',
                render: function (_, __, row) {
                    var html = '<button class="btn btn-sm btn-outline-secondary btn-imprimir me-1" data-id="' + row.id + '" title="Imprimir"><i class="ti ti-printer"></i></button>';
                    if (row.archivo) {
                        html += '<a class="btn btn-sm btn-outline-info me-1" href="' + app_url + '/views/uploads/egresos_egresos/' + row.archivo + '" target="_blank" title="Ver adjunto"><i class="ti ti-paperclip"></i></a>';
                    }
                    if (row.id_compra && row.compra_folio) {
                        html += '<a href="' + (window.APP_URL_EGRESOS || '') + '/views/tickets/compra_ticket.php?id=' + row.id_compra +
                               '" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver compra">' +
                               '<i class="ti ti-receipt me-1"></i>' + row.compra_folio + '</a>';
                    }
                    return html;
                }
            });
        } else {
            cols.push({
                data: null, orderable: false, className: 'text-center',
                render: function (_, __, row) {
                    var html = '<button class="btn btn-sm btn-outline-secondary btn-imprimir me-1" data-id="' + row.id + '" title="Imprimir"><i class="ti ti-printer"></i></button>';
                    if (row.archivo) {
                        html += '<a class="btn btn-sm btn-outline-info me-1" href="' + app_url + '/views/uploads/egresos_egresos/' + row.archivo + '" target="_blank" title="Ver adjunto"><i class="ti ti-paperclip"></i></a>';
                    }
                    if (permisos.eliminar) {
                        html += '<button class="btn btn-sm btn-outline-danger btn-cancelar" data-id="' + row.id + '" title="Cancelar egreso">' +
                                '<i class="ti ti-ban"></i></button>';
                    }
                    return html;
                }
            });
        }

        return cols;
    }

    // ── Nuevo egreso ─────────────────────────────────────────────────────────
    $('#btn_nuevo_egreso').on('click', function () {
        limpiarFormulario();
        cargarCategorias(function () { modal.show(); });
    });

    // ── Imprimir egreso ──────────────────────────────────────────────────────
    $(document).on('click', '.btn-imprimir', function () {
        var id = $(this).data('id');
        window.open(app_url + '/views/tickets/egreso_ticket.php?id=' + id, '_blank');
    });

    // ── Cancelar egreso ──────────────────────────────────────────────────────
    $('#tabla_egresos_activos').on('click', '.btn-cancelar', function () {
        var id = $(this).data('id');
        confirmarEliminacion('El egreso será cancelado y no se podrá reactivar.').then(function (r) {
            if (!r.isConfirmed) return;
            ajaxPost({
                url: ajax_url, data: { action: 'cancelar', id: id },
                onSuccess: function (res) {
                    mostrarExito(res.message);
                    tablaActivos.ajax.reload(null, false);
                },
                onError: function (res) { mostrarError(res.message); }
            });
        });
    });

    // ── Submit ───────────────────────────────────────────────────────────────
    $('#form_egreso').on('submit', function (e) {
        e.preventDefault();
        if (!validarCamposRequeridos('#form_egreso')) { mostrarError('Completa los campos requeridos.'); return; }

        var metodo = tsMetodo.getValue();
        var btn    = $('#btn_guardar_egreso').prop('disabled', true).text('Guardando...');

        var fd = new FormData();
        fd.append('action',          'save');
        fd.append('id_categoria',    tsCategoria    ? tsCategoria.getValue()    : '');
        fd.append('id_subcategoria', tsSubcategoria ? tsSubcategoria.getValue() : '');
        fd.append('concepto',        $('#egreso_concepto').val().trim());
        fd.append('fecha_egreso',    $('#egreso_fecha').val());
        fd.append('monto',           $('#egreso_monto').val());
        fd.append('metodo_pago',     metodo);
        fd.append('referencia',      metodo !== 'efectivo' ? $('#egreso_referencia').val().trim() : '');
        fd.append('notas',           $('#egreso_notas').val().trim());
        var archivoInput = document.getElementById('egreso_archivo');
        if (archivoInput && archivoInput.files[0]) fd.append('archivo', archivoInput.files[0]);

        $.ajax({
            url: ajax_url, type: 'POST',
            data: fd, processData: false, contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.status === 'ok') {
                    modal.hide();
                    mostrarExito(res.message);
                    tablaActivos.ajax.reload(null, false);
                } else {
                    mostrarError(res.message);
                }
            },
            error:    function () { mostrarError('Error de conexión. Intenta nuevamente.'); },
            complete: function () { btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Guardar egreso'); }
        });
    });

    function limpiarFormulario() {
        $('#form_egreso')[0].reset();
        $('#egreso_archivo').val('');
        $('#grupo_referencia').hide();
        if (fpFecha) fpFecha.clear();
        if (tsCategoria)    { tsCategoria.destroy();    tsCategoria = null; }
        if (tsSubcategoria) { tsSubcategoria.destroy();  tsSubcategoria = null; }
        if (tsMetodo)       tsMetodo.setValue('efectivo');
        $('#egreso_id_subcategoria').empty().append('<option value="">Sin subcategoría</option>');
        initTsSubcategoria([]);
    }

});
