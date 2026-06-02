<?php

class ComprasComprasController
{
    public function list()
    {
        $model = new ComprasComprasModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getAll()]);
    }

    public function listActivas()
    {
        $model = new ComprasComprasModel();
        $desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
        $hasta = $_GET['hasta'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) $desde = date('Y-m-d', strtotime('-30 days'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta))  $hasta = date('Y-m-d');
        echo json_encode(['status' => 'ok', 'data' => $model->getByEstadoConFechas(0, $desde, $hasta)]);
    }

    public function listCanceladas()
    {
        $model = new ComprasComprasModel();
        $desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
        $hasta = $_GET['hasta'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) $desde = date('Y-m-d', strtotime('-30 days'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta))  $hasta = date('Y-m-d');
        echo json_encode(['status' => 'ok', 'data' => $model->getByEstadoConFechas(1, $desde, $hasta)]);
    }

    public function get($id)
    {
        $model  = new ComprasComprasModel();
        $compra = $model->getById((int) $id);
        if (!$compra) {
            echo json_encode(['status' => 'error', 'message' => 'Compra no encontrada.']);
            return;
        }
        $compra['detalle'] = $model->getDetalle((int) $id);
        echo json_encode(['status' => 'ok', 'data' => $compra]);
    }

    public function save($data)
    {
        $model = new ComprasComprasModel();

        $id_proveedor = isset($data['id_proveedor']) && (int) $data['id_proveedor'] > 0
            ? (int) $data['id_proveedor'] : null;
        $fecha_compra = trim($data['fecha_compra'] ?? date('Y-m-d'));
        $notas        = trim($data['notas'] ?? '');
        $detalle      = json_decode($data['detalle'] ?? '[]', true) ?: [];

        if (empty($detalle)) {
            echo json_encode(['status' => 'error', 'message' => 'Agrega al menos un producto.']);
            return;
        }

        $subtotal  = 0;
        $iva_total = 0;

        foreach ($detalle as $item) {
            $qty           = (float) ($item['cantidad'] ?? 0);
            $price         = (float) ($item['precio_unitario'] ?? 0);
            $iva           = (float) ($item['iva'] ?? 8);
            $line_subtotal = $qty * $price;
            $line_iva      = $line_subtotal * ($iva / 100);
            $subtotal      += $line_subtotal;
            $iva_total     += $line_iva;
        }
        $total = $subtotal + $iva_total;

        $folio         = $model->generarFolio();
        $id_motivo     = $model->getMotivoPorNombre('Compra');
        $id_alta       = $_SESSION['usuario_id'] ?? null;
        $id_cat_compra = $model->getEgresosCategoriaPorNombre('Compras de inventario');

        $archivo = null;
        if (!empty($_FILES['archivo']['name'])) {
            $allowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
            $mime    = mime_content_type($_FILES['archivo']['tmp_name']);
            if (!in_array($mime, $allowed)) {
                echo json_encode(['status' => 'error', 'message' => 'Solo se permiten archivos PDF, JPG, PNG o WEBP.']);
                return;
            }
            if ($_FILES['archivo']['size'] > 5 * 1024 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'El archivo no debe superar 5 MB.']);
                return;
            }
            $ext     = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
            $archivo = 'cmp_' . uniqid('', true) . '.' . $ext;
            $destino = __DIR__ . '/../views/uploads/compras_compras/' . $archivo;
            if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
                echo json_encode(['status' => 'error', 'message' => 'Error al guardar el archivo adjunto.']);
                return;
            }
        }

        $db = Connection::connect();
        $db->beginTransaction();

        try {
            $id_compra = $model->insertEncabezado([
                'folio'        => $folio,
                'fecha_compra' => $fecha_compra,
                'id_proveedor' => $id_proveedor,
                'subtotal'     => $subtotal,
                'iva_total'    => $iva_total,
                'total'        => $total,
                'notas'        => $notas,
                'archivo'      => $archivo,
                'id_alta'      => $id_alta,
                'id_sucursal'  => $_SESSION['id_sucursal'] ?? null,
            ]);

            foreach ($detalle as $item) {
                $id_producto   = (int) ($item['id_producto'] ?? 0);
                $cantidad      = (float) ($item['cantidad'] ?? 0);
                $cantidad_base = (float) ($item['cantidad_base'] ?? $cantidad);
                $precio        = (float) ($item['precio_unitario'] ?? 0);
                $iva           = (float) ($item['iva'] ?? 8);
                $line_subtotal = $cantidad * $precio;
                $line_iva      = $line_subtotal * ($iva / 100);
                $line_total    = $line_subtotal + $line_iva;

                $model->insertDetalle([
                    'id_compra'       => $id_compra,
                    'id_producto'     => $id_producto,
                    'cantidad'        => $cantidad,
                    'cantidad_base'   => $cantidad_base,
                    'precio_unitario' => $precio,
                    'iva'             => $iva,
                    'subtotal'        => $line_subtotal,
                    'iva_monto'       => $line_iva,
                    'total_linea'     => $line_total,
                    'id_alta'         => $id_alta,
                ]);

                $prod           = $model->getStockProducto($id_producto);
                $stock_anterior = (float) $prod['stock_actual'];
                $stock_nuevo    = $stock_anterior + $cantidad_base;

                $model->updateStock($id_producto, $stock_nuevo);

                $model->registrarMovimiento([
                    'id_producto'    => $id_producto,
                    'tipo'           => 'entrada',
                    'cantidad'       => $cantidad_base,
                    'stock_anterior' => $stock_anterior,
                    'stock_nuevo'    => $stock_nuevo,
                    'id_motivo'      => $id_motivo,
                    'notas'          => 'Compra ' . $folio,
                    'id_alta'        => $id_alta,
                ]);
            }

            $model->insertEgreso([
                'id_compra'    => $id_compra,
                'id_categoria' => $id_cat_compra,
                'concepto'     => 'Compra ' . $folio,
                'fecha_egreso' => $fecha_compra,
                'monto'        => $total,
                'id_alta'      => $id_alta,
            ]);

            $db->commit();

            echo json_encode([
                'status'  => 'ok',
                'message' => 'Compra ' . $folio . ' registrada correctamente.',
                'id'      => $id_compra,
                'folio'   => $folio,
            ]);

        } catch (Exception $e) {
            $db->rollBack();
            if ($archivo && file_exists(__DIR__ . '/../views/uploads/compras_compras/' . $archivo)) {
                unlink(__DIR__ . '/../views/uploads/compras_compras/' . $archivo);
            }
            echo json_encode(['status' => 'error', 'message' => 'Error al registrar la compra: ' . $e->getMessage()]);
        }
    }

    public function cancelar($id)
    {
        $model  = new ComprasComprasModel();
        $id     = (int) $id;
        $compra = $model->getById($id);

        if (!$compra) {
            echo json_encode(['status' => 'error', 'message' => 'Compra no encontrada.']);
            return;
        }
        if ((int) $compra['estado'] === 1) {
            echo json_encode(['status' => 'error', 'message' => 'La compra ya está cancelada.']);
            return;
        }

        $detalle   = $model->getDetalle($id);
        $id_motivo = $model->getMotivoPorNombre('Cancelación de compra');
        $id_alta   = $_SESSION['usuario_id'] ?? null;

        $db = Connection::connect();
        $db->beginTransaction();

        try {
            foreach ($detalle as $item) {
                $id_producto    = (int) $item['id_producto'];
                $cantidad_base  = (float) $item['cantidad_base'];
                $prod           = $model->getStockProducto($id_producto);
                $stock_anterior = (float) $prod['stock_actual'];
                $stock_nuevo    = max(0, $stock_anterior - $cantidad_base);

                $model->updateStock($id_producto, $stock_nuevo);

                $model->registrarMovimiento([
                    'id_producto'    => $id_producto,
                    'tipo'           => 'salida',
                    'cantidad'       => $cantidad_base,
                    'stock_anterior' => $stock_anterior,
                    'stock_nuevo'    => $stock_nuevo,
                    'id_motivo'      => $id_motivo,
                    'notas'          => 'Cancelación de compra ' . $compra['folio'],
                    'id_alta'        => $id_alta,
                ]);
            }

            $model->cancelarEgreso($id);
            $model->cancelar($id);

            $db->commit();
            echo json_encode(['status' => 'ok', 'message' => 'Compra ' . $compra['folio'] . ' cancelada. Stock revertido.']);

        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Error al cancelar: ' . $e->getMessage()]);
        }
    }
}
