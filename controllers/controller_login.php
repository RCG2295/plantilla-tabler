<?php

class LoginController {

    public function login($email, $password) {
        $model   = new LoginModel();
        $usuario = $model->getUserByEmail($email);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            return ['status' => 'error', 'message' => 'Credenciales incorrectas.'];
        }

        $_SESSION['usuario_id']          = $usuario['id'];
        $_SESSION['usuario_nombre']      = $usuario['nombre'];
        $_SESSION['usuario_apellidos']   = $usuario['apellidos'];
        $_SESSION['usuario_email']       = $usuario['email'];
        $_SESSION['usuario_imagen']      = $usuario['imagen'] ?? null;
        $_SESSION['usuario_rol_id']      = $usuario['id_rol'];
        $_SESSION['usuario_rol_nombre']  = $usuario['rol_nombre'];
        $_SESSION['es_superadmin']       = (bool) $usuario['es_superadmin'];
        $_SESSION['id_sucursal']         = $usuario['id_sucursal'] ? (int) $usuario['id_sucursal'] : null;
        $_SESSION['sucursal_nombre']     = $usuario['sucursal_nombre'] ?? null;
        $_SESSION['id_sucursal_propia']  = $_SESSION['id_sucursal'];

        if ($usuario['es_superadmin']) {
            $_SESSION['permisos'] = [];
        } else {
            $permisos_raw = $model->getPermisosByRol($usuario['id_rol']);
            $permisos     = [];
            foreach ($permisos_raw as $row) {
                $permisos[$row['clave']] = [
                    'ver'      => (int) $row['ver'],
                    'crear'    => (int) $row['crear'],
                    'editar'   => (int) $row['editar'],
                    'eliminar' => (int) $row['eliminar'],
                ];
            }
            $_SESSION['permisos'] = $permisos;
        }

        return ['status' => 'ok'];
    }

    public function logout() {
        session_destroy();
    }

}
