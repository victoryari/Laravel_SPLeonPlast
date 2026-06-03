<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequerimientosMaterialesSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = [
            ['nombre' => 'Requerimientos - Listado', 'slug' => 'requerimientos_materiales.index', 'grupo' => 'requerimientos_materiales', 'icono' => 'clipboard-list'],
            ['nombre' => 'Requerimientos - Crear', 'slug' => 'requerimientos_materiales.create', 'grupo' => 'requerimientos_materiales', 'icono' => 'clipboard-list'],
            ['nombre' => 'Requerimientos - Editar', 'slug' => 'requerimientos_materiales.edit', 'grupo' => 'requerimientos_materiales', 'icono' => 'clipboard-list'],
            ['nombre' => 'Requerimientos - Ver', 'slug' => 'requerimientos_materiales.show', 'grupo' => 'requerimientos_materiales', 'icono' => 'clipboard-list'],
            ['nombre' => 'Requerimientos - Enviar', 'slug' => 'requerimientos_materiales.enviar', 'grupo' => 'requerimientos_materiales', 'icono' => 'clipboard-list'],
            ['nombre' => 'Requerimientos - Aprobar', 'slug' => 'requerimientos_materiales.aprobar', 'grupo' => 'requerimientos_materiales', 'icono' => 'clipboard-list'],
            ['nombre' => 'Requerimientos - Atender', 'slug' => 'requerimientos_materiales.atender', 'grupo' => 'requerimientos_materiales', 'icono' => 'clipboard-list'],
        ];

        $insertedIds = [];
        foreach ($modulos as $mod) {
            $id = DB::table('modulos')->updateOrInsert(
                ['slug' => $mod['slug']],
                $mod
            );
            if (!$id) {
                $id = DB::table('modulos')->where('slug', $mod['slug'])->value('id');
            } else {
                $id = DB::getPdo()->lastInsertId();
            }
            $insertedIds[] = $id;
        }

        $adminId = DB::table('roles')->where('nombre', 'Administrador')->value('id');
        $supervisorId = DB::table('roles')->where('nombre', 'Supervisor')->value('id');
        $especialistaId = DB::table('roles')->where('nombre', 'Especialista')->value('id');

        $slugs = array_column($modulos, 'slug');

        $allModules = DB::table('modulos')->whereIn('slug', $slugs)->pluck('id', 'slug');

        // Admin: todos los módulos
        foreach ($allModules as $slug => $modId) {
            DB::table('rol_modulo')->updateOrInsert(
                ['rol_id' => $adminId, 'modulo_id' => $modId],
                ['rol_id' => $adminId, 'modulo_id' => $modId]
            );
        }

        // Supervisor: index, create, show, edit, enviar, atender
        $supervisorSlugs = [
            'requerimientos_materiales.index',
            'requerimientos_materiales.create',
            'requerimientos_materiales.show',
            'requerimientos_materiales.edit',
            'requerimientos_materiales.enviar',
            'requerimientos_materiales.atender',
        ];
        foreach ($allModules as $slug => $modId) {
            if (in_array($slug, $supervisorSlugs)) {
                DB::table('rol_modulo')->updateOrInsert(
                    ['rol_id' => $supervisorId, 'modulo_id' => $modId],
                    ['rol_id' => $supervisorId, 'modulo_id' => $modId]
                );
            }
        }

        // Especialista: index, create, show, enviar
        $especialistaSlugs = [
            'requerimientos_materiales.index',
            'requerimientos_materiales.create',
            'requerimientos_materiales.show',
            'requerimientos_materiales.enviar',
        ];
        foreach ($allModules as $slug => $modId) {
            if (in_array($slug, $especialistaSlugs)) {
                DB::table('rol_modulo')->updateOrInsert(
                    ['rol_id' => $especialistaId, 'modulo_id' => $modId],
                    ['rol_id' => $especialistaId, 'modulo_id' => $modId]
                );
            }
        }
    }
}
