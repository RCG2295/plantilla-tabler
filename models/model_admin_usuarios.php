<?php

class AdminUsuariosModel {

    public function getAll() {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT u.id, u.nombre, u.apellidos, u.email,
                   u.id_rol, r.nombre AS rol_nombre,
                   u.id_sucursal, s.nombre AS sucursal_nombre,
                   u.telefono, u.imagen, u.estado
            FROM admin_usuarios u
            LEFT JOIN cfg_roles r ON r.id = u.id_rol
            LEFT JOIN admin_sucursales s ON s.id = u.id_sucursal
            WHERE u.estado != 2
            ORDER BY u.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT id, nombre, apellidos, email, id_rol, id_sucursal, telefono, imagen, estado
            FROM admin_usuarios
            WHERE id = ? AND estado != 2
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function emailExiste($email, $exclude_id = null) {
        $db = Connection::connect();
        if ($exclude_id) {
            $stmt = $db->prepare("SELECT id FROM admin_usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $exclude_id]);
        } else {
            $stmt = $db->prepare("SELECT id FROM admin_usuarios WHERE email = ?");
            $stmt->execute([$email]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function insert($data) {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO admin_usuarios
                (nombre, apellidos, email, password, id_rol, id_sucursal, telefono, imagen, estado, id_alta, fecha_alta)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['apellidos'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['id_rol']      ?: null,
            $data['id_sucursal'] ?: null,
            $data['telefono']    ?: null,
            $data['imagen']      ?: null,
            $data['estado'],
            $data['id_alta']     ?? null,
        ]);
    }

    public function update($id, $data) {
        $db = Connection::connect();

        $sets   = ['nombre = ?', 'apellidos = ?', 'email = ?', 'id_rol = ?', 'id_sucursal = ?', 'telefono = ?', 'estado = ?'];
        $params = [
            $data['nombre'],
            $data['apellidos'],
            $data['email'],
            $data['id_rol']      ?: null,
            $data['id_sucursal'] ?: null,
            $data['telefono']    ?: null,
            $data['estado'],
        ];

        if (!empty($data['password'])) {
            $sets[]   = 'password = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if ($data['imagen'] !== null) {
            $sets[]   = 'imagen = ?';
            $params[] = $data['imagen'];
        }

        $params[] = $id;
        $stmt     = $db->prepare('UPDATE admin_usuarios SET ' . implode(', ', $sets) . ' WHERE id = ?');
        return $stmt->execute($params);
    }

    public function delete($id) {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE admin_usuarios SET estado = 2 WHERE id = ?");
        return $stmt->execute([$id]);
    }

}
