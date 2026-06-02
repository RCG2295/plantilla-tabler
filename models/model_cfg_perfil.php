<?php

class CfgPerfilModel
{
    public function getById(int $id): array|false
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("
            SELECT id, nombre, apellidos, email, telefono, imagen
            FROM admin_usuarios
            WHERE id = ? AND estado != 2
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function emailExiste(string $email, int $exclude_id): bool
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("SELECT id FROM admin_usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $exclude_id]);
        return $stmt->fetch() !== false;
    }

    public function getPasswordHash(int $id): string|false
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("SELECT password FROM admin_usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['password'] : false;
    }

    public function updateInfo(int $id, array $data): bool
    {
        $db     = Connection::connect();
        $sets   = ['nombre = ?', 'apellidos = ?', 'email = ?', 'telefono = ?'];
        $params = [
            $data['nombre'],
            $data['apellidos'],
            $data['email'],
            $data['telefono'] ?: null,
        ];

        if ($data['imagen'] !== null) {
            $sets[]   = 'imagen = ?';
            $params[] = $data['imagen'];
        }

        $params[] = $id;
        $stmt     = $db->prepare('UPDATE admin_usuarios SET ' . implode(', ', $sets) . ' WHERE id = ?');
        return $stmt->execute($params);
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $db   = Connection::connect();
        $stmt = $db->prepare("UPDATE admin_usuarios SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }
}
