<?php
$app_url  = TemplateController::getUrlController();
$menu     = CfgModulosModel::getSidebarMenu();
$chevron  = '<i class="ti ti-chevron-down dropdown-arrow ms-auto"></i>';
?>
<div class="sidebar-overlay"></div>

<aside class="navbar navbar-vertical navbar-expand-lg">
    <div class="container-fluid flex-column align-items-stretch p-0">

        <!-- Brand -->
        <div class="navbar-brand d-flex align-items-center px-3 py-3" style="min-height:64px;">
            <img src="<?= $app_url ?>/views/assets/img/logo.png" alt="Sistema"
                class="navbar-brand-logo"
                style="height:36px;width:auto;object-fit:contain;filter:brightness(0) invert(1);flex-shrink:0;">
        </div>

        <div class="px-2 pb-2" style="overflow-y:auto;flex:1;">
            <ul class="navbar-nav">

<?php foreach ($menu as $area):
    // Filter modules the current user can see
    $visibles = array_values(array_filter(
        $area['modulos'],
        fn($m) => puedo($m['clave'], 'ver')
    ));

    if (empty($visibles)) continue;

    $area_active = !empty(array_filter(
        $visibles,
        fn($m) => $ruta === $m['clave'] || str_starts_with($ruta, $m['clave'] . '/')
    ));

    if (count($visibles) === 1):
        // Simple nav item — use area icon + module name
        $m        = $visibles[0];
        $is_active = $ruta === $m['clave'] || str_starts_with($ruta, $m['clave'] . '/');
?>
                <li class="nav-item">
                    <a href="<?= $app_url ?>/<?= htmlspecialchars($m['clave']) ?>"
                       class="nav-link <?= $is_active ? 'active' : '' ?>">
                        <span class="nav-link-icon"><i class="<?= htmlspecialchars($area['icono']) ?>"></i></span>
                        <span class="nav-link-title"><?= htmlspecialchars($m['nombre']) ?></span>
                    </a>
                </li>
<?php
    else:
        // Dropdown — use area icon + area name
        $submenu_id = 'submenu-area-' . $area['id'];
?>
                <li class="nav-item">
                    <a href="#<?= $submenu_id ?>"
                       class="nav-link <?= $area_active ? 'active' : '' ?>"
                       data-bs-toggle="collapse" role="button"
                       aria-expanded="<?= $area_active ? 'true' : 'false' ?>">
                        <span class="nav-link-icon"><i class="<?= htmlspecialchars($area['icono']) ?>"></i></span>
                        <span class="nav-link-title"><?= htmlspecialchars($area['nombre']) ?></span>
                        <?= $chevron ?>
                    </a>
                    <div class="collapse nav-submenu <?= $area_active ? 'show' : '' ?>" id="<?= $submenu_id ?>">
                        <ul class="nav flex-column">
<?php foreach ($visibles as $m):
    $is_active = $ruta === $m['clave'] || str_starts_with($ruta, $m['clave'] . '/');
?>
                            <li class="nav-item">
                                <a href="<?= $app_url ?>/<?= htmlspecialchars($m['clave']) ?>"
                                   class="nav-link <?= $is_active ? 'active' : '' ?>">
                                    <?= htmlspecialchars($m['nombre']) ?>
                                </a>
                            </li>
<?php endforeach; ?>
                        </ul>
                    </div>
                </li>
<?php
    endif;
endforeach;
?>

            </ul>
        </div>

    </div>
</aside>
