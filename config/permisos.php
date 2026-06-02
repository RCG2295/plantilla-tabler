<?php

function puedo(string $modulo, string $accion): bool
{
    if (!isset($_SESSION['usuario_id'])) return false;

    if ($_SESSION['es_superadmin'] ?? false) return true;

    $p = $_SESSION['permisos'][$modulo] ?? null;
    if ($p === null) return false;

    // Any action permission implies ver
    if ($accion === 'ver') {
        return (bool)($p['ver'] || $p['crear'] || $p['editar'] || $p['eliminar']);
    }

    return (bool)($p[$accion] ?? false);
}
