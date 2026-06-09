# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# Contexto del Proyecto — Plantilla Tabler

## Stack
- **Backend:** PHP puro con estructura MVC propia (sin framework)
- **Frontend:** HTML + CSS + JavaScript + jQuery
- **UI Library:** Tabler (sobre Bootstrap 5) — instalado vía npm
- **Librerías complementarias:** DataTables, ApexCharts, SweetAlert2, Flatpickr, Tom Select — vía npm
- **Base de datos:** MySQL con PDO
- **Migraciones:** Phinx (`phinx.php`)
- **Assets:** servidos directamente desde `node_modules/` — no se copian a ningún directorio

## Comandos de setup

```bash
# Instalar dependencias PHP
composer install

# Instalar dependencias front-end
npm install

# Configurar entorno
cp .env.example .env
# Editar .env con DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL

# Correr migraciones
vendor/bin/phinx migrate

# Correr seeds base (obligatorios, en este orden exacto)
vendor/bin/phinx seed:run -s CfgRolesSeed
vendor/bin/phinx seed:run -s CfgAreasSeed
vendor/bin/phinx seed:run -s CfgModulosSeed
vendor/bin/phinx seed:run -s CfgRolesPermisosSeed
vendor/bin/phinx seed:run -s UsuarioAdminSeed

# Seeds de módulos incluidos en la plantilla (correr según los módulos que uses)
vendor/bin/phinx seed:run -s AdminSucursalesSeed       # módulo admin/sucursales
vendor/bin/phinx seed:run -s CfgPerfilSeed             # módulo cfg/perfil (recomendado siempre)
vendor/bin/phinx seed:run -s DocsSeed                  # módulo docs/manual (recomendado siempre)
vendor/bin/phinx seed:run -s AdminSucursalesSeed       # módulo admin/sucursales
vendor/bin/phinx seed:run -s ComprasSeed               # área y módulos de compras en el sidebar
vendor/bin/phinx seed:run -s EgresosSeed               # área y módulos de egresos en el sidebar
vendor/bin/phinx seed:run -s InventarioSidebarSeed     # área y módulos de inventario en el sidebar
vendor/bin/phinx seed:run -s InventarioUnidadesMedidaSeed   # datos maestros de unidades
vendor/bin/phinx seed:run -s InventarioMotivosMovimientoSeed # datos maestros de motivos
vendor/bin/phinx seed:run -s InventarioAltaProductoSeed     # motivo extra "Alta de producto"
vendor/bin/phinx seed:run -s VentasSeed                # área y módulos de ventas en el sidebar
# vendor/bin/phinx seed:run -s NotificacionesSeed      # solo datos de demo — NO correr en producción

# Revertir última migración
vendor/bin/phinx rollback

# Crear nueva migración
vendor/bin/phinx create NombreMigracion
```

## Estructura de carpetas

```
PLANTILLA-TABLER/
├── config/
│   ├── connection.php                ← conexión a MySQL via PDO (lee .env)
│   ├── controller_template.php       ← TemplateController: carga views/template.php
│   └── permisos.php                  ← helper global puedo($modulo, $accion)
├── controllers/
│   └── controller_[modulo].php       ← un controller por módulo
├── models/
│   └── model_[modulo].php            ← un model por módulo
├── views/
│   ├── ajax/
│   │   └── ajax_[modulo].php         ← endpoints jQuery → controller
│   ├── assets/
│   │   ├── css/
│   │   │   └── theme.css             ← variables de color y overrides de Tabler
│   │   ├── img/
│   │   └── js/
│   │       └── app.js                ← JS global del proyecto
│   ├── modules/
│   │   ├── sections/
│   │   │   ├── sidebar.php           ← sidebar dinámico generado desde BD
│   │   │   └── navbar.php            ← navbar parcial
│   │   ├── 404.php
│   │   ├── dashboard.php
│   │   ├── login.php
│   │   ├── cfg_areas.php             ← CRUD áreas del sidebar
│   │   ├── cfg_modulos.php           ← CRUD módulos del sistema
│   │   ├── cfg_roles.php             ← CRUD roles + matriz de permisos
│   │   └── admin_usuarios.php
│   ├── tickets/
│   │   └── [modulo]_ticket.php       ← tickets imprimibles por módulo
│   ├── uploads/
│   │   └── [modulo]/                 ← archivos subidos por módulo
│   │       └── .gitkeep
│   └── template.php                  ← layout principal
├── db/
│   ├── migrations/                   ← archivos de migración Phinx
│   └── seeds/                        ← seeders Phinx
├── _docs/                            ← manuales en Markdown por módulo
├── index.php                         ← entry point
├── phinx.php                         ← config de migraciones (BD: plantilla-tabler en dev)
├── .env.example
└── composer.json
```

