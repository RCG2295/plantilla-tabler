<?php

class ComprasProveedoresModel
{
    public function getAll()
    {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT id, nombre, razon_social, rfc, telefono, email,
                   direccion, notas, estado, fecha_alta
            FROM compras_proveedores
            WHERE estado != 2
            ORDER BY nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT id, nombre, razon_social, rfc, telefono, email,
                   direccion, notas, estado
            FROM compras_proveedores
            WHERE id = ? AND estado != 2
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getForSelect()
    {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT id, nombre, razon_social
            FROM compras_proveedores
            WHERE estado = 0
            ORDER BY nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function nombreExiste($nombre, $exclude_id = null)
    {
        $db     = Connection::connect();
        $sql    = "SELECT id FROM compras_proveedores WHERE nombre = ? AND estado != 2";
        $params = [$nombre];
        if ($exclude_id) {
            $sql     .= " AND id != ?";
            $params[] = $exclude_id;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function tieneCompras($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM compras WHERE id_proveedor = ? AND estado != 2");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    public function insert($data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO compras_proveedores
                (nombre, razon_social, rfc, telefono, email, direccion, notas, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['razon_social'] ?: null,
            $data['rfc'] ?: null,
            $data['telefono'] ?: null,
            $data['email'] ?: null,
            $data['direccion'] ?: null,
            $data['notas'] ?: null,
            $data['estado'],
            $data['id_alta'] ?? null,
        ]);
    }

    public function update($id, $data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            UPDATE compras_proveedores
            SET nombre = ?, razon_social = ?, rfc = ?, telefono = ?, email = ?,
                direccion = ?, notas = ?, estado = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['razon_social'] ?: null,
            $data['rfc'] ?: null,
            $data['telefono'] ?: null,
            $data['email'] ?: null,
            $data['direccion'] ?: null,
            $data['notas'] ?: null,
            $data['estado'],
            $id,
        ]);
    }

    public function delete($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE compras_proveedores SET estado = 2 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
