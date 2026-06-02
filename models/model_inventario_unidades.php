<?php

class InventarioUnidadesModel
{
    public function getAll()
    {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT id, nombre, abreviatura, estado
            FROM inventario_unidades_medida
            WHERE estado != 2
            ORDER BY nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT id, nombre, abreviatura, estado
            FROM inventario_unidades_medida
            WHERE id = ? AND estado != 2
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function nombreExiste($nombre, $exclude_id = null)
    {
        $db = Connection::connect();
        if ($exclude_id) {
            $stmt = $db->prepare("SELECT id FROM inventario_unidades_medida WHERE nombre = ? AND id != ? AND estado != 2");
            $stmt->execute([$nombre, $exclude_id]);
        } else {
            $stmt = $db->prepare("SELECT id FROM inventario_unidades_medida WHERE nombre = ? AND estado != 2");
            $stmt->execute([$nombre]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function insert($data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO inventario_unidades_medida (nombre, abreviatura, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['abreviatura'],
            $data['estado'],
            $data['id_alta'] ?? null,
        ]);
    }

    public function update($id, $data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            UPDATE inventario_unidades_medida
            SET nombre = ?, abreviatura = ?, estado = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['abreviatura'],
            $data['estado'],
            $id,
        ]);
    }

    public function delete($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE inventario_unidades_medida SET estado = 2 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
