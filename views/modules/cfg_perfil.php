<?php $app_url = TemplateController::getUrlController(); ?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Mi perfil</h2>
            <div class="text-muted mt-1" style="font-size:0.85rem;">Administra tu información personal y contraseña</div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Informacion personal -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title fw-semibold">Información personal</h3>
            </div>
            <div class="card-body">
                <form id="form_info" autocomplete="off" enctype="multipart/form-data">

                    <!-- Avatar -->
                    <div class="d-flex align-items-center gap-4 mb-4 pb-3" style="border-bottom:1px solid var(--tblr-border-color);">
                        <div class="position-relative" style="flex-shrink:0;">
                            <img id="avatar_img" src="" alt="Foto de perfil"
                                class="rounded-circle" style="width:90px;height:90px;object-fit:cover;display:none;border:3px solid var(--color-primario);">
                            <div id="avatar_placeholder"
                                class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                style="width:90px;height:90px;background-color:var(--color-primario);color:#fff;font-size:2rem;">
                                <span id="avatar_initials">?</span>
                            </div>
                        </div>
                        <div>
                            <label for="perfil_imagen" class="btn btn-sm btn-outline-secondary mb-1">
                                <i class="ti ti-camera me-1"></i>Cambiar foto
                            </label>
                            <input type="file" id="perfil_imagen" name="imagen" class="d-none"
                                accept="image/jpeg,image/png,image/webp">
                            <div class="text-muted" style="font-size:0.78rem;">JPG, PNG o WEBP — máx. 2 MB</div>
                        </div>
                    </div>

                    <!-- Campos -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="perfil_nombre">Nombre</label>
                            <input type="text" id="perfil_nombre" name="nombre" class="form-control"
                                placeholder="Tu nombre" required maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="perfil_apellidos">Apellidos</label>
                            <input type="text" id="perfil_apellidos" name="apellidos" class="form-control"
                                placeholder="Tus apellidos" required maxlength="150">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold" for="perfil_email">Correo electrónico</label>
                            <input type="email" id="perfil_email" name="email" class="form-control"
                                placeholder="correo@ejemplo.com" required maxlength="200">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" for="perfil_telefono">
                                Teléfono <small class="text-muted fw-normal">(opcional)</small>
                            </label>
                            <input type="text" id="perfil_telefono" name="telefono" class="form-control"
                                placeholder="10 dígitos" maxlength="20">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" id="btn_guardar_info" class="btn btn-primary"
                            style="background-color:var(--color-primario);border-color:var(--color-primario);">
                            <i class="ti ti-check me-1"></i>Guardar cambios
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Seguridad -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title fw-semibold">Seguridad</h3>
            </div>
            <div class="card-body">
                <form id="form_password" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pw_actual">Contraseña actual</label>
                        <div class="input-group">
                            <input type="password" id="pw_actual" name="password_actual"
                                class="form-control" placeholder="Contraseña actual" required>
                            <button type="button" class="btn btn-outline-secondary btn-toggle-pw" data-target="pw_actual">
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="pw_nueva">Nueva contraseña</label>
                        <div class="input-group">
                            <input type="password" id="pw_nueva" name="password_nueva"
                                class="form-control" placeholder="Mínimo 8 caracteres" required minlength="8">
                            <button type="button" class="btn btn-outline-secondary btn-toggle-pw" data-target="pw_nueva">
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="pw_confirmacion">Confirmar contraseña</label>
                        <div class="input-group">
                            <input type="password" id="pw_confirmacion" name="password_confirmacion"
                                class="form-control" placeholder="Repite la nueva contraseña" required>
                            <button type="button" class="btn btn-outline-secondary btn-toggle-pw" data-target="pw_confirmacion">
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" id="btn_cambiar_pw" class="btn btn-warning w-100">
                        <i class="ti ti-lock me-1"></i>Actualizar contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
var APP_URL_PERFIL = '<?= $app_url ?>';
</script>
