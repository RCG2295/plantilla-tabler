<?php

class InventarioMotivosModel
{
    public function getAll()
    {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT id, nombre, tipo, estado
            FROM inventario_motivos_movimiento
            WHERE estado != 2
            ORDER BY tipo, nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByTipo($tipo)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT id, nombre, tipo
            FROM inventario_motivos_movimiento
            WHERE estado = 0 AND (tipo = ? OR tipo = 'ambos')
            ORDER BY nombre
        ");
        $stmt->execute([$tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT id, nombre, tipo, estado
            FROM inventario_motivos_movimiento
            WHERE id = ? AND estado != 2
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function nombreExiste($nombre, $exclude_id = null)
    {
        $db = Connection::connect();
        if ($exclude_id) {
            $stmt = $db->prepare("SELECT id FROM inventario_motivos_movimiento WHERE nombre = ? AND id != ? AND estado != 2");
            $stmt->execute([$nombre, $exclude_id]);
        } else {
            $stmt = $db->prepare("SELECT id FROM inventario_motivos_movimiento WHERE nombre = ? AND estado != 2");
            $stmt->execute([$nombre]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function insert($data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO inventario_motivos_movimiento (nombre, tipo, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['tipo'],
            $data['estado'],
            $data['id_alta'] ?? null,
        ]);
    }

    public function update($id, $data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            UPDATE inventario_motivos_movimiento
            SET nombre = ?, tipo = ?, estado = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['tipo'],
            $data['estado'],
            $id,
        ]);
    }

    public function delete($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE inventario_motivos_movimiento SET estado = 2 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
