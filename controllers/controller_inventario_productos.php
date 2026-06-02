<?php

class InventarioProductosController
{
    private $upload_dir;

    public function __construct()
    {
        $this->upload_dir = __DIR__ . '/../views/uploads/inventario_productos/';
    }

    public function list()
    {
        $model = new InventarioProductosModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getAll()]);
    }

    public function get($id)
    {
        $model = new InventarioProductosModel();
        $row   = $model->getById((int) $id);
        if (!$row) {
            echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado.']);
            return;
        }
        $row['fotos'] = $model->getFotos((int) $id);
        echo json_encode(['status' => 'ok', 'data' => $row]);
    }

    public function save($data, $files)
    {
        $model          = new InventarioProductosModel();
        $id             = isset($data['id']) ? (int) $data['id'] : 0;
        $codigo         = strtoupper(trim($data['codigo'] ?? ''));
        $nombre         = trim($data['nombre'] ?? '');
        $descripcion    = trim($data['descripcion'] ?? '');
        $id_categoria   = isset($data['id_categoria']) && (int) $data['id_categoria'] > 0 ? (int) $data['id_categoria'] : null;
        $id_unidad      = isset($data['id_unidad_medida']) && (int) $data['id_unidad_medida'] > 0 ? (int) $data['id_unidad_medida'] : null;
        $stock_actual          = $id ? null : (float) ($data['stock_actual'] ?? 0);
        $stock_minimo          = (float) ($data['stock_minimo'] ?? 0);
        $stock_maximo          = (float) ($data['stock_maximo'] ?? 0);
        $precio_costo          = (float) ($data['precio_costo'] ?? 0);
        $precio_venta          = (float) ($data['precio_venta'] ?? 0);
        $estado                = isset($data['estado']) ? (int) $data['estado'] : 0;
        $se_fracciona          = !empty($data['se_fracciona']) ? 1 : 0;
        $cantidad_presentacion = max(0.01, (float) ($data['cantidad_presentacion'] ?? 1));
        $precio_venta_unidad   = $se_fracciona && isset($data['precio_venta_unidad']) && $data['precio_venta_unidad'] !== ''
                                 ? (float) $data['precio_venta_unidad'] : null;

        if (!$codigo) {
            echo json_encode(['status' => 'error', 'message' => 'El código es requerido.']);
            return;
        }
        if (!$nombre) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre es requerido.']);
            return;
        }
        if ($model->codigoExiste($codigo, $id ?: null)) {
            echo json_encode(['status' => 'error', 'message' => 'Ya existe un producto con ese código.']);
            return;
        }

        $fields = [
            'codigo'               => $codigo,
            'nombre'               => $nombre,
            'descripcion'          => $descripcion,
            'id_categoria'         => $id_categoria,
            'id_unidad_medida'     => $id_unidad,
            'stock_minimo'         => $stock_minimo,
            'stock_maximo'         => $stock_maximo,
            'precio_costo'         => $precio_costo,
            'precio_venta'         => $precio_venta,
            'se_fracciona'         => $se_fracciona,
            'cantidad_presentacion'=> $cantidad_presentacion,
            'precio_venta_unidad'  => $precio_venta_unidad,
            'estado'               => $estado,
        ];

        if ($id) {
            $ok  = $model->update($id, $fields);
            $msg = $ok ? 'Producto actualizado correctamente.' : 'Error al actualizar.';
            $new_id = $id;
        } else {
            $fields['stock_actual'] = $stock_actual;
            $fields['id_alta']      = $_SESSION['usuario_id'] ?? null;

            // Register initial stock movement if stock > 0
            if ($stock_actual > 0) {
                $fields['stock_actual'] = $stock_actual;
            }

            $new_id = $model->insert($fields);
            $ok     = $new_id !== false;
            $msg    = $ok ? 'Producto creado correctamente.' : 'Error al crear.';

            if ($ok && $stock_actual > 0) {
                $model->registrarMovimiento([
                    'id_producto'   => $new_id,
                    'tipo'          => 'entrada',
                    'cantidad'      => $stock_actual,
                    'stock_anterior'=> 0,
                    'stock_nuevo'   => $stock_actual,
                    'id_motivo'     => $model->getMotivoPorNombre('Alta de producto'),
                    'notas'         => 'Stock inicial al crear el producto.',
                    'id_alta'       => $_SESSION['usuario_id'] ?? null,
                ]);
            }
        }

        if ($ok && !empty($files['fotos']['name'][0])) {
            $this->procesarFotos($new_id, $files['fotos'], $model);
        }

        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $msg, 'id' => $new_id]);
    }

    private function procesarFotos($id_producto, $files, $model)
    {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $max_size      = 2 * 1024 * 1024;
        $fotos_actuales = count($model->getFotos($id_producto));

        foreach ($files['name'] as $i => $name) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK || !$name) continue;
            if ($files['size'][$i] > $max_size) continue;

            $mime = mime_content_type($files['tmp_name'][$i]);
            if (!in_array($mime, $allowed_types)) continue;

            $ext      = pathinfo($name, PATHINFO_EXTENSION);
            $filename = 'prod_' . uniqid() . '.' . strtolower($ext);

            if (move_uploaded_file($files['tmp_name'][$i], $this->upload_dir . $filename)) {
                $es_principal = ($fotos_actuales === 0 && $i === 0) ? 1 : 0;
                $model->insertFoto([
                    'id_producto'    => $id_producto,
                    'nombre_archivo' => $filename,
                    'es_principal'   => $es_principal,
                    'orden'          => $i,
                    'id_alta'        => $_SESSION['usuario_id'] ?? null,
                ]);
                $fotos_actuales++;
            }
        }
    }

    public function setFotoPrincipal($id_producto, $id_foto)
    {
        $model = new InventarioProductosModel();
        $model->setFotoPrincipal((int) $id_producto, (int) $id_foto);
        echo json_encode(['status' => 'ok', 'message' => 'Foto principal actualizada.']);
    }

    public function deleteFoto($id)
    {
        $model = new InventarioProductosModel();
        $foto  = $model->deleteFoto((int) $id);

        if ($foto && file_exists($this->upload_dir . $foto['nombre_archivo'])) {
            unlink($this->upload_dir . $foto['nombre_archivo']);
        }

        echo json_encode(['status' => 'ok', 'message' => 'Foto eliminada.']);
    }

    public function registrarMovimiento($data)
    {
        $model    = new InventarioProductosModel();
        $id       = (int) ($data['id_producto'] ?? 0);
        $tipo     = $data['tipo'] ?? '';
        $cantidad = (float) ($data['cantidad'] ?? 0);
        $id_motivo= isset($data['id_motivo']) && (int) $data['id_motivo'] > 0 ? (int) $data['id_motivo'] : null;
        $notas    = trim($data['notas'] ?? '');

        if (!$id || !in_array($tipo, ['entrada', 'salida']) || $cantidad <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Datos del movimiento no válidos.']);
            return;
        }

        $producto = $model->getById($id);
        if (!$producto) {
            echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado.']);
            return;
        }

        $stock_anterior = (float) $producto['stock_actual']; // ya viene de inventario_stock via COALESCE

        if ($tipo === 'salida' && $cantidad > $stock_anterior) {
            echo json_encode(['status' => 'error', 'message' => 'Stock insuficiente. Stock actual: ' . $stock_anterior . ' ' . $producto['abreviatura'] . '.']);
            return;
        }

        $stock_nuevo = $tipo === 'entrada'
            ? $stock_anterior + $cantidad
            : $stock_anterior - $cantidad;

        $model->registrarMovimiento([
            'id_producto'    => $id,
            'tipo'           => $tipo,
            'cantidad'       => $cantidad,
            'stock_anterior' => $stock_anterior,
            'stock_nuevo'    => $stock_nuevo,
            'id_motivo'      => $id_motivo,
            'notas'          => $notas,
            'id_alta'        => $_SESSION['usuario_id'] ?? null,
        ]);

        $model->updateStock($id, $stock_nuevo);

        echo json_encode([
            'status'      => 'ok',
            'message'     => 'Movimiento registrado correctamente.',
            'stock_nuevo' => $stock_nuevo,
        ]);
    }

    public function getMovimientos($id_producto)
    {
        $model = new InventarioProductosModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getMovimientos((int) $id_producto)]);
    }

    public function delete($id)
    {
        $model = new InventarioProductosModel();
        $ok    = $model->delete((int) $id);
        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $ok ? 'Producto eliminado.' : 'Error al eliminar.']);
    }
}
