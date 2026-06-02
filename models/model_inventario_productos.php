<?php

class InventarioProductosModel
{
    public function getAll()
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("
            SELECT p.id, p.codigo, p.nombre,
                   COALESCE(s.stock_actual, 0) AS stock_actual,
                   p.stock_minimo, p.stock_maximo,
                   p.precio_costo, p.precio_venta, p.precio_venta_unidad, p.estado,
                   p.se_fracciona, p.cantidad_presentacion,
                   c.nombre AS categoria,
                   u.nombre AS unidad, u.abreviatura,
                   (SELECT nombre_archivo FROM inventario_producto_fotos
                    WHERE id_producto = p.id AND es_principal = 1 AND estado != 2
                    LIMIT 1) AS foto_principal
            FROM inventario_productos p
            LEFT JOIN inventario_categorias c ON c.id = p.id_categoria
            LEFT JOIN inventario_unidades_medida u ON u.id = p.id_unidad_medida
            LEFT JOIN inventario_stock s ON s.id_producto = p.id AND s.id_sucursal = ?
            WHERE p.estado != 2
            ORDER BY p.nombre
        ");
        $stmt->execute([$id_sucursal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("
            SELECT p.id, p.codigo, p.nombre, p.descripcion,
                   p.id_categoria, p.id_unidad_medida,
                   COALESCE(s.stock_actual, 0) AS stock_actual,
                   p.stock_minimo, p.stock_maximo,
                   p.precio_costo, p.precio_venta, p.precio_venta_unidad, p.estado,
                   p.se_fracciona, p.cantidad_presentacion,
                   c.nombre AS categoria, c.id_padre AS id_categoria_padre,
                   u.nombre AS unidad, u.abreviatura
            FROM inventario_productos p
            LEFT JOIN inventario_categorias c ON c.id = p.id_categoria
            LEFT JOIN inventario_unidades_medida u ON u.id = p.id_unidad_medida
            LEFT JOIN inventario_stock s ON s.id_producto = p.id AND s.id_sucursal = ?
            WHERE p.id = ? AND p.estado != 2
        ");
        $stmt->execute([$id_sucursal, $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function codigoExiste($codigo, $exclude_id = null)
    {
        $db = Connection::connect();
        if ($exclude_id) {
            $stmt = $db->prepare("SELECT id FROM inventario_productos WHERE codigo = ? AND id != ? AND estado != 2");
            $stmt->execute([$codigo, $exclude_id]);
        } else {
            $stmt = $db->prepare("SELECT id FROM inventario_productos WHERE codigo = ? AND estado != 2");
            $stmt->execute([$codigo]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function insert($data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO inventario_productos
                (codigo, nombre, descripcion, id_categoria, id_unidad_medida,
                 stock_minimo, stock_maximo, precio_costo, precio_venta, precio_venta_unidad,
                 se_fracciona, cantidad_presentacion, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $ok = $stmt->execute([
            $data['codigo'],
            $data['nombre'],
            $data['descripcion'] ?: null,
            $data['id_categoria'] ?: null,
            $data['id_unidad_medida'] ?: null,
            $data['stock_minimo']          ?? 0,
            $data['stock_maximo']          ?? 0,
            $data['precio_costo']          ?? 0,
            $data['precio_venta']          ?? 0,
            $data['precio_venta_unidad'] ?: null,
            $data['se_fracciona']          ?? 0,
            $data['cantidad_presentacion'] ?? 1,
            $data['estado'],
            $data['id_alta'] ?? null,
        ]);
        if (!$ok) return false;

        $new_id      = (int) $db->lastInsertId();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;

        // Init stock = 0 for every active branch; stock for this branch set later by controller
        $db->prepare("
            INSERT IGNORE INTO inventario_stock
                (id_producto, id_sucursal, stock_actual, estado, id_alta, fecha_alta)
            SELECT ?, id, 0.00, 0, ?, NOW()
            FROM admin_sucursales WHERE estado != 2
        ")->execute([$new_id, $data['id_alta'] ?? null]);

        return $new_id;
    }

    public function update($id, $data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            UPDATE inventario_productos
            SET codigo = ?, nombre = ?, descripcion = ?, id_categoria = ?, id_unidad_medida = ?,
                stock_minimo = ?, stock_maximo = ?, precio_costo = ?, precio_venta = ?, precio_venta_unidad = ?,
                se_fracciona = ?, cantidad_presentacion = ?, estado = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['codigo'],
            $data['nombre'],
            $data['descripcion'] ?: null,
            $data['id_categoria'] ?: null,
            $data['id_unidad_medida'] ?: null,
            $data['stock_minimo']            ?? 0,
            $data['stock_maximo']            ?? 0,
            $data['precio_costo']            ?? 0,
            $data['precio_venta']            ?? 0,
            $data['precio_venta_unidad'] ?: null,
            $data['se_fracciona']            ?? 0,
            $data['cantidad_presentacion']   ?? 1,
            $data['estado'],
            $id,
        ]);
    }

    public function updateStock($id, $stock_nuevo)
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $id_alta     = $_SESSION['usuario_id']  ?? null;
        $stmt        = $db->prepare("
            INSERT INTO inventario_stock (id_producto, id_sucursal, stock_actual, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, 0, ?, NOW())
            ON DUPLICATE KEY UPDATE stock_actual = ?
        ");
        return $stmt->execute([$id, $id_sucursal, $stock_nuevo, $id_alta, $stock_nuevo]);
    }

    public function delete($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE inventario_productos SET estado = 2 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ── Fotos ────────────────────────────────────────────────────────────────

    public function getFotos($id_producto)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT id, nombre_archivo, es_principal, orden
            FROM inventario_producto_fotos
            WHERE id_producto = ? AND estado != 2
            ORDER BY es_principal DESC, orden, id
        ");
        $stmt->execute([$id_producto]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertFoto($data)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            INSERT INTO inventario_producto_fotos (id_producto, nombre_archivo, es_principal, orden, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, 0, ?, NOW())
        ");
        $ok = $stmt->execute([
            $data['id_producto'],
            $data['nombre_archivo'],
            $data['es_principal'] ? 1 : 0,
            $data['orden'] ?? 0,
            $data['id_alta'] ?? null,
        ]);
        return $ok ? $db->lastInsertId() : false;
    }

    public function setFotoPrincipal($id_producto, $id_foto)
    {
        $db = Connection::connect();
        $db->prepare("UPDATE inventario_producto_fotos SET es_principal = 0 WHERE id_producto = ?")->execute([$id_producto]);
        $db->prepare("UPDATE inventario_producto_fotos SET es_principal = 1 WHERE id = ?")->execute([$id_foto]);
        return true;
    }

    public function deleteFoto($id)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("SELECT nombre_archivo FROM inventario_producto_fotos WHERE id = ?");
        $stmt->execute([$id]);
        $foto = $stmt->fetch(PDO::FETCH_ASSOC);

        $db->prepare("UPDATE inventario_producto_fotos SET estado = 2 WHERE id = ?")->execute([$id]);
        return $foto;
    }

    // ── Movimientos ──────────────────────────────────────────────────────────

    public function getMovimientos($id_producto, $limit = 50)
    {
        $db          = Connection::connect();
        $id_sucursal = $_SESSION['id_sucursal'] ?? null;
        $where       = ['m.id_producto = ?', 'm.estado != 2'];
        $params      = [(int) $id_producto];

        if ($id_sucursal) {
            $where[]  = '(m.id_sucursal = ? OR m.id_sucursal IS NULL)';
            $params[] = $id_sucursal;
        }

        $stmt = $db->prepare("
            SELECT m.id, m.tipo, m.cantidad, m.stock_anterior, m.stock_nuevo,
                   m.notas, m.fecha_alta,
                   mo.nombre AS motivo,
                   u.nombre AS usuario
            FROM inventario_movimientos m
            LEFT JOIN inventario_motivos_movimiento mo ON mo.id = m.id_motivo
            LEFT JOIN admin_usuarios u ON u.id = m.id_alta
            WHERE " . implode(' AND ', $where) . "
            ORDER BY m.fecha_alta DESC
            LIMIT " . (int) $limit
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMotivoPorNombre($nombre)
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("SELECT id FROM inventario_motivos_movimiento WHERE nombre = ? AND estado = 0 LIMIT 1");
        $stmt->execute([$nombre]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['id'] : null;
    }

    public function registrarMovimiento($data)
    {
        $db          = Connection::connect();
        $id_sucursal = $data['id_sucursal'] ?? $_SESSION['id_sucursal'] ?? null;
        $stmt        = $db->prepare("
            INSERT INTO inventario_movimientos
                (id_producto, id_sucursal, tipo, cantidad, stock_anterior, stock_nuevo, id_motivo, notas, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, NOW())
        ");
        return $stmt->execute([
            $data['id_producto'],
            $id_sucursal,
            $data['tipo'],
            $data['cantidad'],
            $data['stock_anterior'],
            $data['stock_nuevo'],
            $data['id_motivo'] ?: null,
            $data['notas'] ?: null,
            $data['id_alta'] ?? null,
        ]);
    }
}
