<?php

class EgresosEgresosModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::connect();
    }

    public function getByEstado(int $estado): array
    {
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $sql = "SELECT e.*,
                       c.nombre  AS categoria_nombre,
                       s.nombre  AS subcategoria_nombre,
                       u.nombre  AS usuario_nombre,
                       u.apellidos AS usuario_apellidos,
                       cp.folio  AS compra_folio
                FROM egresos e
                LEFT JOIN egresos_categorias c  ON e.id_categoria    = c.id
                LEFT JOIN egresos_categorias s  ON e.id_subcategoria = s.id
                LEFT JOIN admin_usuarios     u  ON e.id_alta          = u.id
                LEFT JOIN compras            cp ON e.id_compra        = cp.id
                WHERE e.estado = ?
                  AND (e.id_sucursal = ? OR ? IS NULL)
                ORDER BY e.fecha_alta DESC";
        $st = $this->db->prepare($sql);
        $st->execute([$estado, $id_sucursal, $id_sucursal]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEstadoConFechas(int $estado, string $desde, string $hasta): array
    {
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $sql = "SELECT e.*,
                       c.nombre  AS categoria_nombre,
                       s.nombre  AS subcategoria_nombre,
                       u.nombre  AS usuario_nombre,
                       u.apellidos AS usuario_apellidos,
                       cp.folio  AS compra_folio
                FROM egresos e
                LEFT JOIN egresos_categorias c  ON e.id_categoria    = c.id
                LEFT JOIN egresos_categorias s  ON e.id_subcategoria = s.id
                LEFT JOIN admin_usuarios     u  ON e.id_alta          = u.id
                LEFT JOIN compras            cp ON e.id_compra        = cp.id
                WHERE e.estado = ?
                  AND e.fecha_egreso BETWEEN ? AND ?
                  AND (e.id_sucursal = ? OR ? IS NULL)
                ORDER BY e.fecha_egreso DESC";
        $st = $this->db->prepare($sql);
        $st->execute([$estado, $desde, $hasta, $id_sucursal, $id_sucursal]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false
    {
        $sql = "SELECT e.*,
                       c.nombre  AS categoria_nombre,
                       s.nombre  AS subcategoria_nombre,
                       cp.folio  AS compra_folio
                FROM egresos e
                LEFT JOIN egresos_categorias c  ON e.id_categoria    = c.id
                LEFT JOIN egresos_categorias s  ON e.id_subcategoria = s.id
                LEFT JOIN compras            cp ON e.id_compra        = cp.id
                WHERE e.id = ?";
        $st = $this->db->prepare($sql);
        $st->execute([$id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function insert(array $data): int
    {
        $id_sucursal = $data['id_sucursal'] ?? $_SESSION['id_sucursal'] ?? null;
        $st = $this->db->prepare("
            INSERT INTO egresos
                (id_sucursal, id_compra, id_categoria, id_subcategoria, concepto, fecha_egreso, monto, metodo_pago, referencia, notas, archivo, id_alta)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $st->execute([
            $id_sucursal,
            $data['id_compra']       ?: null,
            $data['id_categoria']    ?: null,
            $data['id_subcategoria'] ?: null,
            $data['concepto'],
            $data['fecha_egreso']    ?: null,
            $data['monto'],
            $data['metodo_pago']     ?? 'efectivo',
            $data['referencia']      ?: null,
            $data['notas']           ?: null,
            $data['archivo']         ?? null,
            $data['id_alta'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function cancelar(int $id): void
    {
        $st = $this->db->prepare("UPDATE egresos SET estado = 1 WHERE id = ?");
        $st->execute([$id]);
    }

    public function cancelarPorCompra(int $id_compra): void
    {
        $st = $this->db->prepare("UPDATE egresos SET estado = 1 WHERE id_compra = ? AND estado = 0");
        $st->execute([$id_compra]);
    }
}