## Cómo funciona el routing

`index.php` recibe todas las peticiones (via `.htaccess` → mod_rewrite). Carga todos los controllers, models y el helper de permisos. `TemplateController::template()` incluye `views/template.php`.

**El routing es completamente dinámico — no hay lista blanca hardcodeada.** La ruta de la URL se convierte en nombre de archivo reemplazando `/` por `_`:
- `admin/usuarios` → `views/modules/admin_usuarios.php`
- `cfg/roles` → `views/modules/cfg_roles.php`

El acceso se controla con `puedo($ruta, 'ver')`. Si el archivo no existe o el usuario no tiene permiso, se muestra `404.php`.

**Al agregar un módulo nuevo, siempre agregar sus `require_once` en `index.php` y registrarlo en la BD (ver checklist abajo).**

`TemplateController::getUrlController()` devuelve `$_ENV['APP_URL']` — debe estar configurado en `.env`.

## Sistema de roles y permisos

### Tablas
- `cfg_roles` — roles del sistema (`es_superadmin=1` omite todos los checks)
- `cfg_areas` — secciones visuales del sidebar (nombre, icono CSS, orden)
- `cfg_modulos` — módulos del sistema; `clave` debe coincidir exactamente con la ruta URL
- `cfg_roles_permisos` — 4 acciones por rol×módulo: `ver`, `crear`, `editar`, `eliminar`

### Sesión al hacer login
```php
$_SESSION['usuario_id']
$_SESSION['usuario_nombre']
$_SESSION['usuario_apellidos']
$_SESSION['usuario_email']
$_SESSION['usuario_rol_id']
$_SESSION['usuario_rol_nombre']
$_SESSION['es_superadmin']   // bool
$_SESSION['id_sucursal']     // int|null — null solo en superadmin sin sucursal asignada
$_SESSION['permisos']        // ['admin/usuarios' => ['ver'=>1,'crear'=>1,'editar'=>0,'eliminar'=>0], ...]
```

### Helper `puedo()`
Definido en `config/permisos.php`, disponible en cualquier archivo que pase por `index.php`.

```php
puedo('admin/usuarios', 'ver')      // acceso al módulo
puedo('admin/usuarios', 'crear')    // botón Agregar
puedo('admin/usuarios', 'editar')   // botón Editar
puedo('admin/usuarios', 'eliminar') // botón Eliminar
```

Reglas:
- Superadmin siempre retorna `true`
- `crear`, `editar` o `eliminar = 1` implica `ver = true` en la verificación

### Sidebar dinámico
`sidebar.php` consulta `CfgModulosModel::getSidebarMenu()` y filtra por `puedo($clave, 'ver')`.
- Área con 1 módulo visible → ítem simple con ícono del área
- Área con 2+ módulos visibles → dropdown colapsable
- Área sin módulos visibles → se oculta completamente

### Íconos
Los íconos de áreas y módulos se almacenan como clase CSS completa de Tabler Icons:
`ti ti-layout-dashboard`, `ti ti-settings`, etc.
Requiere el CSS: `node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css`

### Proteger endpoints AJAX
Cada `ajax_[modulo].php` debe incluir `config/permisos.php` y verificar por acción:
```php
require_once __DIR__ . '/../../config/permisos.php';

case 'list':
    if (!puedo('admin/usuarios', 'ver')) { ... unauthorized ... }
case 'add':
    if (!puedo('admin/usuarios', 'crear')) { ... unauthorized ... }
```

