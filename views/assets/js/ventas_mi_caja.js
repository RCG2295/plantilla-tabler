$(function () {
    var ajax_caja = APP_URL_MC + '/views/ajax/ajax_ventas_caja.php';
    var ajax_mc   = APP_URL_MC + '/views/ajax/ajax_ventas_mi_caja.php';
    var tablaVentas, tablaMovimientos;
    var idTurno = null;
    var denFijas = { pesos: { billetes: [], monedas: [] }, dolares: { billetes: [] } };
    var esperadoPesos = 0, esperadoDolares = 0;

    function fmtMXN(n) { return '$' + parseFloat(n || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function fmtDate(s) { if (!s) return '—'; var d = new Date(s.replace(' ', 'T')); return d.toLocaleString('es-MX'); }

    // ── Denominaciones ───────────────────────────────────────────────────────

    function buildDenominacionesUI(moneda, momento, containerId) {
        var $c = $('#' + containerId);
        $c.empty();
        var grupos = moneda === 'pesos' ? [
            { tipo: 'billete', items: denFijas.pesos.billetes },
            { tipo: 'moneda',  items: denFijas.pesos.monedas  }
        ] : [
            { tipo: 'billete', items: denFijas.dolares.billetes }
        ];
        grupos.forEach(function (g) {
            if (!g.items.length) return;
            g.items.forEach(function (den) {
                var id = 'den_' + moneda + '_' + g.tipo + '_' + String(den).replace('.', '_') + '_' + momento;
                $c.append(
                    '<div class="col-6 col-sm-4 col-md-3">' +
                        '<div class="input-group input-group-sm">' +
                            '<span class="input-group-text" style="font-size:.78rem;min-width:70px;">' +
                                (moneda === 'pesos' ? '$' : 'USD ') + den +
                            '</span>' +
                            '<input type="number" id="' + id + '" class="form-control den-input" ' +
                                'min="0" step="1" placeholder="0" ' +
                                'data-moneda="' + moneda + '" data-tipo="' + g.tipo + '" data-den="' + den + '">' +
                        '</div>' +
                    '</div>'
                );
            });
        });
    }

    function collectDenominaciones(momento) {
        var dens = [];
        $('#den_pesos_' + momento + ' .den-input, #den_dolares_' + momento + ' .den-input').each(function () {
            var qty = parseInt($(this).val()) || 0;
            if (qty > 0) {
                dens.push({
                    moneda:       $(this).data('moneda'),
                    tipo:         $(this).data('tipo'),
                    denominacion: $(this).data('den'),
                    cantidad:     qty
                });
            }
        });
        return dens;
    }

    function loadDenFijas(cb) {
        $.getJSON(ajax_caja + '?action=denominaciones_fijas', function (res) {
            if (res.status === 'ok') {
                denFijas = res.data;
                if (cb) cb();
            }
        });
    }

    // ── Estados de pantalla ──────────────────────────────────────────────────

    function mostrarSinTurno() {
        $('#bloque_cargando').hide();
        $('#bloque_con_turno').hide();
        $('#btns_turno_activo').addClass('d-none');
        buildDenominacionesUI('pesos',   'apertura', 'den_pesos_apertura');
        buildDenominacionesUI('dolares', 'apertura', 'den_dolares_apertura');
        $('#bloque_sin_turno').show();
    }

    function mostrarConTurno(turno) {
        idTurno = turno.id;
        $('#bloque_cargando').hide();
        $('#bloque_sin_turno').hide();
        $('#info_fondo_pesos').text(fmtMXN(turno.fondo_pesos));
        $('#info_fondo_dolares').text('$' + parseFloat(turno.fondo_dolares || 0).toFixed(2));
        $('#info_fecha_alta').text(fmtDate(turno.fecha_alta));
        $('#cierre_id_turno').val(turno.id);
        $('#btns_turno_activo').removeClass('d-none');
        $('#bloque_con_turno').show();
        cargarTodo();
    }

    // ── Datos del turno activo ───────────────────────────────────────────────

    function initTablas() {
        tablaVentas = $('#tabla_ventas_mc').DataTable({
            language: { url: window.DT_LANG_URL },
            order: [[3, 'desc']],
            columns: [
                { data: 'folio' },
                { data: 'total', render: function (v) { return fmtMXN(v); } },
                { data: 'formas_pago', defaultContent: '—', render: function (v) {
                    if (!v) return '—';
                    var map = { efectivo_pesos: 'Ef. MXN', efectivo_dolares: 'Ef. USD', tarjeta: 'Tarjeta', transferencia: 'Transf.' };
                    return v.split(', ').map(function (k) { return map[k] || k; }).join(', ');
                }},
                { data: 'fecha_alta', render: fmtDate },
                { data: 'estado', render: function (v) {
                    if (v == 0) return '<span class="badge" style="background:var(--color-exito);color:#fff;">Activa</span>';
                    return '<span class="badge bg-danger" style="color:#fff;">Cancelada</span>';
                }},
                { data: 'id', orderable: false, className: 'text-center',
                  render: function (id) {
                    return '<button class="btn btn-sm btn-outline-secondary btn-ver-ticket-mc" data-id="' + id + '" title="Ver ticket"><i class="ti ti-printer"></i></button>';
                }},
            ],
        });

        $('#tabla_ventas_mc').on('click', '.btn-ver-ticket-mc', function () {
            window.open(APP_URL_MC + '/views/tickets/venta_ticket.php?id=' + $(this).data('id'), '_blank');
        });

        tablaMovimientos = $('#tabla_movimientos_mc').DataTable({
            language: { url: window.DT_LANG_URL },
            order: [[5, 'desc']],
            columns: [
                { data: 'tipo', render: function (v) {
                    if (v === 'retiro') return '<span class="badge bg-danger-lt text-danger"><i class="ti ti-arrow-up me-1"></i>Retiro</span>';
                    return '<span class="badge bg-success-lt text-success"><i class="ti ti-arrow-down me-1"></i>Ingreso</span>';
                }},
                { data: 'moneda', render: function (v) { return v === 'pesos' ? 'MXN' : 'USD'; } },
                { data: 'monto', render: function (v) { return '$' + parseFloat(v || 0).toFixed(2); } },
                { data: 'descripcion', defaultContent: '—' },
                { data: 'usuario', defaultContent: '—' },
                { data: 'fecha_alta', render: fmtDate },
            ],
        });
    }

    function cargarResumen() {
        $.getJSON(ajax_mc + '?action=resumen&id_turno=' + idTurno, function (res) {
            if (res.status === 'ok' && res.data) {
                var d = res.data;
                esperadoPesos   = parseFloat(d.efectivo_esperado_pesos   || 0);
                esperadoDolares = parseFloat(d.efectivo_esperado_dolares || 0);
                $('#res_num_ventas').text(d.num_ventas);
                $('#res_total_ventas').text(fmtMXN(d.total_ventas));
                $('#res_efectivo_pesos').text(fmtMXN(d.efectivo_pesos));
                $('#res_efectivo_dolares').text('$' + parseFloat(d.efectivo_dolares || 0).toFixed(2));
                $('#res_tarjeta').text(fmtMXN(d.tarjeta));
                $('#res_transferencia').text(fmtMXN(d.transferencia));
                $('#res_esperado_pesos').text(fmtMXN(esperadoPesos));
                $('#res_esperado_dolares').text('$' + esperadoDolares.toFixed(2));
            }
        });
    }

    function cargarVentas() {
        $.getJSON(ajax_mc + '?action=ventas&id_turno=' + idTurno, function (res) {
            if (res.status === 'ok') tablaVentas.clear().rows.add(res.data).draw();
        });
    }

    function cargarMovimientos() {
        $.getJSON(ajax_mc + '?action=movimientos&id_turno=' + idTurno, function (res) {
            if (res.status === 'ok') tablaMovimientos.clear().rows.add(res.data).draw();
        });
    }

    function cargarTodo() {
        cargarResumen();
        cargarVentas();
        cargarMovimientos();
    }

    // ── Inicio ───────────────────────────────────────────────────────────────

    initTablas();

    loadDenFijas(function () {
        $.getJSON(ajax_caja + '?action=turno_activo', function (res) {
            if (res.status === 'ok' && res.data) {
                mostrarConTurno(res.data);
            } else {
                mostrarSinTurno();
            }
        });
    });

    // Reload al cambiar de pestaña
    $('[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        if (idTurno) { cargarVentas(); cargarMovimientos(); }
    });

    // ── Iniciar turno ────────────────────────────────────────────────────────

    $('#form_iniciar_turno').on('submit', function (e) {
        e.preventDefault();
        var dens = collectDenominaciones('apertura');
        $.ajax({
            url: ajax_caja,
            method: 'POST',
            data: {
                action:        'iniciar',
                fondo_pesos:   $('#fondo_pesos').val() || 0,
                fondo_dolares: $('#fondo_dolares').val() || 0,
                denominaciones: JSON.stringify(dens),
            },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'ok') {
                    mostrarExito(res.message);
                    $.getJSON(ajax_caja + '?action=turno_activo', function (r) {
                        if (r.status === 'ok' && r.data) mostrarConTurno(r.data);
                    });
                } else {
                    mostrarError(res.message);
                }
            }
        });
    });

    // ── Cerrar turno ─────────────────────────────────────────────────────────

    $('#btn_abrir_cerrar_turno').on('click', function () {
        $('#declarado_pesos').val('');
        $('#declarado_dolares').val('');
        buildDenominacionesUI('pesos',   'cierre', 'den_pesos_cierre');
        buildDenominacionesUI('dolares', 'cierre', 'den_dolares_cierre');
        new bootstrap.Modal(document.getElementById('modal_cerrar_turno')).show();
    });

    $('#form_cerrar_turno').on('submit', function (e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Cerrar turno?',
            text: 'Esta acción cerrará el turno y generará el corte de caja.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cerrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            var dens = collectDenominaciones('cierre');
            $.ajax({
                url: ajax_caja,
                method: 'POST',
                data: {
                    action:            'cerrar',
                    id_turno:          $('#cierre_id_turno').val(),
                    declarado_pesos:   $('#declarado_pesos').val() || 0,
                    declarado_dolares: $('#declarado_dolares').val() || 0,
                    denominaciones:    JSON.stringify(dens),
                },
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'ok') {
                        bootstrap.Modal.getInstance(document.getElementById('modal_cerrar_turno')).hide();
                        mostrarExito(res.message);
                        idTurno = null;
                        mostrarSinTurno();
                    } else {
                        mostrarError(res.message);
                    }
                }
            });
        });
    });

    // ── Retiro / Ingreso ─────────────────────────────────────────────────────

    $('#btn_retiro').on('click', function () { abrirMovimiento('retiro'); });
    $('#btn_ingreso').on('click', function () { abrirMovimiento('ingreso'); });

    function abrirMovimiento(tipo) {
        $('#mov_tipo').val(tipo);
        $('#mov_id_turno').val(idTurno);
        $('#mov_monto').val('');
        $('#mov_descripcion').val('');
        $('#mov_moneda').val('pesos');
        $('#modal_movimiento_titulo').text(tipo === 'retiro' ? 'Retiro de efectivo' : 'Ingreso de efectivo');
        new bootstrap.Modal(document.getElementById('modal_movimiento')).show();
    }

    $('#form_movimiento').on('submit', function (e) {
        e.preventDefault();
        var tipo   = $('#mov_tipo').val();
        var moneda = $('#mov_moneda').val();
        var monto  = parseFloat($('#mov_monto').val()) || 0;

        if (tipo === 'retiro') {
            var disponible = moneda === 'pesos' ? esperadoPesos : esperadoDolares;
            var label      = moneda === 'pesos' ? 'MXN' : 'USD';
            if (monto > disponible) {
                mostrarError('Fondos insuficientes. Disponible en caja: $' + disponible.toFixed(2) + ' ' + label + '.');
                return;
            }
        }

        $.post(ajax_mc, {
            action:      'movimiento',
            id_turno:    $('#mov_id_turno').val(),
            tipo:        $('#mov_tipo').val(),
            moneda:      $('#mov_moneda').val(),
            monto:       $('#mov_monto').val(),
            descripcion: $('#mov_descripcion').val(),
        }, function (res) {
            if (res.status === 'ok') {
                bootstrap.Modal.getInstance(document.getElementById('modal_movimiento')).hide();
                cargarTodo();
                mostrarExito(res.message);
            } else {
                mostrarError(res.message);
            }
        }, 'json');
    });
});
