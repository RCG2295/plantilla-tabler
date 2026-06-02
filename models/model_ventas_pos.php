<?php

class VentasPosModel
{
    public function getProductos(): array
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("
            SELECT p.id, p.nombre, p.codigo,
                   p.precio_venta, p.precio_venta_unidad,
                   p.se_fracciona, p.cantidad_presentacion,
                   COALESCE(s.stock_actual, 0) AS stock_actual,
                   p.stock_minimo,
                   u.abreviatura AS unidad,
                   c.id AS id_categoria, c.nombre AS categoria,
                   (SELECT nombre_archivo FROM inventario_producto_fotos
                    WHERE id_producto = p.id AND es_principal = 1 AND estado != 2
                    LIMIT 1) AS foto
            FROM inventario_productos p
            LEFT JOIN inventario_categorias c ON c.id = p.id_categoria
            LEFT JOIN inventario_unidades_medida u ON u.id = p.id_unidad_medida
            LEFT JOIN inventario_stock s ON s.id_producto = p.id AND s.id_sucursal = ?
            WHERE p.estado = 0
              AND (p.precio_venta > 0 OR (p.se_fracciona = 1 AND p.precio_venta_unidad > 0))
            ORDER BY c.nombre, p.nombre
        ");
        $stmt->execute([$id_sucursal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategorias(): array
    {
        $db   = Connection::connect();
        $stmt = $db->query("
            SELECT DISTINCT c.id, c.nombre
            FROM inventario_categorias c
            INNER JOIN inventario_productos p ON p.id_categoria = c.id
            WHERE p.estado = 0 AND c.estado = 0
              AND (p.precio_venta > 0 OR (p.se_fracciona = 1 AND p.precio_venta_unidad > 0))
            ORDER BY c.nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTipoCambio()
    {
        $db   = Connection::connect();
        $stmt = $db->query("SELECT id, valor FROM ventas_tipo_cambio WHERE estado != 2 ORDER BY fecha_alta DESC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getStockActual(int $id_producto): float
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("SELECT COALESCE(stock_actual, 0) AS stock FROM inventario_stock WHERE id_producto = ? AND id_sucursal = ?");
        $stmt->execute([$id_producto, $id_sucursal]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float) $row['stock'] : 0;
    }

    public function registrarVenta(array $venta, array $items, array $pagos): array
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $id_usuario  = $_SESSION['usuario_id']  ?? null;

        $db->beginTransaction();
        try {
            // Validate stock for all items before any write
            foreach ($items as $item) {
                $stock = $this->getStockActual((int) $item['id_producto']);
                if ($stock < (float) $item['cantidad']) {
                    $db->rollBack();
                    return ['ok' => false, 'message' => 'Stock insuficiente para ' . $item['nombre_producto']];
                }
            }

            // Insert venta header
            $stmt = $db->prepare("
                INSERT INTO ventas (id_turno, id_usuario, id_sucursal, subtotal, total, tipo_cambio, estado, id_alta, fecha_alta)
                VALUES (?, ?, ?, ?, ?, ?, 0, ?, NOW())
            ");
            $stmt->execute([
                $venta['id_turno'],
                $id_usuario,
                $id_sucursal,
                $venta['subtotal'],
                $venta['total'],
                $venta['tipo_cambio'] ?? 0,
                $id_usuario,
            ]);
            $id_venta = (int) $db->lastInsertId();

            // Set folio
            $folio = str_pad($id_venta, 6, '0', STR_PAD_LEFT);
            $db->prepare("UPDATE ventas SET folio = ? WHERE id = ?")->execute([$folio, $id_venta]);

            // Insert items + update stock + movement
            $id_motivo = $this->getMotivoPorNombre('Venta', 'salida');
            foreach ($items as $item) {
                // Item row
                $db->prepare("
                    INSERT INTO ventas_items (id_venta, id_producto, tipo_precio, cantidad, precio_unitario, subtotal, estado, id_alta, fecha_alta)
                    VALUES (?, ?, ?, ?, ?, ?, 0, ?, NOW())
                ")->execute([
                    $id_venta,
                    $item['id_producto'],
                    $item['tipo_precio'],
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $item['subtotal'],
                    $id_usuario,
                ]);

                // Update stock
                $stock_anterior = $this->getStockActual((int) $item['id_producto']);
                $stock_nuevo    = $stock_anterior - (float) $item['cantidad'];
                $db->prepare("
                    INSERT INTO inventario_stock (id_producto, id_sucursal, stock_actual, estado, id_alta, fecha_alta)
                    VALUES (?, ?, ?, 0, ?, NOW())
                    ON DUPLICATE KEY UPDATE stock_actual = ?
                ")->execute([$item['id_producto'], $id_sucursal, $stock_nuevo, $id_usuario, $stock_nuevo]);

                // Movement
                $db->prepare("
                    INSERT INTO inventario_movimientos
                        (id_producto, id_sucursal, tipo, cantidad, stock_anterior, stock_nuevo, id_motivo, notas, estado, id_alta, fecha_alta)
                    VALUES (?, ?, 'salida', ?, ?, ?, ?, ?, 0, ?, NOW())
                ")->execute([
                    $item['id_producto'],
                    $id_sucursal,
                    $item['cantidad'],
                    $stock_anterior,
                    $stock_nuevo,
                    $id_motivo,
                    'Venta folio ' . $folio,
                    $id_usuario,
                ]);
            }

            // Insert payment methods
            foreach ($pagos as $pago) {
                if ((float) $pago['monto'] <= 0) continue;
                $db->prepare("
                    INSERT INTO ventas_formas_pago (id_venta, forma_pago, monto, monto_pesos, referencia, estado, id_alta, fecha_alta)
                    VALUES (?, ?, ?, ?, ?, 0, ?, NOW())
                ")->execute([
                    $id_venta,
                    $pago['forma_pago'],
                    $pago['monto'],
                    $pago['monto_pesos'],
                    $pago['referencia'] ?? null,
                    $id_usuario,
                ]);
            }

            $db->commit();
            return ['ok' => true, 'id_venta' => $id_venta, 'folio' => $folio];

        } catch (\Exception $e) {
            $db->rollBack();
            return ['ok' => false, 'message' => 'Error al registrar la venta: ' . $e->getMessage()];
        }
    }

    public function cancelarVenta(int $id_venta): array
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $id_usuario  = $_SESSION['usuario_id']  ?? null;

        $stmt = $db->prepare("SELECT * FROM ventas WHERE id = ? AND estado = 0");
        $stmt->execute([$id_venta]);
        $venta = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$venta) return ['ok' => false, 'message' => 'Venta no encontrada o ya cancelada.'];

        $stmt = $db->prepare("SELECT * FROM ventas_items WHERE id_venta = ? AND estado != 2");
        $stmt->execute([$id_venta]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $db->beginTransaction();
        try {
            $db->prepare("UPDATE ventas SET estado = 1 WHERE id = ?")->execute([$id_venta]);

            $id_motivo = $this->getMotivoPorNombre('Cancelación de venta', 'entrada');
            foreach ($items as $item) {
                $stock_anterior = $this->getStockActual((int) $item['id_producto']);
                $stock_nuevo    = $stock_anterior + (float) $item['cantidad'];

                $db->prepare("
                    INSERT INTO inventario_stock (id_producto, id_sucursal, stock_actual, estado, id_alta, fecha_alta)
                    VALUES (?, ?, ?, 0, ?, NOW())
                    ON DUPLICATE KEY UPDATE stock_actual = ?
                ")->execute([$item['id_producto'], $id_sucursal, $stock_nuevo, $id_usuario, $stock_nuevo]);

                $db->prepare("
                    INSERT INTO inventario_movimientos
                        (id_producto, id_sucursal, tipo, cantidad, stock_anterior, stock_nuevo, id_motivo, notas, estado, id_alta, fecha_alta)
                    VALUES (?, ?, 'entrada', ?, ?, ?, ?, ?, 0, ?, NOW())
                ")->execute([
                    $item['id_producto'],
                    $id_sucursal,
                    $item['cantidad'],
                    $stock_anterior,
                    $stock_nuevo,
                    $id_motivo,
                    'Cancelación venta folio ' . $venta['folio'],
                    $id_usuario,
                ]);
            }

            $db->commit();
            return ['ok' => true];
        } catch (\Exception $e) {
            $db->rollBack();
            return ['ok' => false, 'message' => 'Error al cancelar: ' . $e->getMessage()];
        }
    }

    public function getVentaParaTicket(int $id): ?array
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT v.*, u.nombre AS cajero, u.apellidos AS cajero_apellidos,
                   s.nombre AS sucursal_nombre
            FROM ventas v
            LEFT JOIN admin_usuarios u ON u.id = v.id_usuario
            LEFT JOIN admin_sucursales s ON s.id = v.id_sucursal
            WHERE v.id = ?
        ");
        $stmt->execute([$id]);
        $venta = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$venta) return null;

        $stmt = $db->prepare("
            SELECT vi.*, p.nombre AS producto, p.codigo,
                   u.abreviatura AS unidad
            FROM ventas_items vi
            JOIN inventario_productos p ON p.id = vi.id_producto
            LEFT JOIN inventario_unidades_medida u ON u.id = p.id_unidad_medida
            WHERE vi.id_venta = ? AND vi.estado != 2
        ");
        $stmt->execute([$id]);
        $venta['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("SELECT * FROM ventas_formas_pago WHERE id_venta = ? AND estado != 2");
        $stmt->execute([$id]);
        $venta['pagos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $venta;
    }

    public function getVentasByTurno(int $id_turno): array
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT v.id, v.folio, v.total, v.estado, v.fecha_alta,
                   GROUP_CONCAT(DISTINCT fp.forma_pago ORDER BY fp.forma_pago SEPARATOR ', ') AS formas_pago
            FROM ventas v
            LEFT JOIN ventas_formas_pago fp ON fp.id_venta = v.id AND fp.estado != 2
            WHERE v.id_turno = ? AND v.estado != 2
            GROUP BY v.id
            ORDER BY v.fecha_alta DESC
        ");
        $stmt->execute([$id_turno]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getMotivoPorNombre(string $nombre, string $tipo = 'ambos'): ?int
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("SELECT id FROM inventario_motivos_movimiento WHERE nombre = ? AND estado = 0 LIMIT 1");
        $stmt->execute([$nombre]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return (int) $row['id'];

        $db->prepare("INSERT INTO inventario_motivos_movimiento (nombre, tipo, estado, id_alta, fecha_alta) VALUES (?, ?, 0, 1, NOW())")->execute([$nombre, $tipo]);
        return (int) $db->lastInsertId();
    }
}
