<?php

class AdminSucursalesModel
{
    public function getAll(): array
    {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT id, nombre, direccion, telefono, estado
            FROM admin_sucursales
            WHERE estado != 2
            ORDER BY nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT id, nombre, direccion, telefono, estado
            FROM admin_sucursales
            WHERE id = ? AND estado != 2
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert(array $data): int|false
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO admin_sucursales (nombre, direccion, telefono, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $ok = $stmt->execute([
            $data['nombre'],
            $data['direccion'] ?: null,
            $data['telefono']  ?: null,
            $data['estado'],
            $data['id_alta']   ?? null,
        ]);
        return $ok ? (int) $db->lastInsertId() : false;
    }

    public function update(int $id, array $data): bool
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            UPDATE admin_sucursales
            SET nombre = ?, direccion = ?, telefono = ?, estado = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['direccion'] ?: null,
            $data['telefono']  ?: null,
            $data['estado'],
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE admin_sucursales SET estado = 2 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function initStockParaSucursal(int $id_sucursal, ?int $id_alta = null): void
    {
        $db = Connection::connect();
        $db->prepare("
            INSERT IGNORE INTO inventario_stock
                (id_producto, id_sucursal, stock_actual, estado, id_alta, fecha_alta)
            SELECT id, ?, 0.00, 0, ?, NOW()
            FROM inventario_productos
            WHERE estado != 2
        ")->execute([$id_sucursal, $id_alta]);
    }
}
