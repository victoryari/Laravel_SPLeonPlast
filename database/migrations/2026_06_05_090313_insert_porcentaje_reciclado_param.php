<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('parametros_sistema')->insert([
            'codigo_parametro' => 'PORCENTAJE_COSTO_RECICLADO',
            'descripcion' => 'Porcentaje del costo original que hereda el material reciclado (ej. 0.8 para 80%)',
            'valor' => '0.8',
            'tipo' => 'NUMERICO',
            'categoria' => 'PRODUCCION',
            'editable' => 1,
            'fecha_actualizacion' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('parametros_sistema')->where('codigo_parametro', 'PORCENTAJE_COSTO_RECICLADO')->delete();
    }
};
