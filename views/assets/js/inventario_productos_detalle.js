$(document).ready(function () {

    var app_url      = $('meta[name="app-url"]').attr('content');
    var ajax_url     = app_url + '/views/ajax/ajax_inventario_productos.php';
    var ajax_cats    = app_url + '/views/ajax/ajax_inventario_categorias.php';
    var ajax_unids   = app_url + '/views/ajax/ajax_inventario_unidades.php';
    var ajax_motivos = app_url + '/views/ajax/ajax_inventario_motivos.php';
    var id_producto  = window.PRODUCTO_ID;
    var permisos     = window.PERMISOS_PRODUCTOS || { editar: false };
    var stockActual  = 0;
    var seFragciona    = false;
    var cantPresentacion = 1;
    var unidadMap    = {};
    var tablaHistorial;

    var modalProducto   = new bootstrap.Modal(document.getElementById('modal_producto'));
    var modalMovimiento = new bootstrap.Modal(document.getElementById('modal_movimiento'));

    var tsCatPadre = new TomSelect('#producto_cat_padre',        { create: false });
    var tsCatHijo  = new TomSelect('#producto_id_categoria',     { create: false });
    var tsUnidad   = new TomSelect('#producto_id_unidad_medida', { create: false });
    var tsEstado   = new TomSelect('#producto_estado',           { create: false });
    var tsTipoMov  = new TomSelect('#mov_tipo',                  { create: false });
    var tsMotivo   = new TomSelect('#mov_id_motivo',             { create: false });

    function formatoPrecio(v) { return '$' + parseFloat(v || 0).toFixed(2); }

    function colorStock(stock, min, seFragc, cantPres) {
        if (stock <= 0) return '#e74c3c';
        var comparador = (seFragc && cantPres > 0) ? stock / cantPres : stock;
        if (comparador < min) return '#f39c12';
        return 'var(--color-exito)';
    }

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

    // ── Fraccionamiento en modal editar ─────────────────────────────────────
    tsUnidad.on('change', function (val) {
        var u = unidadMap[val];
        if (u && u.abreviatura !== 'pza') {
            $('#wrap_fracciona').show();
        } else {
            $('#wrap_fracciona').hide();
            $('#producto_se_fracciona').prop('checked', false).trigger('change');
            $('#producto_cantidad_presentacion').val(1).prop('disabled', true);
        }
    });

    $('#producto_se_fracciona').on('change', function () {
        if ($(this).is(':checked')) {
            $('#producto_cantidad_presentacion').prop('disabled', false);
            $('#wrap_precio_venta_unidad').show();
            $('#lbl_stock_minimo').text('Stock mínimo (presentaciones)');
            $('#lbl_stock_maximo').text('Stock máximo (presentaciones)');
        } else {
            $('#producto_cantidad_presentacion').val(1).prop('disabled', true);
            $('#wrap_precio_venta_unidad').hide();
            $('#producto_precio_venta_unidad').val('');
            $('#lbl_stock_minimo').text('Stock mínimo');
            $('#lbl_stock_maximo').text('Stock máximo');
        }
    });

    function cargarCatalogosEditar(p) {
        $.getJSON(ajax_cats + '?action=padres', function (r) {
            tsCatPadre.clearOptions();
            tsCatPadre.addOption({ value: '', text: 'Sin categoria' });
            $.each(r.data || [], function (_, c) {
                tsCatPadre.addOption({ value: String(c.id), text: c.nombre });
            });
            var padreVal = p.id_categoria_padre ? String(p.id_categoria_padre) : (p.id_categoria ? String(p.id_categoria) : '');
            tsCatPadre.setValue(padreVal);
            tsCatPadre.refreshOptions(false);

            var hijoVal = p.id_categoria_padre ? p.id_categoria : null;
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
            tsUnidad.setValue(p.id_unidad_medida ? String(p.id_unidad_medida) : '');
            tsUnidad.refreshOptions(false);

            if (parseInt(p.se_fracciona)) {
                $('#producto_se_fracciona').prop('checked', true);
                $('#producto_cantidad_presentacion').val(p.cantidad_presentacion || 1).prop('disabled', false);
                $('#wrap_precio_venta_unidad').show();
                $('#lbl_stock_minimo').text('Stock mínimo (presentaciones)');
                $('#lbl_stock_maximo').text('Stock máximo (presentaciones)');
            } else {
                $('#producto_se_fracciona').prop('checked', false);
                $('#producto_cantidad_presentacion').val(1).prop('disabled', true);
                $('#wrap_precio_venta_unidad').hide();
                $('#lbl_stock_minimo').text('Stock mínimo');
                $('#lbl_stock_maximo').text('Stock máximo');
            }
        });
    }

    // ── Texto de stock con presentaciones ───────────────────────────────────
    function stockTexto(stockVal, ab) {
        var txt = parseFloat(stockVal).toFixed(2) + ' ' + (ab || '');
        if (seFragciona && cantPresentacion > 0) {
            var pres = Math.floor(parseFloat(stockVal) / cantPresentacion);
            txt += ' (' + pres + ' pza)';
        }
        return txt;
    }

    // ── Cargar producto ─────────────────────────────────────────────────────
    function cargarProducto() {
        $.getJSON(ajax_url + '?action=get&id=' + id_producto, function (res) {
            if (res.status !== 'ok') return;
            var p = res.data;
            stockActual      = parseFloat(p.stock_actual);
            seFragciona      = !!parseInt(p.se_fracciona);
            cantPresentacion = parseFloat(p.cantidad_presentacion) || 1;

            var color = colorStock(stockActual, parseFloat(p.stock_minimo), seFragciona, cantPresentacion);
            $('#ficha_stock_actual').text(parseFloat(p.stock_actual).toFixed(2)).css('color', color);
            $('#card_stock').css('border-top', '3px solid ' + color);

            if (seFragciona && cantPresentacion > 0) {
                var pres = Math.floor(stockActual / cantPresentacion);
                $('#ficha_unidad').text((p.abreviatura || '') + ' (' + pres + ' pza)');
                $('#ficha_stock_minimo').text(parseFloat(p.stock_minimo).toFixed(0) + ' pza');
                $('#ficha_stock_maximo').text(parseFloat(p.stock_maximo).toFixed(0) + ' pza');
            } else {
                $('#ficha_unidad').text(p.abreviatura || '');
                $('#ficha_stock_minimo').text(parseFloat(p.stock_minimo).toFixed(2));
                $('#ficha_stock_maximo').text(parseFloat(p.stock_maximo).toFixed(2));
            }

            var costo  = parseFloat(p.precio_costo);
            var venta  = parseFloat(p.precio_venta);
            var margen = costo > 0 ? (((venta - costo) / costo) * 100).toFixed(1) + '%' : '-';
            $('#ficha_precio_costo').text(formatoPrecio(costo));
            $('#ficha_precio_venta').text(formatoPrecio(venta));
            $('#ficha_margen').text(margen);

            if (seFragciona && p.precio_venta_unidad !== null && p.precio_venta_unidad !== '') {
                $('#ficha_precio_venta_unidad').text(formatoPrecio(p.precio_venta_unidad));
                $('#wrap_ficha_precio_unidad').show();
            } else {
                $('#wrap_ficha_precio_unidad').hide();
            }

            if (p.descripcion) {
                $('#ficha_descripcion').text(p.descripcion);
                $('#card_descripcion').show();
            }

            $('#mov_stock_actual').text(stockTexto(p.stock_actual, p.abreviatura));

            renderGaleria(p.fotos || []);
        });
    }

    // ── Galeria ─────────────────────────────────────────────────────────────
    function renderGaleria(fotos) {
        var base = app_url + '/views/uploads/inventario_productos/';
        if (!fotos.length) {
            $('#galeria_placeholder').show();
            $('#foto_principal_img').hide();
            $('#galeria_thumbs').empty();
            return;
        }

        var principal = fotos.find(function (f) { return f.es_principal == 1; }) || fotos[0];
        $('#galeria_placeholder').hide();
        $('#foto_principal_img').attr('src', base + principal.nombre_archivo).show();

        var thumbs = '';
        $.each(fotos, function (_, f) {
            var active = f.es_principal == 1 ? ' border border-primary border-2' : '';
            thumbs += '<div class="position-relative" style="width:56px;">';
            thumbs += '<img src="' + base + f.nombre_archivo + '" class="rounded' + active + '"'
                    + ' style="width:56px;height:56px;object-fit:cover;cursor:pointer;"'
                    + ' data-id="' + f.id + '" data-src="' + base + f.nombre_archivo + '">';
            if (permisos.editar) {
                thumbs += '<button class="btn-close btn-close-white btn-del-foto position-absolute top-0 end-0"'
                        + ' style="font-size:0.5rem;background:#e74c3c;border-radius:50%;padding:3px;" data-id="' + f.id + '"></button>';
                if (!f.es_principal) {
                    thumbs += '<div class="text-center btn-set-principal" style="font-size:0.6rem;cursor:pointer;" data-id="' + f.id + '">Principal</div>';
                }
            }
            thumbs += '</div>';
        });
        $('#galeria_thumbs').html(thumbs);
    }

    $('#galeria_thumbs').on('click', 'img', function () {
        $('#foto_principal_img').attr('src', $(this).data('src'));
    });

    $('#galeria_thumbs').on('click', '.btn-del-foto', function (e) {
        e.stopPropagation();
        var id = $(this).data('id');
        confirmarEliminacion('La foto sera eliminada.').then(function (r) {
            if (!r.isConfirmed) return;
            ajaxPost({
                url: ajax_url, data: { action: 'delete_foto', id: id },
                onSuccess: function () { cargarProducto(); }
            });
        });
    });

    $('#galeria_thumbs').on('click', '.btn-set-principal', function (e) {
        e.stopPropagation();
        var id = $(this).data('id');
        ajaxPost({
            url: ajax_url,
            data: { action: 'set_foto_principal', id_producto: id_producto, id_foto: id },
            onSuccess: function () { cargarProducto(); }
        });
    });

    // ── Subir fotos adicionales ─────────────────────────────────────────────
    $('#btn_subir_fotos').on('click', function () {
        var input = document.getElementById('input_fotos_extra');
        if (!input.files.length) { mostrarError('Selecciona al menos una foto.'); return; }

        $.getJSON(ajax_url + '?action=get&id=' + id_producto, function (res) {
            if (res.status !== 'ok') return;
            var p   = res.data;
            var fd2 = new FormData();
            fd2.append('action', 'save');
            fd2.append('id', id_producto);
            fd2.append('codigo', p.codigo);
            fd2.append('nombre', p.nombre);
            fd2.append('descripcion', p.descripcion || '');
            fd2.append('id_categoria', p.id_categoria || '');
            fd2.append('id_unidad_medida', p.id_unidad_medida || '');
            fd2.append('stock_minimo', p.stock_minimo);
            fd2.append('stock_maximo', p.stock_maximo);
            fd2.append('precio_costo', p.precio_costo);
            fd2.append('precio_venta', p.precio_venta);
            fd2.append('se_fracciona', parseInt(p.se_fracciona) ? '1' : '');
            fd2.append('cantidad_presentacion', p.cantidad_presentacion || 1);
            fd2.append('precio_venta_unidad', p.precio_venta_unidad || '');
            fd2.append('estado', p.estado);
            for (var i = 0; i < input.files.length; i++) {
                fd2.append('fotos[]', input.files[i]);
            }
            $.ajax({
                url: ajax_url, type: 'POST',
                data: fd2, processData: false, contentType: false, dataType: 'json',
                success: function (res) {
                    if (res.status !== 'ok') { mostrarError(res.message); return; }
                    mostrarExito('Fotos subidas correctamente.');
                    input.value = '';
                    cargarProducto();
                }
            });
        });
    });

    // ── Editar producto ─────────────────────────────────────────────────────
    $('#btn_editar_producto').on('click', function () {
        $.getJSON(ajax_url + '?action=get&id=' + id_producto, function (res) {
            if (res.status !== 'ok') { mostrarError(res.message); return; }
            var p = res.data;
            $('#producto_id_categoria_final').val('');
            $('#wrap_fracciona').hide();
            $('#producto_se_fracciona').prop('checked', false);
            $('#producto_cantidad_presentacion').val(1).prop('disabled', true);
            cargarCatalogosEditar(p);
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
            modalProducto.show();
        });
    });

    $('#form_producto').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#btn_guardar_producto').prop('disabled', true).text('Guardando...');
        var fd  = new FormData(this);
        fd.set('action', 'save');
        fd.set('id_unidad_medida', tsUnidad.getValue());
        fd.set('estado',           tsEstado.getValue());
        if (!$('#producto_se_fracciona').is(':checked')) {
            fd.delete('se_fracciona');
        }

        $.ajax({
            url: ajax_url, type: 'POST',
            data: fd, processData: false, contentType: false, dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') { mostrarError(res.message); return; }
                modalProducto.hide();
                mostrarExito(res.message);
                cargarProducto();
            },
            error:    function () { mostrarError('Error de conexion.'); },
            complete: function () { btn.prop('disabled', false).text('Guardar cambios'); }
        });
    });

    // ── Modal movimiento ────────────────────────────────────────────────────
    function cargarMotivosPorTipo(tipo) {
        $.getJSON(ajax_motivos + '?action=list_by_tipo&tipo=' + tipo, function (res) {
            tsMotivo.clearOptions();
            tsMotivo.addOption({ value: '', text: 'Sin motivo' });
            $.each(res.data || [], function (_, m) {
                tsMotivo.addOption({ value: String(m.id), text: m.nombre });
            });
            tsMotivo.setValue('');
            tsMotivo.refreshOptions(false);
        });
    }

    function actualizarLabelCantidad(modo) {
        if (modo === 'presentacion') {
            $('#lbl_mov_cantidad').text('Cantidad (presentaciones)');
            $('#mov_cantidad').attr('step', '1').attr('min', '1');
        } else {
            $('#lbl_mov_cantidad').text('Cantidad');
            $('#mov_cantidad').attr('step', '0.01').attr('min', '0.01');
        }
    }

    $('input[name="mov_modo"]').on('change', function () {
        actualizarLabelCantidad($(this).val());
    });

    tsTipoMov.on('change', function (val) { cargarMotivosPorTipo(val); });

    $('#btn_nuevo_movimiento').on('click', function () {
        $('#form_movimiento')[0].reset();
        tsTipoMov.setValue('entrada');
        cargarMotivosPorTipo('entrada');
        $('#mov_stock_actual').text(stockTexto(stockActual, ''));
        cargarProducto();

        if (seFragciona) {
            $('#wrap_mov_toggle').show();
            $('input[name="mov_modo"][value="cantidad"]').prop('checked', true);
            actualizarLabelCantidad('cantidad');
        } else {
            $('#wrap_mov_toggle').hide();
        }

        modalMovimiento.show();
    });

    $('#form_movimiento').on('submit', function (e) {
        e.preventDefault();
        var btn = $('#btn_guardar_movimiento').prop('disabled', true).text('Registrando...');

        var modo      = $('input[name="mov_modo"]:checked').val() || 'cantidad';
        var cantInput = parseFloat($('#mov_cantidad').val()) || 0;
        var cantFinal = (modo === 'presentacion' && seFragciona) ? cantInput * cantPresentacion : cantInput;

        if (cantFinal <= 0) {
            mostrarError('La cantidad debe ser mayor a 0.');
            btn.prop('disabled', false).text('Registrar');
            return;
        }

        ajaxPost({
            url: ajax_url,
            data: {
                action:      'movimiento',
                id_producto: id_producto,
                tipo:        tsTipoMov.getValue(),
                cantidad:    cantFinal,
                id_motivo:   tsMotivo.getValue(),
                notas:       $('#mov_notas').val().trim()
            },
            onSuccess: function (res) {
                modalMovimiento.hide();
                mostrarExito(res.message);
                stockActual = parseFloat(res.stock_nuevo);
                cargarProducto();
                tablaHistorial.ajax.reload(null, false);
            },
            onError:    function (res) { mostrarError(res.message); },
            onComplete: function ()    { btn.prop('disabled', false).text('Registrar'); }
        });
    });

    // ── Historial ───────────────────────────────────────────────────────────
    tablaHistorial = $('#tabla_historial').DataTable({
        ajax: {
            url:     ajax_url + '?action=movimientos&id_producto=' + id_producto,
            type:    'GET',
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'fecha_alta',
                render: function (d) { return d ? d.replace('T', ' ').substring(0, 16) : '-'; }
            },
            {
                data: 'tipo',
                render: function (d) {
                    return d === 'entrada'
                        ? '<span class="badge bg-green-lt text-green"><i class="ti ti-arrow-up me-1"></i>Entrada</span>'
                        : '<span class="badge bg-red-lt text-red"><i class="ti ti-arrow-down me-1"></i>Salida</span>';
                }
            },
            { data: 'cantidad',       render: function (d) { return parseFloat(d).toFixed(2); } },
            { data: 'stock_anterior', render: function (d) { return parseFloat(d).toFixed(2); } },
            { data: 'stock_nuevo',    render: function (d) { return parseFloat(d).toFixed(2); } },
            { data: 'motivo',  render: function (d) { return d || '<span class="text-muted">-</span>'; } },
            { data: 'usuario', render: function (d) { return d || '<span class="text-muted">-</span>'; } },
            { data: 'notas',   render: function (d) { return d || '<span class="text-muted">-</span>'; } }
        ],
        language: { url: window.DT_LANG_URL },
        pageLength: 20,
        order: [[0, 'desc']],
        responsive: true
    });

    // ── Init ─────────────────────────────────────────────────────────────────
    cargarProducto();

});
