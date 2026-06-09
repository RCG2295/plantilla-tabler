<?php

use Phinx\Seed\AbstractSeed;

class InventarioSidebarSeed extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['CfgAreasSeed', 'CfgRolesSeed'];
    }

    public function run(): void
    {
        $db = $this->getAdapter()->getConnection();

        // Insert area Inventario
        $db->exec("
            INSERT INTO cfg_areas (nombre, icono, orden, estado, id_alta, fecha_alta)
            VALUES ('Inventario', 'ti ti-package', 5, 0, NULL, NOW())
        ");
        $id_area = $db->lastInsertId();

        // Insert modules for the area
        $modulos = [
            ['clave' => 'inventario/categorias', 'nombre' => 'Categorías',         'orden' => 1],
            ['clave' => 'inventario/unidades',   'nombre' => 'Unidades de medida',  'orden' => 2],
            ['clave' => 'inventario/motivos',    'nombre' => 'Motivos de movimiento','orden' => 3],
            ['clave' => 'inventario/productos',  'nombre' => 'Productos',            'orden' => 4],
            ['clave' => 'inventario/movimientos','nombre' => 'Movimientos',          'orden' => 5],
        ];

        $stmt = $db->prepare("
            INSERT INTO cfg_modulos (id_area, clave, nombre, icono, orden, estado, id_alta, fecha_alta)
            VALUES (?, ?, ?, NULL, ?, 0, NULL, NOW())
        ");

        foreach ($modulos as $m) {
            $stmt->execute([$id_area, $m['clave'], $m['nombre'], $m['orden']]);
        }
    }
}
