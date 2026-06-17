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
        Schema::table('produccion_ingresos_proceso', function (Blueprint $table) {
            $table->string('codigo_molde', 50)->nullable()->after('lote_produccion');
            $table->string('descripcion_molde')->nullable()->after('codigo_molde');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produccion_ingresos_proceso', function (Blueprint $table) {
            $table->dropColumn(['codigo_molde', 'descripcion_molde']);
        });
    }
};
