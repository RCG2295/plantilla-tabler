<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Config
require_once "config/connection.php";
require_once "config/controller_template.php";
require_once "config/permisos.php";

// Controllers
require_once "controllers/controller_general.php";
require_once "controllers/controller_login.php";
require_once "controllers/controller_admin_usuarios.php";
require_once "controllers/controller_reportes_notificaciones.php";
require_once "controllers/controller_cfg_areas.php";
require_once "controllers/controller_cfg_modulos.php";
require_once "controllers/controller_cfg_roles.php";
require_once "controllers/controller_inventario_categorias.php";
require_once "controllers/controller_inventario_unidades.php";
require_once "controllers/controller_inventario_motivos.php";
require_once "controllers/controller_inventario_productos.php";
require_once "controllers/controller_inventario_movimientos.php";
require_once "controllers/controller_compras_proveedores.php";
require_once "controllers/controller_compras_compras.php";
require_once "controllers/controller_egresos_categorias.php";
require_once "controllers/controller_egresos_egresos.php";
require_once "controllers/controller_cfg_perfil.php";
require_once "controllers/controller_admin_sucursales.php";
require_once "controllers/controller_docs.php";
require_once "controllers/controller_ventas_tipo_cambio.php";
require_once "controllers/controller_ventas_caja.php";
require_once "controllers/controller_ventas_mi_caja.php";
require_once "controllers/controller_ventas_pos.php";
require_once "controllers/controller_ventas_historial_turnos.php";
require_once "controllers/controller_ventas_historial_ventas.php";
require_once "controllers/controller_dashboard.php";

// Models
require_once "models/model_general.php";
require_once "models/model_login.php";
require_once "models/model_admin_usuarios.php";
require_once "models/model_reportes_notificaciones.php";
require_once "models/model_cfg_areas.php";
require_once "models/model_cfg_modulos.php";
require_once "models/model_cfg_roles.php";
require_once "models/model_inventario_categorias.php";
require_once "models/model_inventario_unidades.php";
require_once "models/model_inventario_motivos.php";
require_once "models/model_inventario_productos.php";
require_once "models/model_inventario_movimientos.php";
require_once "models/model_compras_proveedores.php";
require_once "models/model_compras_compras.php";
require_once "models/model_egresos_categorias.php";
require_once "models/model_egresos_egresos.php";
require_once "models/model_cfg_perfil.php";
require_once "models/model_admin_sucursales.php";
require_once "models/model_docs.php";
require_once "models/model_ventas_tipo_cambio.php";
require_once "models/model_ventas_caja.php";
require_once "models/model_ventas_mi_caja.php";
require_once "models/model_ventas_pos.php";
require_once "models/model_ventas_historial_turnos.php";
require_once "models/model_ventas_historial_ventas.php";
require_once "models/model_dashboard.php";

$template = new TemplateController();
$template->template();
