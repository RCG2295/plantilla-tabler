<?php
$app_url = TemplateController::getUrlController();

$has_ventas  = puedo('ventas/historial_ventas', 'ver');
$has_turno   = puedo('ventas/mi_caja', 'ver');
$has_compras = puedo('compras/compras', 'ver');
$has_egresos = puedo('egresos/egresos', 'ver');
$has_inv     = puedo('inventario/productos', 'ver');

$col_inv = ($has_ventas && $has_inv) ? 6 : 12;
$col_vta = ($has_ventas && $has_inv) ? 6 : 12;

// Accesos rápidos — módulos visibles para este usuario
$_dash_model   = new DashboardModel();
$_todos_modulos = $_dash_model->getAccesosRapidos();
$_areas_acceso  = [];
foreach ($_todos_modulos as $_m) {
    if (puedo($_m['clave'], 'ver')) {
        $_areas_acceso[$_m['area_nombre']][] = $_m;
    }
}

$_nombres_dia = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
$_nombres_mes = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$_fecha_txt   = $_nombres_dia[(int)date('w')] . ', ' . date('j') . ' de ' . $_nombres_mes[(int)date('n') - 1] . ' de ' . date('Y');
$_inicial     = strtoupper(mb_substr(trim($_SESSION['usuario_nombre'] ?? 'U'), 0, 1));
$_nombre_completo = htmlspecialchars(trim(($_SESSION['usuario_nombre'] ?? '') . ' ' . ($_SESSION['usuario_apellidos'] ?? '')));
$_rol         = htmlspecialchars($_SESSION['usuario_rol_nombre'] ?? '');
$_sucursal    = htmlspecialchars($_SESSION['sucursal_nombre'] ?? 'Todas las sucursales');
?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0">Dashboard</h2>
            <div class="text-muted mt-1" id="dash-periodo" style="font-size:0.85rem;">Cargando...</div>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary btn-sm" id="btn_refresh_dash">
                <i class="ti ti-refresh me-1"></i>Actualizar
            </button>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">

    <?php if ($has_ventas): ?>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="text-uppercase text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.05em;">Ventas de la semana</div>
                    <div class="rounded p-2" style="background:rgba(27,142,163,0.12);">
                        <i class="ti ti-shopping-cart" style="font-size:1.1rem;color:var(--color-primario);"></i>
                    </div>
                </div>
                <div class="fw-bold mb-1" id="kpi_num_ventas" style="font-size:1.9rem;line-height:1;">—</div>
                <div class="text-muted mb-2" style="font-size:0.82rem;">Ventas registradas</div>
                <div class="fw-semibold mb-2" id="kpi_total_ventas" style="font-size:1.1rem;color:var(--color-primario);">$—</div>
                <div class="row g-1" style="font-size:0.76rem;">
                    <div class="col-6 text-muted">MXN: <span id="kpi_efec_mxn" class="fw-semibold text-body">—</span></div>
                    <div class="col-6 text-muted">USD: <span id="kpi_efec_usd" class="fw-semibold text-body">—</span></div>
                    <div class="col-6 text-muted">Tarjeta: <span id="kpi_tarjeta" class="fw-semibold text-body">—</span></div>
                    <div class="col-6 text-muted">Transfer: <span id="kpi_transfer" class="fw-semibold text-body">—</span></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($has_turno): ?>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="text-uppercase text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.05em;">Turno activo</div>
                    <div class="rounded p-2" style="background:rgba(34,197,94,0.12);">
                        <i class="ti ti-cash-register" style="font-size:1.1rem;color:#22c55e;"></i>
                    </div>
                </div>
                <div id="kpi_turno_body">
                    <div class="text-muted" style="font-size:0.85rem;">Cargando...</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($has_compras): ?>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="text-uppercase text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.05em;">Compras de la semana</div>
                    <div class="rounded p-2" style="background:rgba(245,158,11,0.12);">
                        <i class="ti ti-package" style="font-size:1.1rem;color:#f59e0b;"></i>
                    </div>
                </div>
                <div class="fw-bold mb-1" id="kpi_num_compras" style="font-size:1.9rem;line-height:1;">—</div>
                <div class="text-muted mb-2" style="font-size:0.82rem;">Compras registradas</div>
                <div class="fw-semibold" id="kpi_total_compras" style="font-size:1.1rem;color:#f59e0b;">$—</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($has_egresos): ?>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="text-uppercase text-muted fw-semibold" style="font-size:0.75rem;letter-spacing:.05em;">Egresos de la semana</div>
                    <div class="rounded p-2" style="background:rgba(239,68,68,0.12);">
                        <i class="ti ti-trending-down" style="font-size:1.1rem;color:#ef4444;"></i>
                    </div>
                </div>
                <div class="fw-bold mb-1" id="kpi_num_egresos" style="font-size:1.9rem;line-height:1;">—</div>
                <div class="text-muted mb-2" style="font-size:0.82rem;">Egresos registrados</div>
                <div class="fw-semibold" id="kpi_total_egresos" style="font-size:1.1rem;color:#ef4444;">$—</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Bienvenida + Accesos rápidos -->
