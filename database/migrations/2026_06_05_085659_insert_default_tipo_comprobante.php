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
            ['codigo_parametro' => 'DOC_FACTURA', 'descripcion' => 'Factura', 'valor' => 'FACTURA', 'tipo' => 'TEXTO', 'categoria' => 'TIPO_COMPROBANTE', 'editable' => 1, 'fecha_actualizacion' => now()],
            ['codigo_parametro' => 'DOC_BOLETA', 'descripcion' => 'Boleta', 'valor' => 'BOLETA', 'tipo' => 'TEXTO', 'categoria' => 'TIPO_COMPROBANTE', 'editable' => 1, 'fecha_actualizacion' => now()],
            ['codigo_parametro' => 'DOC_GUIA', 'descripcion' => 'Guía de Remisión', 'valor' => 'GUIA_REMISION', 'tipo' => 'TEXTO', 'categoria' => 'TIPO_COMPROBANTE', 'editable' => 1, 'fecha_actualizacion' => now()],
            ['codigo_parametro' => 'DOC_OTRO', 'descripcion' => 'Otro', 'valor' => 'OTRO', 'tipo' => 'TEXTO', 'categoria' => 'TIPO_COMPROBANTE', 'editable' => 1, 'fecha_actualizacion' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('parametros_sistema')->where('categoria', 'TIPO_COMPROBANTE')->delete();
    }
};
