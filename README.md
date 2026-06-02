# Plantilla Tabler — Sistema de Gestión Empresarial

Sistema web de gestión empresarial construido sobre PHP puro con arquitectura MVC propia y la librería de UI [Tabler](https://tabler.io/) (Bootstrap 5). Incluye punto de venta, inventario, compras, egresos y administración de usuarios con control de acceso por roles y sucursales.

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8+ (MVC sin framework) |
| Base de datos | MySQL / MariaDB con PDO |
| Migraciones | [Phinx](https://phinx.org/) |
| UI | [Tabler](https://tabler.io/) sobre Bootstrap 5 |
| Frontend | HTML + CSS + JavaScript + jQuery |
| Tablas | DataTables |
| Gráficas | ApexCharts |
| Selects | Tom Select |
| Fechas | Flatpickr |
| Alertas | SweetAlert2 |
| Íconos | Tabler Icons (webfont) |
| Assets | Servidos directamente desde `node_modules/` |

---

## Requisitos

- PHP 8.0 o superior
- MySQL 5.7+ / MariaDB 10.3+
- Composer
- Node.js + npm
- Apache con `mod_rewrite` habilitado (o nginx equivalente)

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone <url-del-repo> plantilla-tabler
cd plantilla-tabler

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias frontend
npm install

# 4. Configurar entorno
cp .env.example .env
# Editar .env con los valores correspondientes:
#   DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL

# 5. Correr migraciones
vendor/bin/phinx migrate

# 6. Correr seeds (en este orden exacto)
vendor/bin/phinx seed:run -s CfgRolesSeed
vendor/bin/phinx seed:run -s CfgAreasSeed
vendor/bin/phinx seed:run -s CfgModulosSeed
vendor/bin/phinx seed:run -s CfgRolesPermisosSeed
vendor/bin/phinx seed:run -s UsuarioAdminSeed
```

El seed `UsuarioAdminSeed` crea el usuario administrador inicial. Revisa ese archivo para ver las credenciales por defecto y cámbialas inmediatamente después del primer login.

---

## Configuración de entorno (`.env`)

```env
APP_URL=http://localhost/plantilla-tabler

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=plantilla-tabler
DB_USERNAME=root
DB_PASSWORD=
```

---

## Módulos del sistema

### Administración
| Módulo | Ruta | Descripción |
|---|---|---|
| Usuarios | `admin/usuarios` | Alta, edición y baja de usuarios. Todos los usuarios deben tener sucursal asignada. |
| Sucursales | `admin/sucursales` | Catálogo de sucursales. |

### Configuración
| Módulo | Ruta | Descripción |
|---|---|---|
| Áreas | `cfg/areas` | Secciones del sidebar (nombre, ícono, orden). |
| Módulos | `cfg/modulos` | Registro de módulos del sistema vinculados a áreas. |
| Roles y permisos | `cfg/roles` | Gestión de roles con matriz de permisos por módulo (ver, crear, editar, eliminar). |
| Mi perfil | `cfg/perfil` | Edición de datos personales y contraseña del usuario en sesión. |

### Inventario
| Módulo | Ruta | Descripción |
|---|---|---|
| Categorías | `inventario/categorias` | Árbol de categorías de productos. |
| Unidades de medida | `inventario/unidades` | Catálogo de unidades. |
| Motivos de movimiento | `inventario/motivos` | Catálogo de motivos para entradas/salidas de inventario. |
| Productos | `inventario/productos` | Catálogo de productos con stock por sucursal, fotos, precios y soporte para productos fraccionados. |
| Movimientos | `inventario/movimientos` | Historial de entradas y salidas de inventario por sucursal. |

### Compras
| Módulo | Ruta | Descripción |
|---|---|---|
| Proveedores | `compras/proveedores` | Catálogo de proveedores. |
| Compras | `compras/compras` | Registro de compras a proveedores. Actualiza stock automáticamente y genera egreso vinculado. |

### Egresos
| Módulo | Ruta | Descripción |
|---|---|---|
| Categorías | `egresos/categorias` | Árbol de categorías de egresos (dos niveles). |
| Egresos | `egresos/egresos` | Registro de egresos con categoría, método de pago y referencia. Impresión de ticket. |

### Ventas
| Módulo | Ruta | Descripción |
|---|---|---|
| Tipo de cambio | `ventas/tipo_cambio` | Registro del tipo de cambio USD/MXN vigente. |
| Mi caja | `ventas/mi_caja` | Apertura y cierre de turno, movimientos de caja (retiros/ingresos), corte de caja. |
| Punto de venta | `ventas/pos` | POS de pantalla completa. Requiere turno activo en la sucursal en sesión. |
| Historial de turnos | `ventas/historial_turnos` | Consulta de turnos por rango de fechas con corte detallado. |
| Historial de ventas | `ventas/historial_ventas` | Consulta de ventas por rango de fechas con detalle por forma de pago. |

### Otros
| Módulo | Ruta | Descripción |
|---|---|---|
| Dashboard | `dashboard` | KPIs de la semana, accesos rápidos, gráficas de ventas y productos con stock bajo. |
| Manual / Docs | `docs` | Manual integrado en Markdown renderizado con Parsedown. |

---

## Sistema de roles y permisos

- Los roles se configuran desde `cfg/roles`.
- Un rol puede marcarse como `es_superadmin`, lo que omite todas las verificaciones de permiso.
- Los permisos se definen por módulo: `ver`, `crear`, `editar`, `eliminar`.
- El helper `puedo($modulo, $accion)` (definido en `config/permisos.php`) está disponible en toda la aplicación.
- El sidebar se construye dinámicamente mostrando solo los módulos que el usuario puede `ver`.

---

## Sucursales y sesión

- Cada usuario tiene una sucursal asignada obligatoriamente.
- Al iniciar sesión, la sucursal del usuario se carga en `$_SESSION['id_sucursal']`.
- Todos los módulos filtran datos por la sucursal en sesión.
- Un usuario con múltiples accesos puede tener turnos de caja independientes por sucursal — al cambiar de sucursal, el turno de la sucursal anterior no aparece en la nueva.
- El superadmin sin sucursal específica (`id_sucursal = NULL`) ve datos de todas las sucursales.

---

## Estructura de carpetas

```
plantilla-tabler/
├── config/
│   ├── connection.php          ← Conexión PDO (lee .env)
│   ├── controller_template.php ← Carga views/template.php
│   └── permisos.php            ← Helper puedo()
├── controllers/                ← Un controller por módulo
├── models/                     ← Un model por módulo
├── views/
│   ├── ajax/                   ← Endpoints AJAX por módulo
│   ├── assets/
│   │   ├── css/theme.css       ← Variables de color y overrides
│   │   ├── img/
│   │   └── js/                 ← Un JS por módulo + app.js global
│   ├── modules/                ← Vistas HTML por módulo
│   │   └── sections/           ← Sidebar y navbar
│   ├── tickets/                ← Tickets imprimibles (compras, egresos)
│   └── template.php            ← Layout principal
├── db/
│   ├── migrations/             ← Migraciones Phinx
│   └── seeds/                  ← Seeders Phinx
├── _docs/                      ← Manuales en Markdown por módulo
├── index.php                   ← Entry point
├── .htaccess                   ← Rewrite rules
├── phinx.php                   ← Configuración de migraciones
├── composer.json
└── package.json
```

---

## Routing

Todas las peticiones pasan por `index.php` via `.htaccess`. La ruta URL se convierte en nombre de archivo reemplazando `/` por `_`:

```
/admin/usuarios  →  views/modules/admin_usuarios.php
/cfg/roles       →  views/modules/cfg_roles.php
/ventas/pos      →  layout POS (pantalla completa, sin sidebar)
```

El acceso se verifica con `puedo($ruta, 'ver')`. Si el archivo no existe o no hay permiso, se muestra `404.php`.

---

## Comandos útiles

```bash
# Crear nueva migración
vendor/bin/phinx create NombreMigracion

# Revertir última migración
vendor/bin/phinx rollback

# Correr un seed específico
vendor/bin/phinx seed:run -s NombreDelSeed
```

---

## Convenciones

- Variables y comentarios en inglés; UI y mensajes en español.
- IDs HTML en `snake_case`: `tabla_usuarios`, `modal_egreso`.
- PHP: métodos en `camelCase`, clases en `PascalCase`.
- Un archivo JS por módulo en `views/assets/js/[modulo].js`.
- Tablas siempre con DataTables vía jQuery.
- Alertas y confirmaciones con SweetAlert2, nunca `alert()` nativo.
- Selects enriquecidos con Tom Select; fechas con Flatpickr.
- Columnas obligatorias en todas las tablas: `estado`, `id_alta`, `fecha_alta`.
- `estado`: `0` = activo, `1` = inactivo/cancelado, `2` = eliminado.
