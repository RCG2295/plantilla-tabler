<?php $app_url = TemplateController::getUrlController(); ?>
<header class="navbar navbar-top d-flex align-items-center px-3 gap-3">

    <!-- Toggle sidebar -->
    <button id="btn_toggle_sidebar" title="Colapsar menú">
        <i class="ti ti-menu-2" style="font-size:1.25rem;"></i>
    </button>

    <!-- Nombre del sistema -->
    <span class="fw-semibold text-muted d-none d-md-inline" style="font-size:0.9rem;">
        <?= htmlspecialchars(trim(($_SESSION['usuario_nombre'] ?? '') . ' ' . ($_SESSION['usuario_apellidos'] ?? '')) ?: 'Sistema') ?>
    </span>

    <div class="flex-fill"></div>

    <!-- Sucursal activa -->
    <?php if (!empty($_SESSION['es_superadmin'])): ?>
    <div class="nav-item dropdown me-1" id="dropdown_sucursal_wrap">
        <a href="#" class="d-flex align-items-center gap-1 text-decoration-none px-2 py-1 rounded"
            data-bs-toggle="dropdown" aria-expanded="false"
            style="background:var(--color-primario);color:#fff;font-size:0.78rem;"
            id="btn_sucursal_dropdown">
            <i class="ti ti-building" style="font-size:1rem;"></i>
            <span id="navbar_sucursal_nombre"><?= htmlspecialchars($_SESSION['sucursal_nombre'] ?? 'Sin sucursal') ?></span>
            <i class="ti ti-chevron-down" style="font-size:0.7rem;"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end" id="dropdown_sucursales_menu" style="min-width:220px;">
            <div class="dropdown-header" style="font-size:0.75rem;">Cambiar sucursal</div>
            <div id="lista_sucursales_navbar">
                <div class="text-center py-2"><span class="spinner-border spinner-border-sm text-muted"></span></div>
            </div>
        </div>
    </div>
    <?php elseif (!empty($_SESSION['sucursal_nombre'])): ?>
    <span class="badge me-1 d-none d-md-inline-flex align-items-center gap-1"
        style="background:var(--color-primario);color:#fff;font-size:0.75rem;padding:5px 10px;border-radius:20px;">
        <i class="ti ti-building"></i>
        <?= htmlspecialchars($_SESSION['sucursal_nombre']) ?>
    </span>
    <?php endif; ?>

    <!-- Notificaciones -->
    <div class="nav-item dropdown me-1">
        <a href="#" class="nav-link d-flex align-items-center position-relative p-2"
            data-bs-toggle="dropdown" aria-expanded="false" id="btn_notificaciones">
            <i class="ti ti-bell" style="font-size:1.25rem;"></i>
            <span class="badge bg-danger badge-notification d-none"
                style="position:absolute;top:4px;right:4px;font-size:0.6rem;padding:2px 4px;color:#fff;"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-end" style="min-width:280px;">
            <div class="dropdown-header">Notificaciones</div>
            <div id="notificaciones_lista">
                <div class="dropdown-item text-muted text-center py-3" style="font-size:0.82rem;">Cargando…</div>
            </div>
            <div class="dropdown-divider"></div>
            <?php if (puedo('reportes/notificaciones', 'ver')): ?>
            <a href="<?= $app_url ?>/reportes/notificaciones" class="dropdown-item text-center" style="font-size:0.8rem;">Ver todas</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Avatar usuario -->
    <div class="nav-item dropdown">
        <a href="#" class="d-flex align-items-center gap-2 text-decoration-none"
            data-bs-toggle="dropdown" aria-expanded="false">
            <?php if (!empty($_SESSION['usuario_imagen'])): ?>
            <img src="<?= $app_url ?>/views/uploads/admin_usuarios/<?= htmlspecialchars($_SESSION['usuario_imagen']) ?>"
                alt="Foto de perfil" class="rounded-circle"
                style="width:32px;height:32px;object-fit:cover;flex-shrink:0;">
            <?php else: ?>
            <div class="avatar avatar-sm rounded-circle"
                style="background:var(--color-primario);color:#fff;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;flex-shrink:0;">
                <?= strtoupper(substr($_SESSION['usuario_nombre'] ?? 'U', 0, 1)) ?>
            </div>
            <?php endif; ?>
            <div class="d-none d-md-block">
                <div class="fw-semibold" style="font-size:0.85rem;line-height:1.2;">
                    <?= htmlspecialchars(trim(($_SESSION['usuario_nombre'] ?? '') . ' ' . ($_SESSION['usuario_apellidos'] ?? ''))) ?>
                </div>
                <div class="text-muted" style="font-size:0.72rem;">
                    <?= htmlspecialchars($_SESSION['usuario_rol_nombre'] ?? '') ?>
                </div>
            </div>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="<?= $app_url ?>/cfg/perfil">
                <i class="ti ti-user-circle me-2"></i>
                Mi perfil
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-danger" href="<?= $app_url ?>/salir">
                <i class="ti ti-logout me-2"></i>
                Cerrar sesión
            </a>
        </div>
    </div>

</header>
