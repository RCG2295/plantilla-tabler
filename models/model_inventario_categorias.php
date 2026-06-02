<?php

class InventarioCategoriasModel
{
    public function getAll()
    {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT c.id, c.nombre, c.descripcion, c.estado,
                   (SELECT COUNT(*) FROM inventario_categorias s
                    WHERE s.id_padre = c.id AND s.estado != 2) AS total_subcategorias
            FROM inventario_categorias c
            WHERE c.id_padre IS NULL AND c.estado != 2
            ORDER BY c.nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByPadre($id_padre)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT id, nombre, descripcion, estado
            FROM inventario_categorias
            WHERE id_padre = ? AND estado != 2
            ORDER BY nombre
        ");
        $stmt->execute([$id_padre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPadres()
    {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT id, nombre
            FROM inventario_categorias
            WHERE id_padre IS NULL AND estado != 2
            ORDER BY nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT id, nombre, descripcion, id_padre, estado
            FROM inventario_categorias
            WHERE id = ? AND estado != 2
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function nombreExiste($nombre, $id_padre, $exclude_id = null)
    {
        $db = Connection::connect();
        if ($id_padre) {
            $sql    = "SELECT id FROM inventario_categorias WHERE nombre = ? AND id_padre = ? AND estado != 2";
            $params = [$nombre, $id_padre];
        } else {
            $sql    = "SELECT id FROM inventario_categorias WHERE nombre = ? AND id_padre IS NULL AND estado != 2";
            $params = [$nombre];
        }
        if ($exclude_id) {
            $sql    .= " AND id != ?";
            $params[] = $exclude_id;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function tieneHijos($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM inventario_categorias WHERE id_padre = ? AND estado != 2");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    public function insert($data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO inventario_categorias (nombre, descripcion, id_padre, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?: null,
            $data['id_padre'] ?: null,
            $data['estado'],
            $data['id_alta'] ?? null,
        ]);
    }

    public function update($id, $data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            UPDATE inventario_categorias
            SET nombre = ?, descripcion = ?, id_padre = ?, estado = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?: null,
            $data['id_padre'] ?: null,
            $data['estado'],
            $id,
        ]);
    }

    public function delete($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE inventario_categorias SET estado = 2 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
