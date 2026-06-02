<?php

class LoginModel {

    public function getUserByEmail($email) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT u.id, u.nombre, u.apellidos, u.email, u.password, u.imagen,
                   u.id_sucursal, s.nombre AS sucursal_nombre,
                   r.id AS id_rol, r.nombre AS rol_nombre, r.es_superadmin
            FROM admin_usuarios u
            JOIN cfg_roles r ON r.id = u.id_rol
            LEFT JOIN admin_sucursales s ON s.id = u.id_sucursal
            WHERE u.email = ? AND u.estado = 0
            LIMIT 1
        ");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPermisosByRol($id_rol) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT m.clave, rp.ver, rp.crear, rp.editar, rp.eliminar
            FROM cfg_roles_permisos rp
            JOIN cfg_modulos m ON m.id = rp.id_modulo
            WHERE rp.id_rol = ?
              AND rp.estado = 0
              AND m.estado = 0
        ");
        $stmt->execute([$id_rol]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
