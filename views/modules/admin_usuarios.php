<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0" style="color:var(--color-texto-principal);">Gestión de Usuarios</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Administra los usuarios del sistema</div>
        </div>
        <?php if (puedo('admin/usuarios', 'crear')): ?>
        <div class="col-auto">
            <button id="btn_nuevo_usuario" class="btn btn-primary"
                style="background-color:var(--color-primario);border-color:var(--color-primario);">
                <i class="ti ti-plus me-1"></i>
                Agregar usuario
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabla_usuarios" class="table table-hover align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Teléfono</th>
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
var PERMISOS_USUARIOS = <?= json_encode([
    'crear'    => puedo('admin/usuarios', 'crear'),
    'editar'   => puedo('admin/usuarios', 'editar'),
    'eliminar' => puedo('admin/usuarios', 'eliminar'),
]) ?>;
</script>

<!-- Modal agregar/editar usuario -->
<div class="modal fade" id="modal_usuario" tabindex="-1" aria-labelledby="modal_usuario_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="form_usuario" autocomplete="off" enctype="multipart/form-data">
                <input type="hidden" id="usuario_id" name="id" value="">

                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modal_usuario_titulo">Nuevo usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="usuario_nombre">Nombre</label>
                            <input type="text" id="usuario_nombre" name="nombre" class="form-control"
                                placeholder="Ej: Juan" required maxlength="100">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="usuario_apellidos">Apellidos</label>
                            <input type="text" id="usuario_apellidos" name="apellidos" class="form-control"
                                placeholder="Ej: Pérez García" required maxlength="150">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="usuario_email">Correo electrónico</label>
                            <input type="email" id="usuario_email" name="email" class="form-control"
                                placeholder="correo@ejemplo.com" required maxlength="150">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="usuario_telefono">Teléfono</label>
                            <input type="text" id="usuario_telefono" name="telefono" class="form-control"
                                placeholder="Ej: 5512345678" maxlength="20">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="usuario_id_rol">Rol</label>
                            <select id="usuario_id_rol" name="id_rol" class="form-select" required>
                                <option value="">Selecciona un rol...</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="usuario_id_sucursal">Sucursal</label>
                            <select id="usuario_id_sucursal" name="id_sucursal" class="form-select" required></select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="usuario_estado">Estado</label>
                            <select id="usuario_estado" name="estado" class="form-select">
                                <option value="0">Activo</option>
                                <option value="1">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="usuario_imagen">Foto de perfil</label>
                            <input type="file" id="usuario_imagen" name="imagen" class="form-control"
                                accept="image/jpeg,image/png,image/webp">
                            <div id="usuario_imagen_actual" class="mt-2 d-none">
                                <img id="usuario_imagen_preview" src="" alt="Foto actual"
                                    style="width:56px;height:56px;object-fit:cover;border-radius:50%;border:2px solid var(--color-primario);">
                                <span class="ms-2 text-muted" style="font-size:0.8rem;">Foto actual (dejar vacío para conservar)</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="usuario_password">
                                Contraseña
                                <span id="password_hint" class="text-muted fw-normal" style="font-size:0.78rem;">
                                    (dejar en blanco para no cambiar)
                                </span>
                            </label>
                            <input type="password" id="usuario_password" name="password" class="form-control"
                                placeholder="••••••••" minlength="6">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="usuario_password_confirmar">Confirmar contraseña</label>
                            <input type="password" id="usuario_password_confirmar" name="password_confirmar" class="form-control"
                                placeholder="••••••••" minlength="6">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_usuario" class="btn btn-primary"
                        style="background-color:var(--color-primario);border-color:var(--color-primario);">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
