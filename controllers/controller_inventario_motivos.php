<?php

class InventarioMotivosController
{
    public function list()
    {
        $model = new InventarioMotivosModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getAll()]);
    }

    public function listByTipo($tipo)
    {
        $allowed = ['entrada', 'salida'];
        if (!in_array($tipo, $allowed)) {
            echo json_encode(['status' => 'error', 'message' => 'Tipo no válido.']);
            return;
        }
        $model = new InventarioMotivosModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getByTipo($tipo)]);
    }

    public function get($id)
    {
        $model = new InventarioMotivosModel();
        $row   = $model->getById((int) $id);
        if (!$row) {
            echo json_encode(['status' => 'error', 'message' => 'Motivo no encontrado.']);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $row]);
    }

    public function save($data)
    {
        $model  = new InventarioMotivosModel();
        $id     = isset($data['id']) ? (int) $data['id'] : 0;
        $nombre = trim($data['nombre'] ?? '');
        $tipo   = trim($data['tipo'] ?? '');
        $estado = isset($data['estado']) ? (int) $data['estado'] : 0;

        if (!$nombre) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre es requerido.']);
            return;
        }
        if (!in_array($tipo, ['entrada', 'salida', 'ambos'])) {
            echo json_encode(['status' => 'error', 'message' => 'Tipo de movimiento no válido.']);
            return;
        }
        if ($model->nombreExiste($nombre, $id ?: null)) {
            echo json_encode(['status' => 'error', 'message' => 'Ya existe un motivo con ese nombre.']);
            return;
        }

        $fields = compact('nombre', 'tipo', 'estado');

        if ($id) {
            $ok  = $model->update($id, $fields);
            $msg = $ok ? 'Motivo actualizado correctamente.' : 'Error al actualizar.';
        } else {
            $ok  = $model->insert($fields + ['id_alta' => $_SESSION['usuario_id'] ?? null]);
            $msg = $ok ? 'Motivo creado correctamente.' : 'Error al crear.';
        }

        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $msg]);
    }

    public function delete($id)
    {
        $model = new InventarioMotivosModel();
        $ok    = $model->delete((int) $id);
        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $ok ? 'Motivo eliminado.' : 'Error al eliminar.']);
    }
}
