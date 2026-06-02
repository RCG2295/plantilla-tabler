$(document).ready(function () {

    var app_url  = $('meta[name="app-url"]').attr('content');
    var ajax_url = app_url + '/views/ajax/ajax_compras_proveedores.php';
    var permisos = window.PERMISOS_PROVEEDORES || { crear: false, editar: false, eliminar: false };
    var modal    = new bootstrap.Modal(document.getElementById('modal_proveedor'));
    var tabla;
    var tsEstado = new TomSelect('#proveedor_estado', { create: false });

    // ── DataTable ───────────────────────────────────────────────────────────
    tabla = $('#tabla_proveedores').DataTable({
        ajax: { url: ajax_url + '?action=list', type: 'GET', dataSrc: 'data' },
        columns: [
            { data: 'nombre', render: function (d) { return '<span class="fw-semibold">' + d + '</span>'; } },
            { data: 'razon_social', render: function (d) { return d || '<span class="text-muted">—</span>'; } },
            { data: 'rfc', render: function (d) { return d ? '<code>' + d + '</code>' : '<span class="text-muted">—</span>'; } },
            { data: 'telefono', render: function (d) { return d || '<span class="text-muted">—</span>'; } },
            { data: 'email', render: function (d) { return d ? '<a href="mailto:' + d + '">' + d + '</a>' : '<span class="text-muted">—</span>'; } },
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
                    if (permisos.editar)   html += '<button class="btn btn-sm btn-outline-primary me-1 btn-editar" data-id="' + id + '" title="Editar"><i class="ti ti-pencil"></i></button>';
                    if (permisos.eliminar) html += '<button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="' + id + '" title="Eliminar"><i class="ti ti-trash"></i></button>';
                    return html || '<span class="text-muted">—</span>';
                }
            }
        ],
        language: { url: window.DT_LANG_URL },
        pageLength: 15,
        order: [[0, 'asc']],
        responsive: true
    });

    // ── Nuevo ───────────────────────────────────────────────────────────────
    $('#btn_nuevo_proveedor').on('click', function () {
        limpiarFormulario();
        $('#modal_proveedor_titulo').text('Nuevo proveedor');
        modal.show();
    });

    // ── Editar ──────────────────────────────────────────────────────────────
    $('#tabla_proveedores').on('click', '.btn-editar', function () {
        var id = $(this).data('id');
        $.ajax({
            url: ajax_url, type: 'GET',
            data: { action: 'get', id: id }, dataType: 'json',
            success: function (res) {
                if (res.status !== 'ok') { mostrarError(res.message); return; }
                var p = res.data;
                limpiarFormulario();
                $('#modal_proveedor_titulo').text('Editar proveedor');
                $('#proveedor_id').val(p.id);
                $('#proveedor_nombre').val(p.nombre);
                $('#proveedor_razon_social').val(p.razon_social || '');
                $('#proveedor_rfc').val(p.rfc || '');
                $('#proveedor_telefono').val(p.telefono || '');
                $('#proveedor_email').val(p.email || '');
                $('#proveedor_direccion').val(p.direccion || '');
                $('#proveedor_notas').val(p.notas || '');
                tsEstado.setValue(String(p.estado));
                modal.show();
            }
        });
    });

    // ── Eliminar ────────────────────────────────────────────────────────────
    $('#tabla_proveedores').on('click', '.btn-eliminar', function () {
        var id = $(this).data('id');
        confirmarEliminacion('El proveedor será marcado como eliminado.').then(function (r) {
            if (!r.isConfirmed) return;
            ajaxPost({
                url: ajax_url, data: { action: 'delete', id: id },
                onSuccess: function (res) { mostrarExito(res.message); tabla.ajax.reload(null, false); },
                onError:   function (res) { mostrarError(res.message); }
            });
        });
    });

    // ── Submit ──────────────────────────────────────────────────────────────
    $('#form_proveedor').on('submit', function (e) {
        e.preventDefault();
        if (!validarCamposRequeridos('#form_proveedor')) { mostrarError('Completa los campos requeridos.'); return; }

        var btn = $('#btn_guardar_proveedor').prop('disabled', true).text('Guardando...');

        ajaxPost({
            url: ajax_url,
            data: {
                action:       'save',
                id:           $('#proveedor_id').val(),
                nombre:       $('#proveedor_nombre').val().trim(),
                razon_social: $('#proveedor_razon_social').val().trim(),
                rfc:          $('#proveedor_rfc').val().trim(),
                telefono:     $('#proveedor_telefono').val().trim(),
                email:        $('#proveedor_email').val().trim(),
                direccion:    $('#proveedor_direccion').val().trim(),
                notas:        $('#proveedor_notas').val().trim(),
                estado:       tsEstado.getValue()
            },
            onSuccess: function (res) {
                modal.hide();
                mostrarExito(res.message);
                tabla.ajax.reload(null, false);
            },
            onError:    function (res) { mostrarError(res.message); },
            onComplete: function ()    { btn.prop('disabled', false).text('Guardar'); }
        });
    });

    function limpiarFormulario() {
        $('#form_proveedor')[0].reset();
        $('#proveedor_id').val('');
        tsEstado.setValue('0');
    }

});
