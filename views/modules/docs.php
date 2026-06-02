<?php $app_url = TemplateController::getUrlController(); ?>

<style>
#doc_content h1 { font-size: 1.6rem; font-weight: 700; color: var(--color-texto-principal); border-bottom: 2px solid var(--color-primario); padding-bottom: .4rem; margin-bottom: 1.2rem; }
#doc_content h2 { font-size: 1.2rem; font-weight: 600; margin-top: 1.8rem; margin-bottom: .6rem; color: var(--color-texto-principal); }
#doc_content h3 { font-size: 1rem; font-weight: 600; margin-top: 1.2rem; margin-bottom: .4rem; }
#doc_content p  { margin-bottom: .8rem; line-height: 1.65; }
#doc_content table { width: 100%; border-collapse: collapse; margin-bottom: 1.2rem; font-size: .9rem; }
#doc_content table th { background: var(--color-primario); color: #fff; padding: .5rem .75rem; text-align: left; font-weight: 600; }
#doc_content table td { padding: .45rem .75rem; border-bottom: 1px solid #e8eaf0; }
#doc_content table tr:nth-child(even) td { background: #f8fafc; }
#doc_content ul, #doc_content ol { padding-left: 1.4rem; margin-bottom: .8rem; }
#doc_content li { margin-bottom: .25rem; line-height: 1.6; }
#doc_content code { background: #f0f4f8; padding: .15rem .4rem; border-radius: .25rem; font-size: .85rem; color: #c0392b; }
#doc_content strong { font-weight: 600; }
.doc-nav-header { font-size: .7rem; font-weight: 700; letter-spacing: .06em; color: #8a96a3; padding: .5rem 1rem .25rem; text-transform: uppercase; background: #f8fafc; border-top: 1px solid #e8eaf0; }
.doc-link { font-size: .875rem; border-radius: 0 !important; border-left: 3px solid transparent !important; }
.doc-link:hover { background: #f0f7fa !important; border-left-color: var(--color-primario) !important; }
.doc-link.active { background: #e8f4f8 !important; border-left-color: var(--color-primario) !important; color: var(--color-primario) !important; font-weight: 600; }
.doc-link.doc-link-inicio { font-weight: 600; }
</style>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title fw-bold mb-0" style="color:var(--color-texto-principal);">Manual del sistema</h2>
            <div class="text-muted mt-1" style="font-size:.85rem;">Documentación y guía de uso</div>
        </div>
        <div class="col-auto">
            <button id="btn_print_doc" class="btn btn-outline-secondary">
                <i class="ti ti-download me-1"></i> Descargar PDF
            </button>
        </div>
    </div>
</div>

<div class="row g-3 align-items-start">

    <!-- Navegación -->
    <div class="col-12 col-md-3" id="docs_nav_col">
        <div class="card" style="position:sticky;top:1rem;">
            <div class="list-group list-group-flush" id="docs_nav" style="border-radius:.5rem;overflow:hidden;">

                <a href="#" class="list-group-item list-group-item-action doc-link doc-link-inicio" data-doc="index">
                    <i class="ti ti-home me-1"></i> Inicio
                </a>
                <a href="#" class="list-group-item list-group-item-action doc-link" data-doc="dashboard">
                    <i class="ti ti-layout-dashboard me-1"></i> Dashboard
                </a>

                <div class="doc-nav-header">Administración</div>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="admin_usuarios">
                    <i class="ti ti-users me-1"></i> Usuarios
                </a>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="admin_sucursales">
                    <i class="ti ti-building-store me-1"></i> Sucursales
                </a>

                <div class="doc-nav-header">Inventario</div>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="inventario_productos">
                    <i class="ti ti-box me-1"></i> Productos
                </a>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="inventario_movimientos">
                    <i class="ti ti-transfer me-1"></i> Movimientos
                </a>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="inventario_categorias">
                    <i class="ti ti-category me-1"></i> Categorías
                </a>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="inventario_unidades">
                    <i class="ti ti-ruler me-1"></i> Unidades de medida
                </a>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="inventario_motivos">
                    <i class="ti ti-tag me-1"></i> Motivos de movimiento
                </a>

                <div class="doc-nav-header">Compras</div>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="compras_compras">
                    <i class="ti ti-shopping-cart me-1"></i> Compras
                </a>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="compras_proveedores">
                    <i class="ti ti-truck me-1"></i> Proveedores
                </a>

                <div class="doc-nav-header">Egresos</div>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="egresos_egresos">
                    <i class="ti ti-cash me-1"></i> Egresos
                </a>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="egresos_categorias">
                    <i class="ti ti-folder me-1"></i> Categorías de egresos
                </a>

                <div class="doc-nav-header">Ventas</div>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="ventas_tipo_cambio">
                    <i class="ti ti-currency-dollar me-1"></i> Tipo de cambio
                </a>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="ventas_mi_caja">
                    <i class="ti ti-wallet me-1"></i> Mi caja
                </a>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="ventas_historial_turnos">
                    <i class="ti ti-history me-1"></i> Historial de turnos
                </a>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="ventas_historial_ventas">
                    <i class="ti ti-report me-1"></i> Historial de ventas
                </a>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="ventas_pos">
                    <i class="ti ti-shopping-cart me-1"></i> Punto de venta
                </a>

                <div class="doc-nav-header">Configuración</div>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="cfg_roles">
                    <i class="ti ti-shield me-1"></i> Roles y permisos
                </a>
                <a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="cfg_perfil">
                    <i class="ti ti-user-circle me-1"></i> Mi perfil
                </a>

            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="col-12 col-md-9">
        <div class="card">
            <div class="card-body p-4" id="doc_content" style="min-height:400px;">
                <div class="text-center py-5">
                    <div class="spinner-border" style="color:var(--color-primario);"></div>
                </div>
            </div>
        </div>
    </div>

</div>
