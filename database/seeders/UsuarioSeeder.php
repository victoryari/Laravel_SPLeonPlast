<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $adminExists = DB::table('usuarios')->where('nombre_usuario', 'admin')->exists();
        if (!$adminExists) {
            $codTrabajador = DB::table('trabajador')->where('estado', 1)->value('codigo');

            DB::table('usuarios')->insert([
                'codigo_trabajador' => $codTrabajador,
                'nombre_usuario' => 'admin',
                'contrasena_hash' => Hash::make('admin123'),
                'email' => 'admin@leonplast.com',
                'rol' => 'Administrador',
                'activo' => 1,
            ]);
        }
    }
}