<div class="row g-3 mb-4">

    <!-- Card bienvenida -->
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="avatar avatar-lg flex-shrink-0 fw-bold"
                         style="background:var(--color-primario);color:#fff;font-size:1.2rem;">
                        <?= $_inicial ?>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:1rem;"><?= $_nombre_completo ?></div>
                        <div class="text-muted" style="font-size:0.82rem;"><?= $_rol ?></div>
                    </div>
                </div>
                <hr class="my-2">
                <div class="d-flex flex-column gap-1" style="font-size:0.85rem;">
                    <div>
                        <span class="text-muted">Sucursal:</span>
                        <span class="fw-semibold ms-1"><?= $_sucursal ?></span>
                    </div>
                    <div>
                        <span class="text-muted">Hoy:</span>
                        <span class="fw-semibold ms-1"><?= $_fecha_txt ?></span>
                    </div>
                </div>
                <?php if (puedo('cfg/perfil', 'ver')): ?>
                <div class="mt-3">
                    <a href="<?= $app_url ?>/cfg/perfil" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="ti ti-user-circle me-1"></i>Mi perfil
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Accesos rápidos -->
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title fw-semibold" style="font-size:0.9rem;">
                    <i class="ti ti-layout-grid me-1"></i>Accesos rápidos
                </h3>
            </div>
            <div class="card-body" style="overflow-y:auto;">
                <?php if (empty($_areas_acceso)): ?>
                    <div class="text-center text-muted py-3" style="font-size:0.85rem;">
                        Sin módulos disponibles.
                    </div>
                <?php else: ?>
                    <?php foreach ($_areas_acceso as $_area_nombre => $_modulos_area): ?>
                    <div class="mb-3">
                        <div class="text-uppercase text-muted fw-semibold mb-2"
                             style="font-size:0.7rem;letter-spacing:.06em;">
                            <?= htmlspecialchars($_area_nombre) ?>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($_modulos_area as $_mod): ?>
                            <a href="<?= $app_url . '/' . htmlspecialchars($_mod['clave']) ?>"
                               class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded text-decoration-none acceso-rapido-btn">
                                <i class="<?= htmlspecialchars($_mod['icono']) ?>" style="font-size:1rem;"></i>
                                <span style="font-size:0.82rem;"><?= htmlspecialchars($_mod['nombre']) ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- Charts Row -->
<?php if ($has_ventas): ?>
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title fw-semibold" style="font-size:0.9rem;">Ventas por dia</h3>
            </div>
            <div class="card-body">
                <div id="chart_ventas_dia"></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title fw-semibold" style="font-size:0.9rem;">Formas de pago</h3>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div id="chart_formas_pago" style="width:100%;"></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tables Row -->
<?php if ($has_inv || $has_ventas): ?>
<div class="row g-3">

    <?php if ($has_inv): ?>
    <div class="col-12 col-lg-<?= $col_inv ?>">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title fw-semibold" style="font-size:0.9rem;">
                    <i class="ti ti-alert-triangle me-1 text-danger"></i>Productos con stock bajo
                </h3>
                <div class="card-options">
                    <span class="badge bg-danger text-white" id="badge_bajo_stock">—</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th class="ps-3">Producto</th>
                                <th class="text-center">Stock actual</th>
                                <th class="text-center">Minimo</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_stock_bajo">
                            <tr><td colspan="3" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($has_ventas): ?>
    <div class="col-12 col-lg-<?= $col_vta ?>">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title fw-semibold" style="font-size:0.9rem;">
                    <i class="ti ti-clock me-1"></i>Ultimas ventas
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th class="ps-3">Folio</th>
                                <th>Cajero</th>
                                <th class="text-end">Total</th>
                                <th>Hora</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_ultimas_ventas">
                            <tr><td colspan="4" class="text-center text-muted py-3">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
<?php endif; ?>

<style>
.acceso-rapido-btn {
    background: rgba(27,142,163,0.07);
    color: var(--color-primario);
    border: 1px solid rgba(27,142,163,0.18);
    transition: background .15s, border-color .15s;
}
.acceso-rapido-btn:hover {
    background: rgba(27,142,163,0.15);
    border-color: rgba(27,142,163,0.35);
    color: var(--color-primario);
}
</style>

<script>
var DASH_PERMISOS = <?= json_encode([
    'ventas'     => $has_ventas,
    'turno'      => $has_turno,
    'compras'    => $has_compras,
    'egresos'    => $has_egresos,
    'inventario' => $has_inv,
]) ?>;
var DASH_AJAX_URL = '<?= $app_url ?>/views/ajax/ajax_dashboard.php';
</script>