### Exponer permisos al JS de la vista
En el HTML de la vista (antes del modal):
```php
<script>
var PERMISOS_USUARIOS = <?= json_encode([
    'crear'    => puedo('admin/usuarios', 'crear'),
    'editar'   => puedo('admin/usuarios', 'editar'),
    'eliminar' => puedo('admin/usuarios', 'eliminar'),
]) ?>;
</script>
```
En el JS, usar `PERMISOS_USUARIOS.editar` para condicionar botones en el render de DataTables.

## Filtrado por sucursal en sesión

Todos los módulos que manejan datos operativos (ventas, inventario, compras, egresos) deben filtrar por la sucursal del usuario en sesión. El superadmin (`id_sucursal = NULL`) ve todas las sucursales.

**Patrón obligatorio en queries SQL:**
```php
$id_sucursal = $_SESSION['id_sucursal'] ?? null;

// En el WHERE:
AND (t.id_sucursal = ? OR ? IS NULL)

// En el execute, el parámetro va dos veces:
$stmt->execute([..., $id_sucursal, $id_sucursal]);
```

**Regla de usuarios:** todos los usuarios deben tener una sucursal asignada (campo `id_sucursal` NOT NULL). El único caso en que `id_sucursal` puede ser NULL es el superadmin inicial creado por seed. Nunca ofrecer "Sin sucursal" como opción al crear o editar usuarios desde la UI.

## Cómo funciona el sistema de uploads

Los archivos subidos por usuarios se guardan en `views/uploads/[modulo]/` — nunca en `views/assets/`.

**Convenciones:**
- Cada módulo tiene su propia subcarpeta: `views/uploads/admin_usuarios/`, etc.
- Cada carpeta incluye un `.gitkeep`; los archivos subidos se excluyen vía `.gitignore`.
- Nombres de archivo con `uniqid()`: `usr_67f6e3a1b2c3d.jpg`.
- Validación de tipo con `mime_content_type()` sobre el archivo temporal, no la extensión.
- Tamaño máximo por defecto: **2 MB**.

## Cómo funciona el flujo Ajax

```
JS en la vista → views/ajax/ajax_[modulo].php → controller_[modulo].php → model_[modulo].php → MySQL (PDO)
```

Los archivos `ajax_[modulo].php` son endpoints independientes — no pasan por `index.php`. Cada uno debe incluir explícitamente `connection.php` y `permisos.php`.

## Al agregar un módulo nuevo — checklist

### Archivos a crear (6)
1. `views/modules/[modulo].php` — HTML puro, sin `<html>/<head>/<body>`
2. `controllers/controller_[modulo].php`
3. `models/model_[modulo].php`
4. `views/ajax/ajax_[modulo].php`
5. `views/assets/js/[modulo].js`
6. `_docs/[modulo].md` — documentación del módulo (ver sección Documentación)

### En `index.php`
```php
require_once "controllers/controller_[modulo].php";
require_once "models/model_[modulo].php";
```

### En la BD (obligatorio para que el routing y el sidebar funcionen)
1. Asegurarse de que el área correspondiente exista en `cfg_areas`
2. Insertar el módulo en `cfg_modulos` con `clave` = ruta exacta (ej. `admin/productos`)
3. Configurar permisos por rol en `cfg_roles_permisos` (desde el módulo Roles y Permisos)

### En la vista
- Condicionar el botón "Agregar" con `<?php if (puedo('modulo', 'crear')): ?>`
- Exponer `PERMISOS_*` como variable JS para condicionar botones en DataTables
- En el ajax, verificar `puedo()` por cada acción

## Reglas de UI — SIEMPRE seguir esto

