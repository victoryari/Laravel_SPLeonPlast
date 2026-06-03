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
        Schema::table('detalle_requerimientos_materiales', function (Blueprint $table) {
            $table->string('codigo_almacen_origen', 10)->nullable()->change();
            $table->string('codigo_almacen_destino', 10)->nullable()->change();
        });

        Schema::table('requerimientos_materiales', function (Blueprint $table) {
            $table->unsignedBigInteger('id_proceso')->nullable()->after('idop');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requerimientos_materiales', function (Blueprint $table) {
            $table->dropColumn('id_proceso');
        });

        Schema::table('detalle_requerimientos_materiales', function (Blueprint $table) {
            $table->string('codigo_almacen_origen', 10)->nullable(false)->change();
            $table->string('codigo_almacen_destino', 10)->nullable(false)->change();
        });
    }
};
