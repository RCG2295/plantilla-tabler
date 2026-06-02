<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0" style="color:var(--color-texto-principal);">Módulos del sistema</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Define las secciones del sistema y su área de pertenencia</div>
        </div>
        <?php if (puedo('cfg/modulos', 'crear')): ?>
        <div class="col-auto">
            <button id="btn_nuevo_modulo" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-plus me-1"></i>
                Nuevo módulo
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_modulos" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Área</th>
                        <th>Clave (ruta)</th>
                        <th>Nombre</th>
                        <th>Orden</th>
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
var PERMISOS_MODULOS = <?= json_encode([
    'crear'    => puedo('cfg/modulos', 'crear'),
    'editar'   => puedo('cfg/modulos', 'editar'),
    'eliminar' => puedo('cfg/modulos', 'eliminar'),
]) ?>;
</script>

<!-- Modal agregar/editar módulo -->
<div class="modal fade" id="modal_modulo" tabindex="-1" aria-labelledby="modal_modulo_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form_modulo" autocomplete="off">
                <input type="hidden" id="modulo_id" name="id" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_modulo_titulo">Nuevo módulo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="modulo_id_area">Área</label>
                            <select id="modulo_id_area" name="id_area" class="form-select" required>
                                <option value="">Selecciona un área...</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="modulo_clave">
                                Clave <small class="text-muted fw-normal">(debe coincidir con la ruta del sistema)</small>
                            </label>
                            <input type="text" id="modulo_clave" name="clave" class="form-control"
                                placeholder="Ej: admin/usuarios" required maxlength="100">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="modulo_nombre">Nombre</label>
                            <input type="text" id="modulo_nombre" name="nombre" class="form-control"
                                placeholder="Ej: Usuarios" required maxlength="150">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="modulo_icono">
                                Ícono <small class="text-muted fw-normal">(opcional, class CSS de Tabler Icons)</small>
                            </label>
                            <input type="text" id="modulo_icono" name="icono" class="form-control"
                                placeholder="Ej: ti ti-users" maxlength="100">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="modulo_orden">Orden</label>
                            <input type="number" id="modulo_orden" name="orden" class="form-control"
                                value="0" min="0" max="999">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" for="modulo_estado">Estado</label>
                            <select id="modulo_estado" name="estado" class="form-select">
                                <option value="0">Activo</option>
                                <option value="1">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_modulo" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
