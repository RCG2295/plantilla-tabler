$(document).ready(function () {

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_admin_sucursales.php';
    var permisos = window.PERMISOS_SUCURSALES || {};
    var modal    = new bootstrap.Modal(document.getElementById('modal_sucursal'));
    var tabla;

    // ── DataTable ────────────────────────────────────────────────────────────
    tabla = $('#tabla_sucursales').DataTable({
        ajax: { url: ajax_url + '?action=list', type: 'GET', dataSrc: 'data' },
        columns: [
            { data: 'nombre', render: function (d) { return '<span class="fw-semibold">' + d + '</span>'; } },
            { data: 'direccion', render: function (d) { return d || '<span class="text-muted">—</span>'; } },
            { data: 'telefono',  render: function (d) { return d || '<span class="text-muted">—</span>'; } },
            {
                data: 'estado', className: 'text-center',
                render: function (d) {
                    if (d == 0) return '<span class="badge" style="background:var(--color-exito);color:#fff;">Activa</span>';
                    return '<span class="badge bg-secondary" style="color:#fff;">Inactiva</span>';
                }
            },
            {
                data: null, orderable: false, className: 'text-center',
                render: function (_, __, row) {
                    var html = '';
                    if (permisos.editar) {
                        html += '<button class="btn btn-sm btn-outline-primary me-1 btn-editar" data-id="' + row.id + '" title="Editar"><i class="ti ti-pencil"></i></button>';
                    }
                    if (permisos.eliminar) {
                        html += '<button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="' + row.id + '" data-nombre="' + row.nombre + '" title="Eliminar"><i class="ti ti-trash"></i></button>';
                    }
                    return html || '<span class="text-muted">—</span>';
                }
            }
        ],
        language: { url: window.DT_LANG_URL },
        pageLength: 15,
        order: [[0, 'asc']],
        responsive: true
    });

    // ── Nueva sucursal ───────────────────────────────────────────────────────
    $('#btn_nueva_sucursal').on('click', function () {
        limpiarModal();
        $('#modal_sucursal_titulo').text('Nueva sucursal');
        modal.show();
    });

    // ── Editar ───────────────────────────────────────────────────────────────
    $('#tabla_sucursales').on('click', '.btn-editar', function () {
        var id = $(this).data('id');
        $.getJSON(ajax_url + '?action=get&id=' + id, function (res) {
            if (res.status !== 'ok') { mostrarError(res.message); return; }
            var s = res.data;
            limpiarModal();
            $('#modal_sucursal_titulo').text('Editar sucursal');
            $('#sucursal_id').val(s.id);
            $('#sucursal_nombre').val(s.nombre);
            $('#sucursal_direccion').val(s.direccion || '');
            $('#sucursal_telefono').val(s.telefono || '');
            $('#sucursal_estado').val(s.estado);
            modal.show();
        });
    });

    // ── Submit ───────────────────────────────────────────────────────────────
    $('#form_sucursal').on('submit', function (e) {
        e.preventDefault();
        if (!validarCamposRequeridos('#form_sucursal')) { mostrarError('Completa los campos requeridos.'); return; }
        var btn = $('#btn_guardar_sucursal').prop('disabled', true).html('<i class="ti ti-loader me-1"></i>Guardando...');

        ajaxPost({
            url: ajax_url,
            data: $(this).serialize() + '&action=save',
            onSuccess: function (res) {
                modal.hide();
                mostrarExito(res.message);
                tabla.ajax.reload(null, false);
            },
            onError:    function (res) { mostrarError(res.message); },
            onComplete: function ()    { btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i>Guardar'); }
        });
    });

    // ── Eliminar ─────────────────────────────────────────────────────────────
    $('#tabla_sucursales').on('click', '.btn-eliminar', function () {
        var id     = $(this).data('id');
        var nombre = $(this).data('nombre');
        confirmarEliminacion('Se eliminará la sucursal "' + nombre + '". Esta acción no se puede deshacer.').then(function (r) {
            if (!r.isConfirmed) return;
            ajaxPost({
                url: ajax_url,
                data: { action: 'delete', id: id },
                onSuccess: function (res) { mostrarExito(res.message); tabla.ajax.reload(null, false); },
                onError:   function (res) { mostrarError(res.message); }
            });
        });
    });

    function limpiarModal() {
        $('#form_sucursal')[0].reset();
        $('#sucursal_id').val('');
    }

});