- Todos los colores van como CSS variables en `views/assets/css/theme.css`, nunca hardcodeados
- Layout: sidebar colapsable + navbar top, estilo SaaS denso
- Mobile: sidebar se convierte en offcanvas en pantallas pequeñas
- Usar clases nativas de Tabler — no inventar componentes desde cero
- Tablas siempre inicializadas con DataTables via jQuery en el JS del módulo (no con atributos `data-`)
- Alertas y confirmaciones siempre con SweetAlert2, nunca `alert()` nativo
- Selects enriquecidos con Tom Select
- Date pickers con Flatpickr
- Modales al final del HTML de la vista, antes del cierre del bloque
- Inputs numéricos: usar `step="any"` — las flechas del teclado incrementan de entero en entero pero se puede escribir decimales libremente

### DataTables + loading bar — patrón obligatorio

DataTables hace sus peticiones con XHR interno que **no dispara** los eventos globales de jQuery (`ajaxStart`/`ajaxStop`), por lo que el loading bar global de `app.js` no se activa con el patrón de objeto. **Siempre usar la función personalizada:**

```js
ajax: function (_, callback) {
    $.ajax({
        url: ajax_url,
        type: 'GET',
        dataType: 'json',
        data: { action: 'list', desde: $('#filtro_desde').val(), hasta: $('#filtro_hasta').val() },
        success: function (res) { callback({ data: res.data || [] }); },
        error:   function ()    { callback({ data: [] }); }
    });
},
```

Esto aplica a **toda** tabla que use ajax, especialmente las que tienen filtro de fechas que se recarga con un botón.

### Orden de fechas en DataTables

Cuando una columna de fecha se muestra formateada (`dd/mm/YYYY`) hay que usar render tipo-consciente para que el sort funcione correctamente:

```js
render: function (d, type) {
    if (!d) return (type === 'sort' || type === 'type') ? '' : '<span class="text-muted">—</span>';
    if (type === 'sort' || type === 'type') return d; // devolver YYYY-MM-DD para el sort
    var p = d.split('-');
    return p[2] + '/' + p[1] + '/' + p[0];
}
```

### Tom Select dentro de modales Bootstrap

Tom Select dentro de un modal con `overflow-y: auto` (o dentro de `table-responsive` con `overflow-x: auto`) queda recortado. **No usar `dropdownParent: 'body'`** — causa problemas de posicionamiento cuando Bootstrap pone `overflow: hidden` en el body al abrir el modal.

**Solución: sobrescribir `positionDropdown` después de inicializar:**

```js
var ts = new TomSelect('#mi_select', { /* opciones normales */ });
ts.positionDropdown = function () {
    var rect = this.control.getBoundingClientRect();
    this.dropdown.style.position = 'fixed';
    this.dropdown.style.top      = rect.bottom + 'px';
    this.dropdown.style.left     = rect.left   + 'px';
    this.dropdown.style.width    = rect.width  + 'px';
    this.dropdown.style.zIndex   = '9999';
};
```

`position: fixed` no es recortado por `overflow` de ancestros (salvo que tengan `transform`/`filter`). También asegurarse de que `.ts-dropdown` tenga `z-index: 9999` en `theme.css`.

### Tickets de impresión

Los tickets imprimibles viven en `views/tickets/[modulo]_ticket.php`. Son páginas HTML independientes (con su propio `<html>/<head>/<body>`) que se abren en pestaña nueva y ejecutan `window.print()` al cargar. Se acceden directamente vía URL con un parámetro `?id=`:

```js
window.open(app_url + '/views/tickets/egreso_ticket.php?id=' + id, '_blank');
```

Cada ticket consulta la BD directamente (incluye `config/connection.php`), no pasa por `index.php`. Incluir CSS de impresión con `@media print` para ocultar el botón de imprimir.

## Assets y npm

Las dependencias front se instalan con `npm install`. Los archivos se sirven **directamente desde `node_modules/`**.

`package.json` incluye: `@tabler/core`, `@tabler/icons-webfont`, `datatables.net`, `datatables.net-bs5`, `apexcharts`, `sweetalert2`, `flatpickr`, `tom-select`, `jquery`.

