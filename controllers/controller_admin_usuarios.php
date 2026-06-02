<?php

class AdminUsuariosController {

    public function list() {
        $model    = new AdminUsuariosModel();
        $usuarios = $model->getAll();
        echo json_encode(['status' => 'ok', 'data' => $usuarios]);
    }

    public function get($id) {
        $model   = new AdminUsuariosModel();
        $usuario = $model->getById((int) $id);
        if (!$usuario) {
            echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado.']);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $usuario]);
    }

    public function add($data) {
        $model = new AdminUsuariosModel();

        $id        = isset($data['id']) ? (int) $data['id'] : 0;
        $nombre    = trim($data['nombre'] ?? '');
        $apellidos = trim($data['apellidos'] ?? '');
        $email     = trim($data['email'] ?? '');
        $id_rol      = isset($data['id_rol'])      && $data['id_rol']      !== '' ? (int) $data['id_rol']      : null;
        $id_sucursal = isset($data['id_sucursal']) && $data['id_sucursal'] !== '' ? (int) $data['id_sucursal'] : null;
        $telefono    = trim($data['telefono'] ?? '');
        $estado      = isset($data['estado']) ? (int) $data['estado'] : 0;
        $id_alta     = $_SESSION['usuario_id'] ?? null;

        if (!$nombre || !$apellidos || !$email) {
            echo json_encode(['status' => 'error', 'message' => 'Nombre, apellidos y email son requeridos.']);
            return;
        }

        if (!$id_sucursal) {
            echo json_encode(['status' => 'error', 'message' => 'Selecciona una sucursal para el usuario.']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'El email no tiene un formato válido.']);
            return;
        }

        if ($model->emailExiste($email, $id ?: null)) {
            echo json_encode(['status' => 'error', 'message' => 'El email ya está registrado.']);
            return;
        }

        $imagen = null;
        if (!empty($_FILES['imagen']['name'])) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $mime    = mime_content_type($_FILES['imagen']['tmp_name']);
            if (!in_array($mime, $allowed)) {
                echo json_encode(['status' => 'error', 'message' => 'Solo se permiten imágenes JPG, PNG o WEBP.']);
                return;
            }
            if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'La imagen no debe superar 2 MB.']);
                return;
            }
            $ext      = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('usr_', true) . '.' . strtolower($ext);
            $destino  = __DIR__ . '/../views/uploads/admin_usuarios/' . $filename;
            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                echo json_encode(['status' => 'error', 'message' => 'Error al guardar la imagen.']);
                return;
            }
            $imagen = $filename;
        }

        $fields = compact('nombre', 'apellidos', 'email', 'id_rol', 'id_sucursal', 'telefono', 'estado');

        if ($id) {
            $ok  = $model->update($id, $fields + ['password' => $data['password'] ?? '', 'imagen' => $imagen]);
            $msg = $ok ? 'Usuario actualizado correctamente.' : 'Error al actualizar.';
        } else {
            if (empty($data['password'])) {
                echo json_encode(['status' => 'error', 'message' => 'La contraseña es requerida.']);
                return;
            }
            $ok  = $model->insert($fields + ['password' => $data['password'], 'imagen' => $imagen, 'id_alta' => $id_alta]);
            $msg = $ok ? 'Usuario creado correctamente.' : 'Error al crear.';
        }

        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $msg]);
    }

    public function delete($id) {
        $model = new AdminUsuariosModel();
        $ok    = $model->delete((int) $id);
        echo json_encode(['status' => $ok ? 'ok' : 'error', 'message' => $ok ? 'Usuario eliminado.' : 'Error al eliminar.']);
    }

}
