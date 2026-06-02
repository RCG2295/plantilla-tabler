<?php

class AdminSucursalesController
{
    public function list(): void
    {
        $model = new AdminSucursalesModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getAll()]);
    }

    public function get(int $id): void
    {
        $model = new AdminSucursalesModel();
        $row   = $model->getById($id);
        if (!$row) {
            echo json_encode(['status' => 'error', 'message' => 'Sucursal no encontrada.']);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $row]);
    }

    public function save(array $data): void
    {
        $model     = new AdminSucursalesModel();
        $id        = isset($data['id']) ? (int) $data['id'] : 0;
        $nombre    = trim($data['nombre']    ?? '');
        $direccion = trim($data['direccion'] ?? '');
        $telefono  = trim($data['telefono']  ?? '');
        $estado    = isset($data['estado']) ? (int) $data['estado'] : 0;
        $id_alta   = $_SESSION['usuario_id'] ?? null;

        if (!$nombre) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre de la sucursal es requerido.']);
            return;
        }

        if ($id) {
            $ok  = $model->update($id, compact('nombre', 'direccion', 'telefono', 'estado'));
            $msg = $ok ? 'Sucursal actualizada correctamente.' : 'Error al actualizar.';
        } else {
            $new_id = $model->insert(compact('nombre', 'direccion', 'telefono', 'estado', 'id_alta'));
            $ok     = $new_id !== false;
            $msg    = $ok ? 'Sucursal creada correctamente.' : 'Error al crear.';
            if ($ok) {
                $model->initStockParaSucursal($new_id, $id_alta);
            }
        }

        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $msg]);
    }

    public function delete(int $id): void
    {
        $model = new AdminSucursalesModel();
        $ok    = $model->delete($id);
        echo json_encode([
            'status'  => $ok ? 'ok'    : 'error',
            'message' => $ok ? 'Sucursal eliminada.' : 'Error al eliminar.',
        ]);
    }

    public function switchSucursal(int $id): void
    {
        if (empty($_SESSION['es_superadmin'])) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']);
            return;
        }
        $model    = new AdminSucursalesModel();
        $sucursal = $model->getById($id);
        if (!$sucursal) {
            echo json_encode(['status' => 'error', 'message' => 'Sucursal no encontrada.']);
            return;
        }
        $_SESSION['id_sucursal']     = (int) $sucursal['id'];
        $_SESSION['sucursal_nombre'] = $sucursal['nombre'];
        echo json_encode(['status' => 'ok', 'nombre' => $sucursal['nombre']]);
    }
}