Rutas de referencia en `template.php` (via `$app_url`):
- Tabler Icons CSS: `node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css`
- Tabler CSS: `node_modules/@tabler/core/dist/css/tabler.min.css` (vía `theme.css`)
- Tabler JS: `node_modules/@tabler/core/dist/js/tabler.min.js`
- jQuery: `node_modules/jquery/dist/jquery.min.js`
- DataTables: `node_modules/datatables.net/js/dataTables.min.js` + `node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js`
- SweetAlert2: `node_modules/sweetalert2/dist/sweetalert2.min.{css,js}`
- ApexCharts: `node_modules/apexcharts/dist/apexcharts.min.js`
- Flatpickr: `node_modules/flatpickr/dist/flatpickr.min.{css,js}`
- Tom Select: `node_modules/tom-select/dist/css/tom-select.bootstrap5.min.css` + `node_modules/tom-select/dist/js/tom-select.complete.min.js`

`theme.css` importa Tabler y define variables custom:

```css
@import '../../../node_modules/@tabler/core/dist/css/tabler.min.css';

:root {
  --color-primario: #1b8ea3;
  --color-sidebar-bg: #0c1f28;
  --color-sidebar-texto: #8dc9d4;
  --color-sidebar-activo: #1b8ea3;
}
```

## Reglas para las tablas en BD - SIEMPRE seguir esto

- Siempre agregar las columnas `estado`, `id_alta` y `fecha_alta` (sin excepción)
- `estado`: 0 = activo (default), 1 = inactivo, 2 = eliminado/cancelado
- Los nombres de las tablas empiezan por su área: `admin_usuarios`, `admin_sucursales`, `inventario_productos`, etc.
- **Excepción:** las tablas del sistema de configuración usan el prefijo `cfg_`: `cfg_roles`, `cfg_areas`, `cfg_modulos`, `cfg_roles_permisos`

## Convenciones de código

- Nombres de variables y comentarios en inglés; UI y mensajes en español
- IDs HTML en `snake_case` con nombres en español: `tabla_usuarios`, `modal_egreso`, `btn_nuevo_producto`
- Clases CSS del proyecto en BEM: `.sidebar__item--active`
- PHP: métodos en camelCase, clases en PascalCase
- jQuery: un archivo JS por módulo en `views/assets/js/[modulo].js`

## Base de datos

- Motor: MySQL, conexión PDO en `config/connection.php`
- BD de desarrollo: `plantilla-tabler` (configurado en `phinx.php`)
- Migraciones en `db/migrations/`, seeds en `db/seeds/`
- Correr con `vendor/bin/phinx migrate`

## Documentación del sistema

El proyecto tiene un módulo de manual integrado. Los archivos viven en `_docs/` (Markdown) y se renderizan en la ruta `docs` de la app con Parsedown. La carpeta se llama `_docs/` (con guion bajo) para evitar colisión con la ruta `/docs` en Apache.

### Regla obligatoria

**Al crear un módulo nuevo:** crear `_docs/[modulo].md` con la documentación del módulo Y:
1. Agregar el enlace en `views/modules/docs.php` dentro del nav lateral.
2. Agregar la entrada en la constante `PRINT_ORDER` de `controllers/controller_docs.php` en la posición correcta del menú. Ese array es la única fuente de verdad del manual impreso completo.

**Al modificar un módulo existente** (agregar campos, cambiar comportamiento, nuevas reglas): actualizar el `_docs/[modulo].md` correspondiente en la misma respuesta.

### Estructura del archivo .md de cada módulo

```markdown
# Nombre del módulo

## Descripción
Qué hace el módulo en una o dos oraciones.

## Campos del formulario
Tabla con campo y descripción.

## Comportamiento / Reglas importantes
Bullets con las reglas de negocio no obvias.

## Estados (si aplica)
Tabla con los estados posibles.

## Permisos requeridos
Tabla con acción y permiso necesario.
```

### Enlace en el nav (docs.php)

Al agregar un módulo nuevo, insertar en el bloque correspondiente de `views/modules/docs.php`:

```html
<a href="#" class="list-group-item list-group-item-action doc-link ps-4" data-doc="[modulo]">
    <i class="ti ti-[icono] me-1"></i> Nombre visible
</a>
```
