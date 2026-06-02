$(document).ready(function () {

    var app_url      = $('meta[name="app-url"]').attr('content');
    var ajax_url     = app_url + '/views/ajax/ajax_inventario_productos.php';
    var ajax_cats    = app_url + '/views/ajax/ajax_inventario_categorias.php';
    var ajax_unids   = app_url + '/views/ajax/ajax_inventario_unidades.php';
    var tabla;
    var modal    = new bootstrap.Modal(document.getElementById('modal_producto'));
    var permisos = window.PERMISOS_PRODUCTOS || { crear: false, editar: false, eliminar: false };
    var unidadMap = {};

    var tsCatPadre = new TomSelect('#producto_cat_padre',        { create: false });
    var tsCatHijo  = new TomSelect('#producto_id_categoria',     { create: false });
    var tsUnidad   = new TomSelect('#producto_id_unidad_medida', { create: false });
    var tsEstado   = new TomSelect('#producto_estado',           { create: false });

    // ── Helpers categoria ───────────────────────────────────────────────────
    function sincHidden() {
        var hijo  = tsCatHijo.getValue();
        var padre = tsCatPadre.getValue();
        $('#producto_id_categoria_final').val(hijo || padre || '');
    }

    function cargarSubcategorias(id_padre, selectedHijo) {
        tsCatHijo.clearOptions();
        tsCatHijo.addOption({ value: '', text: 'Sin subcategoria' });

        if (!id_padre) {
            sincHidden();
            return;
        }

        $.getJSON(ajax_cats + '?action=list_by_padre&id_padre=' + id_padre, function (r) {
            $.each(r.data || [], function (_, s) {
                tsCatHijo.addOption({ value: String(s.id), text: s.nombre });
            });
            tsCatHijo.setValue(selectedHijo ? String(selectedHijo) : '');
            tsCatHijo.refreshOptions(false);
            sincHidden();
        });
    }

    tsCatPadre.on('change', function (val) {
        tsCatHijo.setValue('');
        cargarSubcategorias(val, null);
    });

    tsCatHijo.on('change', function () { sincHidden(); });

    // ── Fraccionamiento ─────────────────────────────────────────────────────
    tsUnidad.on('change', function (val) {
        var u = unidadMap[val];
        if (u && u.abreviatura !== 'pza') {
            $('#wrap_fracciona').show();
        } else {
            $('#wrap_fracciona').hide();
            $('#producto_se_fracciona').prop('checked', false);
            $('#producto_cantidad_presentacion').val(1).prop('disabled', true);
            $('#wrap_precio_venta_unidad').hide();
            $('#producto_precio_venta_unidad').val('');
        }
        actualizarHintStock();
    });

    $('#producto_se_fracciona').on('change', function () {
        if ($(this).is(':checked')) {
            $('#producto_cantidad_presentacion').prop('disabled', false);
            $('#wrap_precio_venta_unidad').show();
        } else {
            $('#producto_cantidad_presentacion').val(1).prop('disabled', true);
            $('#wrap_precio_venta_unidad').hide();
            $('#producto_precio_venta_unidad').val('');
        }
        actualizarHintStock();
    });

    $('#producto_stock_actual').on('input', actualizarHintStock);
    $('#producto_cantidad_presentacion').on('input', actualizarHintStock);

    function actualizarHintStock() {
        var seFragc   = $('#producto_se_fracciona').is(':checked');
        var cantPres  = parseFloat($('#producto_cantidad_presentacion').val()) || 1;
        var cantInput = parseFloat($('#producto_stock_actual').val()) || 0;
        var u         = unidadMap[tsUnidad.getValue()];
        var ab        = u ? u.abreviatura : '';

        if (seFragc && cantPres > 0) {
            $('#lbl_stock_inicial').text('Stock inicial (presentaciones)');
            $('#lbl_stock_minimo').text('Stock mínimo (presentaciones)');
            $('#lbl_stock_maximo').text('Stock máximo (presentaciones)');
            if (cantInput > 0) {
                var total = cantInput * cantPres;
                $('#hint_stock_presentacion')
                    .text('= ' + total.toFixed(2) + ' ' + ab + ' que se guardarán en el sistema')
                    .show();
            } else {
                $('#hint_stock_presentacion').hide();
            }
        } else {
            $('#lbl_stock_inicial').text('Stock inicial');
            $('#lbl_stock_minimo').text('Stock mínimo');
            $('#lbl_stock_maximo').text('Stock máximo');
            $('#hint_stock_presentacion').hide();
        }
    }

    function cargarCatalogos(idCategoria, idCategoriaPadre, selectedUnidad, seFragciona, cantPresentacion) {
        $.getJSON(ajax_cats + '?action=padres', function (r) {
            tsCatPadre.clearOptions();
            tsCatPadre.addOption({ value: '', text: 'Sin categoria' });
            $.each(r.data || [], function (_, c) {
                tsCatPadre.addOption({ value: String(c.id), text: c.nombre });
            });
            var padreVal = idCategoriaPadre ? String(idCategoriaPadre) : (idCategoria ? String(idCategoria) : '');
            tsCatPadre.setValue(padreVal);
            tsCatPadre.refreshOptions(false);

            var hijoVal = idCategoriaPadre ? idCategoria : null;
            cargarSubcategorias(padreVal, hijoVal);
        });

        $.getJSON(ajax_unids + '?action=list', function (r) {
            unidadMap = {};
            tsUnidad.clearOptions();
            tsUnidad.addOption({ value: '', text: 'Sin unidad' });
            $.each(r.data || [], function (_, u) {
                unidadMap[String(u.id)] = { abreviatura: u.abreviatura, nombre: u.nombre };
                tsUnidad.addOption({ value: String(u.id), text: u.nombre + ' (' + u.abreviatura + ')' });
            });
            tsUnidad.setValue(selectedUnidad ? String(selectedUnidad) : '');
            tsUnidad.refreshOptions(false);

            if (seFragciona) {
                $('#producto_se_fracciona').prop('checked', true);
                $('#producto_cantidad_presentacion').val(cantPresentacion || 1).prop('disabled', false);
                $('#wrap_precio_venta_unidad').show();
            } else {
                $('#producto_se_fracciona').prop('checked', false);
                $('#producto_cantidad_presentacion').val(1).prop('disabled', true);
                $('#wrap_precio_venta_unidad').hide();
            }
            actualizarHintStock();
        });
    }

    // ── Indicador stock ─────────────────────────────────────────────────────
    function stockBadge(row) {
        var stock    = parseFloat(row.stock_actual);
        var min      = parseFloat(row.stock_minimo);
        var ab       = row.abreviatura || '';
        var cantPres = parseFloat(row.cantidad_presentacion) || 1;
        var seFragc  = parseInt(row.se_fracciona);
        var val, comparador;

        if (seFragc && cantPres > 0) {
            var pres = stock / cantPres;
            val        = stock.toFixed(2) + ' ' + ab + ' (' + Math.floor(pres) + ' pza)';
            comparador = pres;
        } else {
            val        = stock.toFixed(2) + ' ' + ab;
            comparador = stock;
        }

        if (stock <= 0)        return '<span class="badge bg-danger text-white">' + val + '</span>';
        if (comparador < min)  return '<span class="badge bg-warning text-dark">' + val + '</span>';
        return '<span class="badge" style="background:var(--color-exito);color:#fff;">' + val + '</span>';
    }

    // ── DataTables ──────────────────────────────────────────────────────────
    tabla = $('#tabla_productos').DataTable({
        ajax: { url: ajax_url + '?action=list', type: 'GET', dataSrc: 'data' },
        columns: [
            {
                data: 'foto_principal', orderable: false, width: '50px',
                render: function (foto) {
                    if (!foto) return '<span class="text-muted"><i class="ti ti-photo" style="font-size:1.4rem;"></i></span>';
                    return '<img src="' + app_url + '/views/uploads/inventario_productos/' + foto + '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">';
                }
            },
            { data: 'codigo', render: function (d) { return '<code>' + d + '</code>'; } },
            {
                data: 'nombre',
                render: function (d, _, row) {
                    return '<a href="' + app_url + '/inventario/productos/detalle?id=' + row.id + '" class="fw-semibold text-decoration-none">' + d + '</a>';
                }
            },
            { data: 'categoria', render: function (d) { return d || '<span class="text-muted">-</span>'; } },
            { data: null, render: function (_, __, row) { return stockBadge(row); } },
            {
                data: null,
                render: function (_, __, row) {
                    if (parseInt(row.se_fracciona)) {
                        return parseFloat(row.stock_minimo).toFixed(0) + ' / ' + parseFloat(row.stock_maximo).toFixed(0) + ' pza';
                    }
                    return parseFloat(row.stock_minimo).toFixed(2) + ' / ' + parseFloat(row.stock_maximo).toFixed(2);
                }
            },
            { data: 'precio_costo', render: function (d) { return '$' + parseFloat(d).toFixed(2); } },
            { data: 'precio_venta', render: function (d) { return '$' + parseFloat(d).toFixed(2); } },
            {
                data: 'estado',
                render: function (d) {
                    if (d == 0) return '<span class="badge" style="background:var(--color-exito);color:#fff;">Activo</span>';
                    if (d == 1) return '<span class="badge bg-secondary">Inactivo</span>';
                    return '<span class="badge bg-danger text-white">Eliminado</span>';
                }
            },
            {
                data: 'id', orderable: false, className: 'text-center',
                render: function (id) {
                    var html = '<a href="' + app_url + '/inventario/productos/detalle?id=' + id + '" class="btn btn-sm btn-outline-secondary me-1" title="Ver detalle"><i class="ti ti-eye"></i></a>';
                    if (permisos.editar)   html += '<button class="btn btn-sm btn-outline-primary me-1 btn-editar" data-id="' + id + '" title="Editar"><i class="ti ti-pencil"></i></button>';
                    if (permisos.eliminar) html += '<button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="' + id + '" title="Eliminar"><i class="ti ti-trash"></i></button>';
                    return html;
                }
            }
        ],
        language: { url: window.DT_LANG_URL },
        pageLength: 15,
        order: [[2, 'asc']],
        responsive: true
    });

    // ── Nuevo ───────────────────────────────────────────────────────────────
    $('#btn_nuevo_producto').on('click', function () {
        limpiarFormulario();
        cargarCatalogos(null, null, null, false, 1);
        $('#modal_producto_titulo').text('Nuevo producto');
        $('#wrap_stock_inicial').show();
        modal.show();
    });

    // ── Editar ──────────────────────────────────────────────────────────────
    $('#tabla_productos').on('click', '.btn-editar', function () {
        var id = $(this).data('id');
        $.ajax({
            url: ajax_url, type: 'GET',
            data: { action: 'get', id: id }, dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') { mostrarError(res.message); return; }
                var p = res.data;
                limpiarFormulario();
                cargarCatalogos(p.id_categoria, p.id_categoria_padre, p.id_unidad_medida,
                    !!parseInt(p.se_fracciona), p.cantidad_presentacion);
                $('#modal_producto_titulo').text('Editar producto');
                $('#producto_id').val(p.id);
                $('#producto_codigo').val(p.codigo);
                $('#producto_nombre').val(p.nombre);
                $('#producto_descripcion').val(p.descripcion);
                $('#producto_stock_minimo').val(p.stock_minimo);
                $('#producto_stock_maximo').val(p.stock_maximo);
                $('#producto_precio_costo').val(p.precio_costo);
                $('#producto_precio_venta').val(p.precio_venta);
                $('#producto_precio_venta_unidad').val(p.precio_venta_unidad || '');
                tsEstado.setValue(String(p.estado));
                $('#wrap_stock_inicial').hide();
                modal.show();
            }
        });
    });

    // ── Submit ──────────────────────────────────────────────────────────────
    $('#form_producto').on('submit', function (e) {
        e.preventDefault();
        if (!validarCamposRequeridos('#form_producto')) { mostrarError('Completa los campos requeridos.'); return; }

        var btn = $('#btn_guardar_producto').prop('disabled', true).text('Guardando...');
        var formData = new FormData(this);
        formData.set('action', 'save');
        formData.set('id_unidad_medida', tsUnidad.getValue());
        formData.set('estado',           tsEstado.getValue());
        if (!$('#producto_se_fracciona').is(':checked')) {
            formData.delete('se_fracciona');
        }
        // Producto nuevo y fraccionable: stock ingresado es en presentaciones, convertir a base
        if (!$('#producto_id').val() && $('#producto_se_fracciona').is(':checked')) {
            var cantPres  = parseFloat($('#producto_cantidad_presentacion').val()) || 1;
            var cantInput = parseFloat($('#producto_stock_actual').val()) || 0;
            formData.set('stock_actual', cantInput * cantPres);
        }

        $.ajax({
            url: ajax_url, type: 'POST',
            data: formData, processData: false, contentType: false, dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') { mostrarError(res.message); return; }
                modal.hide();
                mostrarExito(res.message);
                tabla.ajax.reload(null, false);
            },
            error:    function () { mostrarError('Error de conexion.'); },
            complete: function () { btn.prop('disabled', false).text('Guardar'); }
        });
    });

    // ── Eliminar ────────────────────────────────────────────────────────────
    $('#tabla_productos').on('click', '.btn-eliminar', function () {
        var id = $(this).data('id');
        confirmarEliminacion('El producto sera marcado como eliminado.').then(function (r) {
            if (!r.isConfirmed) return;
            ajaxPost({
                url: ajax_url, data: { action: 'delete', id: id },
                onSuccess: function (res) { mostrarExito(res.message); tabla.ajax.reload(null, false); },
                onError:   function (res) { mostrarError(res.message); }
            });
        });
    });

    function limpiarFormulario() {
        $('#form_producto')[0].reset();
        $('#producto_id').val('');
        $('#producto_id_categoria_final').val('');
        $('#wrap_fracciona').hide();
        $('#wrap_precio_venta_unidad').hide();
        $('#producto_se_fracciona').prop('checked', false);
        $('#producto_cantidad_presentacion').val(1).prop('disabled', true);
        $('#producto_precio_venta_unidad').val('');
        $('#lbl_stock_inicial').text('Stock inicial');
        $('#lbl_stock_minimo').text('Stock mínimo');
        $('#lbl_stock_maximo').text('Stock máximo');
        $('#hint_stock_presentacion').hide();
        tsEstado.setValue('0');
        tsCatPadre.setValue('');
        tsCatHijo.setValue('');
        $('#wrap_stock_inicial').show();
    }

});
