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
        Schema::table('componentes_orden_produccion_global', function (Blueprint $table) {
            $table->date('fecha_inicio_maquina')->nullable()->after('hora_fin');
            $table->time('hora_inicio_maquina')->nullable()->after('fecha_inicio_maquina');
            $table->date('fecha_fin_maquina')->nullable()->after('hora_inicio_maquina');
            $table->time('hora_fin_maquina')->nullable()->after('fecha_fin_maquina');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('componentes_orden_produccion_global', function (Blueprint $table) {
            $table->dropColumn(['fecha_inicio_maquina', 'hora_inicio_maquina', 'fecha_fin_maquina', 'hora_fin_maquina']);
        });
    }
};
