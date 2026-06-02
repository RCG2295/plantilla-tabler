# Manual del Sistema e-Sol

## Descripción general

Sistema de gestión empresarial para el control de inventario, compras, egresos y usuarios, con soporte multisucursal. Desarrollado sobre PHP con arquitectura MVC, interfaz Tabler/Bootstrap 5.

## Acceso al sistema

- Ingresar con **correo electrónico** y **contraseña** en la pantalla de login.
- Si las credenciales son incorrectas, el sistema muestra un mensaje de error.
- La sesión se mantiene activa mientras el navegador esté abierto.

## Roles y permisos

Cada usuario tiene asignado un **rol** que determina a qué módulos puede acceder y qué acciones puede realizar:

| Permiso | Descripción |
|---------|-------------|
| Ver | Acceder al módulo y ver la lista |
| Crear | Agregar nuevos registros |
| Editar | Modificar registros existentes |
| Eliminar | Marcar registros como eliminados |

El **Superadministrador** tiene acceso total sin restricciones y puede cambiar entre sucursales.

## Sistema multisucursal

Los registros de inventario, compras y egresos están ligados a una **sucursal**. Cada usuario pertenece a una sucursal y solo ve los datos de la suya.

Un superadministrador puede cambiar de sucursal desde el selector en la barra superior para revisar datos de cualquier sucursal.

El catálogo de productos es **compartido** entre todas las sucursales, pero el **stock es independiente** por sucursal.

## Navegación

- **Sidebar izquierdo**: accesos directos a los módulos disponibles según el rol.
- **Barra superior**: nombre del usuario, sucursal activa y acceso al perfil.
- En pantallas pequeñas el sidebar se convierte en menú desplegable.

## Estados de los registros

La mayoría de los módulos manejan tres estados:

| Estado | Descripción |
|--------|-------------|
| Activo (0) | Registro visible y operativo |
| Inactivo (1) | Deshabilitado temporalmente |
| Eliminado (2) | Eliminación lógica, no aparece en la lista |

## Áreas del sistema

| Área | Módulos |
|------|---------|
| Administración | Usuarios, Sucursales |
| Inventario | Productos, Movimientos, Categorías, Unidades, Motivos |
| Compras | Compras, Proveedores |
| Egresos | Egresos, Categorías de egresos |
| Configuración | Roles y permisos, Áreas, Módulos, Mi perfil |
