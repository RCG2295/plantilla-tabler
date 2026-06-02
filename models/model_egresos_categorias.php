<?php

class EgresosCategoriasModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::connect();
    }

    public function getAll(): array
    {
        $sql = "SELECT c.*,
                       p.nombre AS padre_nombre
                FROM egresos_categorias c
                LEFT JOIN egresos_categorias p ON c.id_padre = p.id
                WHERE c.estado != 2
                ORDER BY COALESCE(c.id_padre, c.id), c.id_padre IS NULL DESC, c.nombre";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPadres(): array
    {
        $st = $this->db->prepare(
            "SELECT * FROM egresos_categorias WHERE id_padre IS NULL AND estado != 2 ORDER BY nombre"
        );
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllSubcategorias(int $id_padre): array
    {
        $st = $this->db->prepare(
            "SELECT * FROM egresos_categorias WHERE id_padre = ? AND estado != 2 ORDER BY nombre"
        );
        $st->execute([$id_padre]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false
    {
        $st = $this->db->prepare(
            "SELECT * FROM egresos_categorias WHERE id = ? AND estado != 2"
        );
        $st->execute([$id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function getPadres(array $exclude_nombres = []): array
    {
        $where = "id_padre IS NULL AND estado != 2";
        $params = [];
        if (!empty($exclude_nombres)) {
            $placeholders = implode(',', array_fill(0, count($exclude_nombres), '?'));
            $where .= " AND nombre NOT IN ($placeholders)";
            $params = $exclude_nombres;
        }
        $st = $this->db->prepare("SELECT id, nombre FROM egresos_categorias WHERE $where ORDER BY nombre");
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubcategorias(int $id_padre): array
    {
        $st = $this->db->prepare(
            "SELECT id, nombre FROM egresos_categorias WHERE id_padre = ? AND estado != 2 ORDER BY nombre"
        );
        $st->execute([$id_padre]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getForSelect(): array
    {
        $sql = "SELECT id, nombre, id_padre FROM egresos_categorias WHERE estado != 2 ORDER BY nombre";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByNombre(string $nombre): array|false
    {
        $st = $this->db->prepare(
            "SELECT * FROM egresos_categorias WHERE nombre = ? AND id_padre IS NULL AND estado != 2 LIMIT 1"
        );
        $st->execute([$nombre]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function nombreExiste(string $nombre, ?int $id_padre, int $exclude_id = 0): bool
    {
        if ($id_padre === null) {
            $st = $this->db->prepare(
                "SELECT COUNT(*) FROM egresos_categorias WHERE nombre = ? AND id_padre IS NULL AND estado != 2 AND id != ?"
            );
            $st->execute([$nombre, $exclude_id]);
        } else {
            $st = $this->db->prepare(
                "SELECT COUNT(*) FROM egresos_categorias WHERE nombre = ? AND id_padre = ? AND estado != 2 AND id != ?"
            );
            $st->execute([$nombre, $id_padre, $exclude_id]);
        }
        return (int) $st->fetchColumn() > 0;
    }

    public function tieneHijos(int $id): bool
    {
        $st = $this->db->prepare(
            "SELECT COUNT(*) FROM egresos_categorias WHERE id_padre = ? AND estado != 2"
        );
        $st->execute([$id]);
        return (int) $st->fetchColumn() > 0;
    }

    public function tieneEgresos(int $id): bool
    {
        $st = $this->db->prepare(
            "SELECT COUNT(*) FROM egresos WHERE (id_categoria = ? OR id_subcategoria = ?) AND estado != 2"
        );
        $st->execute([$id, $id]);
        return (int) $st->fetchColumn() > 0;
    }

    public function insert(array $data): int
    {
        $st = $this->db->prepare(
            "INSERT INTO egresos_categorias (nombre, descripcion, id_padre, id_alta) VALUES (?, ?, ?, ?)"
        );
        $st->execute([
            $data['nombre'],
            $data['descripcion'] ?: null,
            $data['id_padre'] ?: null,
            $data['id_alta'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(array $data): void
    {
        $st = $this->db->prepare(
            "UPDATE egresos_categorias SET nombre = ?, descripcion = ?, id_padre = ? WHERE id = ?"
        );
        $st->execute([
            $data['nombre'],
            $data['descripcion'] ?: null,
            $data['id_padre'] ?: null,
            $data['id'],
        ]);
    }

    public function delete(int $id): void
    {
        $st = $this->db->prepare("UPDATE egresos_categorias SET estado = 2 WHERE id = ?");
        $st->execute([$id]);
    }
}
