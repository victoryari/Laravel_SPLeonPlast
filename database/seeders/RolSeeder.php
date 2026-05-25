<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema'],
            ['nombre' => 'Supervisor', 'descripcion' => 'Supervisión de producción y almacén'],
            ['nombre' => 'Especialista', 'descripcion' => 'Gestión de fórmulas y productos'],
            ['nombre' => 'Almacen', 'descripcion' => 'Gestión de inventario y recepciones'],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(
                ['nombre' => $rol['nombre']],
                $rol
            );
        }
    }
}
