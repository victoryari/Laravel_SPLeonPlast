<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('parametros_sistema')->insert([
            'codigo_parametro' => 'COSTO_HORA_MAQUINA',
            'descripcion' => 'Costo estimado por hora máquina en producción (Soles)',
            'valor' => '15.50',
            'tipo' => 'NUMERICO',
            'categoria' => 'Producción',
            'editable' => 1,
            'fecha_actualizacion' => now(),
            'usuario_actualizacion' => 5
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('parametros_sistema')->where('codigo_parametro', 'COSTO_HORA_MAQUINA')->delete();
    }
};
