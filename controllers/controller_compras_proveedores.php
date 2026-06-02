<?php

class ComprasProveedoresController
{
    public function list()
    {
        $model = new ComprasProveedoresModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getAll()]);
    }

    public function get($id)
    {
        $model = new ComprasProveedoresModel();
        $row   = $model->getById((int) $id);
        if (!$row) {
            echo json_encode(['status' => 'error', 'message' => 'Proveedor no encontrado.']);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $row]);
    }

    public function forSelect()
    {
        $model = new ComprasProveedoresModel();
        echo json_encode(['status' => 'ok', 'data' => $model->getForSelect()]);
    }

    public function save($data)
    {
        $model        = new ComprasProveedoresModel();
        $id           = isset($data['id']) ? (int) $data['id'] : 0;
        $nombre       = trim($data['nombre'] ?? '');
        $razon_social = trim($data['razon_social'] ?? '');
        $rfc          = strtoupper(trim($data['rfc'] ?? ''));
        $telefono     = trim($data['telefono'] ?? '');
        $email        = trim($data['email'] ?? '');
        $direccion    = trim($data['direccion'] ?? '');
        $notas        = trim($data['notas'] ?? '');
        $estado       = isset($data['estado']) ? (int) $data['estado'] : 0;

        if (!$nombre) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre es requerido.']);
            return;
        }
        if ($model->nombreExiste($nombre, $id ?: null)) {
            echo json_encode(['status' => 'error', 'message' => 'Ya existe un proveedor con ese nombre.']);
            return;
        }

        $fields = compact('nombre', 'razon_social', 'rfc', 'telefono', 'email', 'direccion', 'notas', 'estado');

        if ($id) {
            $ok  = $model->update($id, $fields);
            $msg = $ok ? 'Proveedor actualizado correctamente.' : 'Error al actualizar.';
        } else {
            $ok  = $model->insert($fields + ['id_alta' => $_SESSION['usuario_id'] ?? null]);
            $msg = $ok ? 'Proveedor creado correctamente.' : 'Error al crear.';
        }

        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $msg]);
    }

    public function delete($id)
    {
        $model = new ComprasProveedoresModel();
        $id    = (int) $id;

        if ($model->tieneCompras($id)) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar: el proveedor tiene compras registradas.']);
            return;
        }

        $ok = $model->delete($id);
        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $ok ? 'Proveedor eliminado.' : 'Error al eliminar.']);
    }
}
