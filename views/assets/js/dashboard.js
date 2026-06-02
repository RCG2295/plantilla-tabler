$(document).ready(function () {

    var ajax_url        = DASH_AJAX_URL;
    var chartVentasDia  = null;
    var chartFormasPago = null;

    // ── Helpers ──────────────────────────────────────────────────────────────
    function numFmt(n) {
        n = parseFloat(n) || 0;
        return n.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fechaFmt(str) {
        if (!str) return '—';
        var p = str.substring(0, 10).split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    }

    function horaFmt(str) {
        if (!str) return '';
        var partes = str.split(' ');
        return partes[1] ? partes[1].substring(0, 5) : '';
    }

    // ── Load all sections ────────────────────────────────────────────────────
    function cargarTodo() {
        if (DASH_PERMISOS.ventas)     cargarVentas();
        if (DASH_PERMISOS.turno)      cargarTurno();
        if (DASH_PERMISOS.compras)    cargarCompras();
        if (DASH_PERMISOS.egresos)    cargarEgresos();
        if (DASH_PERMISOS.inventario) cargarInventario();
    }

    // ── Ventas ───────────────────────────────────────────────────────────────
    function cargarVentas() {
        $.ajax({
            url: ajax_url + '?action=ventas',
            dataType: 'json',
            error: function (xhr) { console.error('Dashboard ventas:', xhr.status, xhr.responseText); }
        }).done(function (res) {
            if (res.status !== 'ok') { console.error('Dashboard ventas error:', res.message); return; }
            var d = res.data;
            var r = d.resumen || {};
            var s = d.semana  || {};

            if (s.desde && s.hoy) {
                $('#dash-periodo').text('Semana del ' + fechaFmt(s.desde) + ' al ' + fechaFmt(s.hoy));
            }

            $('#kpi_num_ventas').text(parseInt(r.num_ventas) || 0);
            $('#kpi_total_ventas').text('$' + numFmt(r.total_ventas));
            $('#kpi_efec_mxn').text('$' + numFmt(r.efectivo_mxn));
            $('#kpi_efec_usd').text('$' + numFmt(r.efectivo_usd));
            $('#kpi_tarjeta').text('$' + numFmt(r.tarjeta));
            $('#kpi_transfer').text('$' + numFmt(r.transferencia));

            var dias   = d.por_dia || [];
            var cats   = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
            var totals = dias.map(function (x) { return parseFloat(x.total) || 0; });
            renderChartVentasDia(cats, totals);

            var fp = [
                parseFloat(r.efectivo_mxn)   || 0,
                parseFloat(r.efectivo_usd)   || 0,
                parseFloat(r.tarjeta)        || 0,
                parseFloat(r.transferencia)  || 0,
            ];
            renderChartFormasPago(fp);

            var ultimas = d.ultimas || [];
            var html = '';
            if (ultimas.length === 0) {
                html = '<tr><td colspan="4" class="text-center text-muted py-3">Sin ventas registradas.</td></tr>';
            } else {
                ultimas.forEach(function (v) {
                    html += '<tr>' +
                        '<td class="ps-3 fw-semibold">' + v.folio + '</td>' +
                        '<td>' + (v.cajero || '—') + '</td>' +
                        '<td class="text-end">$' + numFmt(v.total) + '</td>' +
                        '<td class="text-muted">' + horaFmt(v.fecha_alta) + '</td>' +
                    '</tr>';
                });
            }
            $('#tbody_ultimas_ventas').html(html);
        });
    }

    // ── Turno activo ─────────────────────────────────────────────────────────
    function cargarTurno() {
        $.ajax({
            url: ajax_url + '?action=turno',
            dataType: 'json',
            error: function (xhr) { console.error('Dashboard turno:', xhr.status, xhr.responseText); }
        }).done(function (res) {
            var html = '';
            if (res.status !== 'ok' || !res.data) {
                html = '<div class="text-muted" style="font-size:0.85rem;">Sin turno activo en esta sucursal.</div>';
            } else {
                var t = res.data;
                html =
                    '<div class="fw-bold mb-1" style="font-size:1.1rem;">' + (t.cajero || '—') + '</div>' +
                    '<div class="text-muted mb-2" style="font-size:0.78rem;">Apertura: ' + horaFmt(t.fecha_alta) + '</div>' +
                    '<div class="d-flex justify-content-between mb-1" style="font-size:0.85rem;">' +
                        '<span class="text-muted">Ventas en turno</span>' +
                        '<span class="fw-semibold">' + (parseInt(t.num_ventas) || 0) + '</span>' +
                    '</div>' +
                    '<div class="d-flex justify-content-between mb-2" style="font-size:0.85rem;">' +
                        '<span class="text-muted">Total cobrado</span>' +
                        '<span class="fw-semibold" style="color:#22c55e;">$' + numFmt(t.total_ventas) + '</span>' +
                    '</div>' +
                    '<div class="d-flex justify-content-between" style="font-size:0.78rem;">' +
                        '<span class="text-muted">Fondo MXN</span>' +
                        '<span>$' + numFmt(t.fondo_pesos) + '</span>' +
                    '</div>';
                if (parseFloat(t.fondo_dolares) > 0) {
                    html +=
                    '<div class="d-flex justify-content-between" style="font-size:0.78rem;">' +
                        '<span class="text-muted">Fondo USD</span>' +
                        '<span>$' + numFmt(t.fondo_dolares) + '</span>' +
                    '</div>';
                }
            }
            $('#kpi_turno_body').html(html);
        });
    }

    // ── Compras ──────────────────────────────────────────────────────────────
    function cargarCompras() {
        $.ajax({
            url: ajax_url + '?action=compras',
            dataType: 'json',
            error: function (xhr) { console.error('Dashboard compras:', xhr.status, xhr.responseText); }
        }).done(function (res) {
            if (res.status !== 'ok') return;
            var d = res.data || {};
            $('#kpi_num_compras').text(parseInt(d.num_compras) || 0);
            $('#kpi_total_compras').text('$' + numFmt(d.total_compras));
        });
    }

    // ── Egresos ──────────────────────────────────────────────────────────────
    function cargarEgresos() {
        $.ajax({
            url: ajax_url + '?action=egresos',
            dataType: 'json',
            error: function (xhr) { console.error('Dashboard egresos:', xhr.status, xhr.responseText); }
        }).done(function (res) {
            if (res.status !== 'ok') return;
            var d = res.data || {};
            $('#kpi_num_egresos').text(parseInt(d.num_egresos) || 0);
            $('#kpi_total_egresos').text('$' + numFmt(d.total_egresos));
        });
    }

    // ── Inventario ───────────────────────────────────────────────────────────
    function cargarInventario() {
        $.ajax({
            url: ajax_url + '?action=inventario',
            dataType: 'json',
            error: function (xhr) { console.error('Dashboard inventario:', xhr.status, xhr.responseText); }
        }).done(function (res) {
            if (res.status !== 'ok') return;
            var d  = res.data || {};
            var r  = d.resumen    || {};
            var sb = d.stock_bajo || [];

            $('#badge_bajo_stock').text(parseInt(r.bajo_stock) || 0);

            var html = '';
            if (sb.length === 0) {
                html = '<tr><td colspan="3" class="text-center text-muted py-3">Sin productos bajo stock.</td></tr>';
            } else {
                sb.forEach(function (p) {
                    var actual = parseFloat(p.stock_actual);
                    var minimo = parseFloat(p.stock_minimo);
                    var pct    = minimo > 0 ? actual / minimo : 1;
                    var cls    = pct < 0.5 ? 'text-danger fw-bold' : 'text-warning fw-semibold';
                    var uni    = p.unidad || '';
                    html += '<tr>' +
                        '<td class="ps-3">' +
                            '<div class="fw-semibold">' + p.nombre + '</div>' +
                            (p.codigo ? '<div class="text-muted" style="font-size:0.76rem;">' + p.codigo + '</div>' : '') +
                        '</td>' +
                        '<td class="text-center ' + cls + '">' + actual.toFixed(2) + ' <span class="fw-normal text-muted">' + uni + '</span></td>' +
                        '<td class="text-center text-muted">' + minimo.toFixed(2) + ' <span>' + uni + '</span></td>' +
                    '</tr>';
                });
            }
            $('#tbody_stock_bajo').html(html);
        });
    }

    // ── ApexCharts ───────────────────────────────────────────────────────────
    function renderChartVentasDia(cats, totals) {
        if (chartVentasDia) {
            chartVentasDia.updateOptions({ xaxis: { categories: cats } });
            chartVentasDia.updateSeries([{ name: 'Ventas ($)', data: totals }]);
            return;
        }
        chartVentasDia = new ApexCharts(document.querySelector('#chart_ventas_dia'), {
            chart: {
                type: 'bar',
                height: 260,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            series: [{ name: 'Ventas ($)', data: totals }],
            xaxis: { categories: cats, labels: { style: { fontSize: '12px' } } },
            yaxis: {
                labels: {
                    formatter: function (v) { return '$' + v.toLocaleString('es-MX'); }
                }
            },
            colors: ['var(--color-primario)'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            tooltip: {
                y: { formatter: function (v) { return '$' + numFmt(v); } }
            },
        });
        chartVentasDia.render();
    }

    function renderChartFormasPago(values) {
        var total  = values.reduce(function (a, b) { return a + b; }, 0);
        var labels = ['Efectivo MXN', 'Efectivo USD', 'Tarjeta', 'Transferencia'];
        var el     = document.querySelector('#chart_formas_pago');

        if (!el) return;

        if (total === 0) {
            el.innerHTML = '<div class="text-center text-muted py-4" style="font-size:0.85rem;">Sin ventas esta semana.</div>';
            return;
        }

        if (chartFormasPago) {
            chartFormasPago.updateSeries(values);
            return;
        }

        chartFormasPago = new ApexCharts(el, {
            chart: {
                type: 'donut',
                height: 260,
                fontFamily: 'inherit',
            },
            series: values,
            labels: labels,
            colors: ['var(--color-primario)', '#3b82f6', '#f59e0b', '#8b5cf6'],
            legend: { position: 'bottom', fontSize: '12px' },
            dataLabels: {
                enabled: true,
                formatter: function (val) { return val.toFixed(1) + '%'; },
            },
            tooltip: {
                y: { formatter: function (v) { return '$' + numFmt(v); } }
            },
            plotOptions: { pie: { donut: { size: '60%' } } },
        });
        chartFormasPago.render();
    }

    // ── Refresh ──────────────────────────────────────────────────────────────
    $('#btn_refresh_dash').on('click', function () {
        if (chartVentasDia)  { chartVentasDia.destroy();  chartVentasDia  = null; }
        if (chartFormasPago) { chartFormasPago.destroy(); chartFormasPago = null; }
        if (DASH_PERMISOS.ventas && document.querySelector('#chart_ventas_dia')) {
            document.querySelector('#chart_ventas_dia').innerHTML  = '';
            document.querySelector('#chart_formas_pago').innerHTML = '';
        }
        cargarTodo();
    });

    cargarTodo();

});
