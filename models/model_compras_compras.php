<?php

class ComprasComprasModel
{
    public function getAll()
    {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT e.id, e.folio, e.fecha_compra, e.subtotal, e.iva_total, e.total,
                   e.notas, e.archivo, e.estado, e.fecha_alta,
                   p.nombre  AS proveedor_nombre,
                   u.nombre  AS usuario_nombre,
                   (SELECT COUNT(*) FROM compras_items d
                    WHERE d.id_compra = e.id AND d.estado != 2) AS total_items
            FROM compras e
            LEFT JOIN compras_proveedores p ON p.id = e.id_proveedor
            LEFT JOIN admin_usuarios u ON u.id = e.id_alta
            WHERE e.estado != 2
            ORDER BY e.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEstado(int $estado): array
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("
            SELECT e.id, e.folio, e.fecha_compra, e.subtotal, e.iva_total, e.total,
                   e.notas, e.archivo, e.estado, e.fecha_alta,
                   p.nombre  AS proveedor_nombre,
                   u.nombre  AS usuario_nombre,
                   (SELECT COUNT(*) FROM compras_items d
                    WHERE d.id_compra = e.id AND d.estado != 2) AS total_items
            FROM compras e
            LEFT JOIN compras_proveedores p ON p.id = e.id_proveedor
            LEFT JOIN admin_usuarios u ON u.id = e.id_alta
            WHERE e.estado = ? AND (e.id_sucursal = ? OR ? IS NULL)
            ORDER BY e.id DESC
        ");
        $stmt->execute([$estado, $id_sucursal, $id_sucursal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEstadoConFechas(int $estado, string $desde, string $hasta): array
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("
            SELECT e.id, e.folio, e.fecha_compra, e.subtotal, e.iva_total, e.total,
                   e.notas, e.archivo, e.estado, e.fecha_alta,
                   p.nombre  AS proveedor_nombre,
                   u.nombre  AS usuario_nombre,
                   (SELECT COUNT(*) FROM compras_items d
                    WHERE d.id_compra = e.id AND d.estado != 2) AS total_items
            FROM compras e
            LEFT JOIN compras_proveedores p ON p.id = e.id_proveedor
            LEFT JOIN admin_usuarios u ON u.id = e.id_alta
            WHERE e.estado = ?
              AND e.fecha_compra BETWEEN ? AND ?
              AND (e.id_sucursal = ? OR ? IS NULL)
            ORDER BY e.id DESC
        ");
        $stmt->execute([$estado, $desde, $hasta, $id_sucursal, $id_sucursal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT e.id, e.folio, e.fecha_compra, e.subtotal, e.iva_total, e.total,
                   e.notas, e.archivo, e.estado, e.fecha_alta,
                   p.nombre       AS proveedor_nombre,
                   p.razon_social AS proveedor_razon_social,
                   p.rfc          AS proveedor_rfc,
                   u.nombre       AS usuario_nombre,
                   u.apellidos    AS usuario_apellidos
            FROM compras e
            LEFT JOIN compras_proveedores p ON p.id = e.id_proveedor
            LEFT JOIN admin_usuarios u ON u.id = e.id_alta
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDetalle($id_compra)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT d.id, d.id_producto, d.cantidad, d.cantidad_base,
                   d.precio_unitario, d.iva, d.subtotal, d.iva_monto, d.total_linea,
                   pr.nombre AS producto_nombre, pr.codigo AS producto_codigo,
                   pr.se_fracciona, pr.cantidad_presentacion,
                   u.abreviatura
            FROM compras_items d
            JOIN inventario_productos pr ON pr.id = d.id_producto
            LEFT JOIN inventario_unidades_medida u ON u.id = pr.id_unidad_medida
            WHERE d.id_compra = ? AND d.estado != 2
            ORDER BY d.id
        ");
        $stmt->execute([$id_compra]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generarFolio()
    {
        $db   = Connection::connect();
        $year = date('Y');
        $last = $db->query("
            SELECT folio FROM compras
            WHERE folio LIKE 'COM-{$year}-%'
            ORDER BY id DESC LIMIT 1
        ")->fetchColumn();

        $num = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'COM-' . $year . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function insertEncabezado($data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO compras
                (id_sucursal, folio, fecha_compra, id_proveedor, subtotal, iva_total, total, notas, archivo, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['id_sucursal'] ?? null,
            $data['folio'],
            $data['fecha_compra'],
            $data['id_proveedor'] ?: null,
            $data['subtotal'],
            $data['iva_total'],
            $data['total'],
            $data['notas'] ?: null,
            $data['archivo'] ?? null,
            $data['id_alta'] ?? null,
        ]);
        return (int) $db->lastInsertId();
    }

    public function insertDetalle($data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO compras_items
                (id_compra, id_producto, cantidad, cantidad_base, precio_unitario,
                 iva, subtotal, iva_monto, total_linea, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['id_compra'],
            $data['id_producto'],
            $data['cantidad'],
            $data['cantidad_base'],
            $data['precio_unitario'],
            $data['iva'],
            $data['subtotal'],
            $data['iva_monto'],
            $data['total_linea'],
            $data['id_alta'] ?? null,
        ]);
    }

    public function getStockProducto($id_producto)
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("
            SELECT COALESCE(s.stock_actual, 0) AS stock_actual, p.nombre, u.abreviatura
            FROM inventario_productos p
            LEFT JOIN inventario_unidades_medida u ON u.id = p.id_unidad_medida
            LEFT JOIN inventario_stock s ON s.id_producto = p.id AND s.id_sucursal = ?
            WHERE p.id = ?
        ");
        $stmt->execute([$id_sucursal, $id_producto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStock($id_producto, $stock_nuevo)
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $id_alta     = $_SESSION['usuario_id']  ?? null;
        $stmt        = $db->prepare("
            INSERT INTO inventario_stock (id_producto, id_sucursal, stock_actual, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, 0, ?, NOW())
            ON DUPLICATE KEY UPDATE stock_actual = ?
        ");
        return $stmt->execute([$id_producto, $id_sucursal, $stock_nuevo, $id_alta, $stock_nuevo]);
    }

    public function registrarMovimiento($data)
    {
        $db          = Connection::connect();
        $id_sucursal = $data['id_sucursal'] ?? $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("
            INSERT INTO inventario_movimientos
                (id_producto, id_sucursal, tipo, cantidad, stock_anterior, stock_nuevo, id_motivo, notas, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['id_producto'],
            $id_sucursal,
            $data['tipo'],
            $data['cantidad'],
            $data['stock_anterior'],
            $data['stock_nuevo'],
            $data['id_motivo'] ?? null,
            $data['notas'] ?? null,
            $data['id_alta'] ?? null,
        ]);
    }

    public function getMotivoPorNombre($nombre)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("SELECT id FROM inventario_motivos_movimiento WHERE nombre = ? LIMIT 1");
        $stmt->execute([$nombre]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['id'] : null;
    }

    public function getEgresosCategoriaPorNombre($nombre)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare(
            "SELECT id FROM egresos_categorias WHERE nombre = ? AND id_padre IS NULL AND estado != 2 LIMIT 1"
        );
        $stmt->execute([$nombre]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['id'] : null;
    }

    public function insertEgreso($data)
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("
            INSERT INTO egresos
                (id_sucursal, id_compra, id_categoria, concepto, fecha_egreso, monto, metodo_pago, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, ?, 'efectivo', ?, NOW())
        ");
        return $stmt->execute([
            $id_sucursal,
            $data['id_compra'],
            $data['id_categoria'] ?? null,
            $data['concepto'],
            $data['fecha_egreso'] ?? null,
            $data['monto'],
            $data['id_alta'] ?? null,
        ]);
    }

    public function cancelarEgreso($id_compra)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE egresos SET estado = 1 WHERE id_compra = ? AND estado = 0");
        return $stmt->execute([$id_compra]);
    }

    public function cancelar($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE compras SET estado = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
