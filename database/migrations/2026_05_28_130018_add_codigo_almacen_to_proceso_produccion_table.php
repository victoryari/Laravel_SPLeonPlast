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
        Schema::table('proceso_produccion', function (Blueprint $table) {
            $table->string('codigo_almacen', 10)->nullable()->after('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proceso_produccion', function (Blueprint $table) {
            $table->dropColumn('codigo_almacen');
        });
    }
};
