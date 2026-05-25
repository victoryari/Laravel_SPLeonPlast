<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuloSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = [
            // Tablas Maestras
            ['nombre' => 'Procesos de producción', 'slug' => 'procesos_produccion.index', 'grupo' => 'Tablas Maestras', 'icono' => 'fas fa-cogs'],
            ['nombre' => 'Fórmulas', 'slug' => 'formulas.index', 'grupo' => 'Tablas Maestras', 'icono' => 'fas fa-flask'],
            ['nombre' => 'Productos', 'slug' => 'productos.index', 'grupo' => 'Tablas Maestras', 'icono' => 'fas fa-box'],
            ['nombre' => 'Tipo de productos', 'slug' => 'tipos_producto.index', 'grupo' => 'Tablas Maestras', 'icono' => 'fas fa-tags'],
            ['nombre' => 'Unidades de medida', 'slug' => 'unidades_medida.index', 'grupo' => 'Tablas Maestras', 'icono' => 'fas fa-ruler'],
            ['nombre' => 'Operaciones', 'slug' => 'operaciones_produccion.index', 'grupo' => 'Tablas Maestras', 'icono' => 'fas fa-wrench'],
            ['nombre' => 'Centros de trabajo', 'slug' => 'centros_trabajo.index', 'grupo' => 'Tablas Maestras', 'icono' => 'fas fa-building'],
            ['nombre' => 'Trabajadores', 'slug' => 'trabajadores.index', 'grupo' => 'Tablas Maestras', 'icono' => 'fas fa-users'],
            ['nombre' => 'Proveedores', 'slug' => 'proveedores.index', 'grupo' => 'Tablas Maestras', 'icono' => 'fas fa-truck'],
            ['nombre' => 'Actividades', 'slug' => 'actividades.index', 'grupo' => 'Tablas Maestras', 'icono' => 'fas fa-tasks'],
            ['nombre' => 'Moldes', 'slug' => 'moldes.index', 'grupo' => 'Tablas Maestras', 'icono' => 'fas fa-cube'],
            ['nombre' => 'Colores', 'slug' => 'colores.index', 'grupo' => 'Tablas Maestras', 'icono' => 'fas fa-palette'],

            // Principal
            ['nombre' => 'Compras', 'slug' => 'compras.index', 'grupo' => 'Principal', 'icono' => 'fas fa-shopping-cart'],
            ['nombre' => 'Almacén', 'slug' => 'almacenes.index', 'grupo' => 'Principal', 'icono' => 'fas fa-warehouse'],
            ['nombre' => 'Órdenes de Producción', 'slug' => 'produccion.ordenes.index', 'grupo' => 'Principal', 'icono' => 'fas fa-industry'],
            ['nombre' => 'Reportes', 'slug' => 'reportes.index', 'grupo' => 'Principal', 'icono' => 'fas fa-file-invoice-dollar'],

            // Inventario
            ['nombre' => 'Existencias', 'slug' => 'inventario.index', 'grupo' => 'Inventario', 'icono' => 'fas fa-boxes'],
            ['nombre' => 'Recepciones Pendientes', 'slug' => 'inventario.recepciones', 'grupo' => 'Inventario', 'icono' => 'fas fa-clipboard-check'],
            ['nombre' => 'Kardex de Movimientos', 'slug' => 'inventario.kardex', 'grupo' => 'Inventario', 'icono' => 'fas fa-list-alt'],
            ['nombre' => 'Ajuste Manual', 'slug' => 'inventario.ajuste', 'grupo' => 'Inventario', 'icono' => 'fas fa-sliders-h'],
            ['nombre' => 'Extornos', 'slug' => 'inventario.extornos', 'grupo' => 'Inventario', 'icono' => 'fas fa-undo-alt'],

            // Administración
            ['nombre' => 'Usuarios', 'slug' => 'usuarios.index', 'grupo' => 'Administración', 'icono' => 'fas fa-users-cog'],
            ['nombre' => 'Roles y Permisos', 'slug' => 'roles.index', 'grupo' => 'Administración', 'icono' => 'fas fa-user-shield'],
        ];

        $adminRoleId = DB::table('roles')->where('nombre', 'Administrador')->value('id');

        foreach ($modulos as $mod) {
            $existing = DB::table('modulos')->where('slug', $mod['slug'])->first();
            if ($existing) {
                DB::table('modulos')->where('id', $existing->id)->update($mod);
                $moduloId = $existing->id;
            } else {
                $moduloId = DB::table('modulos')->insertGetId($mod);
            }

            if ($adminRoleId) {
                DB::table('rol_modulo')->updateOrInsert(
                    ['rol_id' => $adminRoleId, 'modulo_id' => $moduloId],
                    ['rol_id' => $adminRoleId, 'modulo_id' => $moduloId]
                );
            }
        }
    }
}
