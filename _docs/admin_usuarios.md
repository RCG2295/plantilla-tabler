# Gestión de Usuarios

## Descripción

Permite crear, editar y desactivar los usuarios del sistema. Cada usuario tiene un rol que determina sus permisos y puede estar asociado a una sucursal.

## Campos del formulario

| Campo | Descripción |
|-------|-------------|
| Nombre | Nombre del usuario |
| Apellidos | Apellidos del usuario |
| Correo electrónico | Usado para iniciar sesión; debe ser único en el sistema |
| Teléfono | Opcional |
| Rol | Define los permisos del usuario en el sistema |
| Sucursal | Sucursal a la que pertenece el usuario (opcional para superadmin) |
| Estado | Activo o Inactivo |
| Contraseña | Mínimo 6 caracteres; al editar se deja en blanco para no cambiarla |
| Foto de perfil | JPG, PNG o WEBP, máximo 2 MB |

## Estados del usuario

| Estado | Descripción |
|--------|-------------|
| Activo | Puede iniciar sesión en el sistema |
| Inactivo | No puede iniciar sesión; el registro se conserva |
| Eliminado | Eliminación lógica; no aparece en la lista |

## Reglas importantes

- El correo electrónico debe ser único; el sistema avisa si ya está registrado.
- Al crear un usuario la contraseña es obligatoria.
- Al editar, si los campos de contraseña se dejan en blanco, la contraseña actual no cambia.
- La foto de perfil se muestra en el navbar y en la tabla de usuarios.
- Un usuario eliminado no puede recuperarse desde la interfaz; requiere ajuste directo en base de datos.

## Permisos requeridos

| Acción | Permiso |
|--------|---------|
| Ver lista de usuarios | ver |
| Agregar usuario | crear |
| Editar datos de usuario | editar |
| Eliminar usuario | eliminar |
