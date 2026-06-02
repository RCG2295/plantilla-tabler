# Roles y Permisos

## Descripción

Gestión de los roles del sistema y la matriz de permisos que determina qué puede hacer cada rol en cada módulo.

## Roles

| Campo | Descripción |
|-------|-------------|
| Nombre | Nombre del rol (ej. "Administrador", "Vendedor") |
| Es superadmin | Si está activo, el rol tiene acceso total sin restricciones |

## Matriz de permisos

Para cada rol se configura individualmente el acceso a cada módulo:

| Permiso | Descripción |
|---------|-------------|
| Ver | Puede acceder al módulo y ver la lista |
| Crear | Puede agregar nuevos registros |
| Editar | Puede modificar registros existentes |
| Eliminar | Puede marcar registros como eliminados |

Los permisos se asignan por módulo. Si un rol tiene permiso de crear, editar o eliminar, automáticamente tiene permiso de ver.

## Roles superadmin

Un rol marcado como `es_superadmin = 1` omite todos los checks de permisos. Tiene acceso total al sistema incluyendo cambio de sucursal.

## Impacto en la sesión

Los permisos del rol se cargan en la sesión al iniciar sesión. Si se modifican los permisos de un rol, el usuario debe cerrar sesión y volver a entrar para que los cambios tomen efecto.

## Permisos requeridos

Solo roles con `es_superadmin = 1` pueden gestionar roles y permisos.
