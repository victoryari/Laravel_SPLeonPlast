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
        Schema::table('requerimientos_materiales', function (Blueprint $table) {
            $table->string('codigo_almacen', 10)->nullable()->after('id_proceso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requerimientos_materiales', function (Blueprint $table) {
            $table->dropColumn('codigo_almacen');
        });
    }
};
