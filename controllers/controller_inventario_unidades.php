<?php

class InventarioUnidadesController
{
    public function list()
    {
        $model = new InventarioUnidadesModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getAll()]);
    }

    public function get($id)
    {
        $model = new InventarioUnidadesModel();
        $row   = $model->getById((int) $id);
        if (!$row) {
            echo json_encode(['status' => 'error', 'message' => 'Unidad no encontrada.']);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $row]);
    }

    public function save($data)
    {
        $model       = new InventarioUnidadesModel();
        $id          = isset($data['id']) ? (int) $data['id'] : 0;
        $nombre      = trim($data['nombre'] ?? '');
        $abreviatura = trim($data['abreviatura'] ?? '');
        $estado      = isset($data['estado']) ? (int) $data['estado'] : 0;

        if (!$nombre) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre es requerido.']);
            return;
        }
        if (!$abreviatura) {
            echo json_encode(['status' => 'error', 'message' => 'La abreviatura es requerida.']);
            return;
        }
        if ($model->nombreExiste($nombre, $id ?: null)) {
            echo json_encode(['status' => 'error', 'message' => 'Ya existe una unidad con ese nombre.']);
            return;
        }

        $fields = compact('nombre', 'abreviatura', 'estado');

        if ($id) {
            $ok  = $model->update($id, $fields);
            $msg = $ok ? 'Unidad actualizada correctamente.' : 'Error al actualizar.';
        } else {
            $ok  = $model->insert($fields + ['id_alta' => $_SESSION['usuario_id'] ?? null]);
            $msg = $ok ? 'Unidad creada correctamente.' : 'Error al crear.';
        }

        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $msg]);
    }

    public function delete($id)
    {
        $model = new InventarioUnidadesModel();
        $ok    = $model->delete((int) $id);
        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $ok ? 'Unidad eliminada.' : 'Error al eliminar.']);
    }
}
