<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0" style="color:var(--color-texto-principal);">Roles y permisos</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Define los roles del sistema y configura sus permisos por módulo</div>
        </div>
        <?php if (puedo('cfg/roles', 'crear')): ?>
        <div class="col-auto">
            <button id="btn_nuevo_rol" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-plus me-1"></i>
                Nuevo rol
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_roles" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Superadmin</th>
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
var PERMISOS_ROLES = <?= json_encode([
    'crear'    => puedo('cfg/roles', 'crear'),
    'editar'   => puedo('cfg/roles', 'editar'),
    'eliminar' => puedo('cfg/roles', 'eliminar'),
]) ?>;
</script>

<!-- Modal agregar/editar rol -->
<div class="modal fade" id="modal_rol" tabindex="-1" aria-labelledby="modal_rol_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form_rol" autocomplete="off">
                <input type="hidden" id="rol_id" name="id" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_rol_titulo">Nuevo rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="rol_nombre">Nombre</label>
                            <input type="text" id="rol_nombre" name="nombre" class="form-control"
                                placeholder="Ej: Supervisor" required maxlength="100">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="rol_descripcion">Descripción</label>
                            <input type="text" id="rol_descripcion" name="descripcion" class="form-control"
                                placeholder="Descripción breve del rol" maxlength="255">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="rol_estado">Estado</label>
                            <select id="rol_estado" name="estado" class="form-select">
                                <option value="0">Activo</option>
                                <option value="1">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="form-check form-switch ms-1 mb-2">
                                <input class="form-check-input" type="checkbox" id="rol_es_superadmin" name="es_superadmin" value="1">
                                <label class="form-check-label fw-semibold" for="rol_es_superadmin">
                                    Superadmin
                                    <small class="text-muted d-block fw-normal" style="font-size:0.75rem;">Acceso total sin restricciones</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_rol" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal permisos -->
<div class="modal fade" id="modal_permisos" tabindex="-1" aria-labelledby="modal_permisos_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modal_permisos_titulo">Permisos del rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="permisos_loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div id="permisos_contenido" class="d-none">
                    <table class="table table-bordered table-hover align-middle mb-0" id="tabla_permisos_matriz">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="min-width:200px;">Módulo</th>
                                <th class="text-center" style="width:80px;">Ver</th>
                                <th class="text-center" style="width:80px;">Crear</th>
                                <th class="text-center" style="width:80px;">Editar</th>
                                <th class="text-center" style="width:80px;">Eliminar</th>
                            </tr>
                        </thead>
                        <tbody id="permisos_tbody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btn_guardar_permisos" class="btn btn-primary"
                    style="background-color:var(--color-primario);border-color:var(--color-primario);"
                    data-rol-id="">
                    Guardar permisos
                </button>
            </div>
        </div>
    </div>
</div>
