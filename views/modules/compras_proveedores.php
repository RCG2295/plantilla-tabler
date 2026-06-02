<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Proveedores</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Gestión del catálogo de proveedores</div>
        </div>
        <?php if (puedo('compras/proveedores', 'crear')): ?>
        <div class="col-auto">
            <button id="btn_nuevo_proveedor" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-plus me-1"></i>Nuevo proveedor
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_proveedores" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Razón social</th>
                        <th>RFC</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
var PERMISOS_PROVEEDORES = <?= json_encode([
    'crear'    => puedo('compras/proveedores', 'crear'),
    'editar'   => puedo('compras/proveedores', 'editar'),
    'eliminar' => puedo('compras/proveedores', 'eliminar'),
]) ?>;
</script>

<!-- Modal agregar/editar proveedor -->
<div class="modal fade" id="modal_proveedor" tabindex="-1" aria-labelledby="modal_proveedor_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="form_proveedor" autocomplete="off">
                <input type="hidden" id="proveedor_id" name="id" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_proveedor_titulo">Nuevo proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="proveedor_nombre">Nombre comercial</label>
                            <input type="text" id="proveedor_nombre" name="nombre" class="form-control"
                                placeholder="Nombre del proveedor" required maxlength="200">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="proveedor_razon_social">
                                Razón social <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <input type="text" id="proveedor_razon_social" name="razon_social" class="form-control"
                                placeholder="Razón social" maxlength="250">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="proveedor_rfc">
                                RFC <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <input type="text" id="proveedor_rfc" name="rfc" class="form-control"
                                placeholder="RFC" maxlength="20">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="proveedor_telefono">
                                Teléfono <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <input type="text" id="proveedor_telefono" name="telefono" class="form-control"
                                placeholder="Teléfono" maxlength="25">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="proveedor_email">
                                Email <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <input type="email" id="proveedor_email" name="email" class="form-control"
                                placeholder="correo@ejemplo.com" maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="proveedor_estado">Estado</label>
                            <select id="proveedor_estado" name="estado" class="form-select">
                                <option value="0">Activo</option>
                                <option value="1">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="proveedor_direccion">
                                Dirección <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <input type="text" id="proveedor_direccion" name="direccion" class="form-control"
                                placeholder="Dirección" maxlength="500">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="proveedor_notas">
                                Notas <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <textarea id="proveedor_notas" name="notas" class="form-control" rows="2"
                                placeholder="Observaciones del proveedor"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_proveedor" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
