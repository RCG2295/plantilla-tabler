<?php

class CfgPerfilController
{
    public function get(int $id): void
    {
        $model = new CfgPerfilModel();
        $user  = $model->getById($id);
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado.']);
            return;
        }
        echo json_encode(['status' => 'ok', 'data' => $user]);
    }

    public function updateInfo(array $post, array $files, int $id_sesion): void
    {
        $model     = new CfgPerfilModel();
        $nombre    = trim($post['nombre'] ?? '');
        $apellidos = trim($post['apellidos'] ?? '');
        $email     = trim($post['email'] ?? '');
        $telefono  = trim($post['telefono'] ?? '');

        if (!$nombre || !$apellidos || !$email) {
            echo json_encode(['status' => 'error', 'message' => 'Nombre, apellidos y email son requeridos.']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'El email no tiene un formato válido.']);
            return;
        }

        if ($model->emailExiste($email, $id_sesion)) {
            echo json_encode(['status' => 'error', 'message' => 'El email ya está registrado por otro usuario.']);
            return;
        }

        $imagen = null;
        if (!empty($files['imagen']['name'])) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $mime    = mime_content_type($files['imagen']['tmp_name']);
            if (!in_array($mime, $allowed)) {
                echo json_encode(['status' => 'error', 'message' => 'Solo se permiten imágenes JPG, PNG o WEBP.']);
                return;
            }
            if ($files['imagen']['size'] > 2 * 1024 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'La imagen no debe superar 2 MB.']);
                return;
            }
            $ext      = pathinfo($files['imagen']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('usr_', true) . '.' . strtolower($ext);
            $destino  = __DIR__ . '/../views/uploads/admin_usuarios/' . $filename;
            if (!move_uploaded_file($files['imagen']['tmp_name'], $destino)) {
                echo json_encode(['status' => 'error', 'message' => 'Error al guardar la imagen.']);
                return;
            }
            $imagen = $filename;
        }

        $ok = $model->updateInfo($id_sesion, compact('nombre', 'apellidos', 'email', 'telefono', 'imagen'));

        if ($ok) {
            $_SESSION['usuario_nombre']    = $nombre;
            $_SESSION['usuario_apellidos'] = $apellidos;
            $_SESSION['usuario_email']     = $email;
            if ($imagen !== null) {
                $_SESSION['usuario_imagen'] = $imagen;
            }
        }

        echo json_encode([
            'status'    => $ok ? 'ok'    : 'error',
            'message'   => $ok ? 'Perfil actualizado correctamente.' : 'Error al actualizar el perfil.',
            'nombre'    => $nombre,
            'apellidos' => $apellidos,
            'imagen'    => $imagen,
        ]);
    }

    public function updatePassword(array $post, int $id_sesion): void
    {
        $model        = new CfgPerfilModel();
        $actual       = $post['password_actual']       ?? '';
        $nueva        = $post['password_nueva']        ?? '';
        $confirmacion = $post['password_confirmacion'] ?? '';

        if (!$actual || !$nueva || !$confirmacion) {
            echo json_encode(['status' => 'error', 'message' => 'Todos los campos de contraseña son requeridos.']);
            return;
        }

        if (strlen($nueva) < 8) {
            echo json_encode(['status' => 'error', 'message' => 'La nueva contraseña debe tener al menos 8 caracteres.']);
            return;
        }

        if ($nueva !== $confirmacion) {
            echo json_encode(['status' => 'error', 'message' => 'La confirmación no coincide con la nueva contraseña.']);
            return;
        }

        $hash = $model->getPasswordHash($id_sesion);
        if (!$hash || !password_verify($actual, $hash)) {
            echo json_encode(['status' => 'error', 'message' => 'La contraseña actual es incorrecta.']);
            return;
        }

        $ok = $model->updatePassword($id_sesion, password_hash($nueva, PASSWORD_BCRYPT));
        echo json_encode([
            'status'  => $ok ? 'ok'    : 'error',
            'message' => $ok ? 'Contraseña actualizada correctamente.' : 'Error al actualizar la contraseña.',
        ]);
    }
}
