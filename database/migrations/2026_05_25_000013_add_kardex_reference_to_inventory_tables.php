<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kardex', function (Blueprint $table) {
            $table->unsignedBigInteger('codigo_referencia_movimiento')->nullable()->after('codigo_unidad_medida');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->boolean('tiene_kardex')->default(false)->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('kardex', function (Blueprint $table) {
            $table->dropColumn('codigo_referencia_movimiento');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropColumn('tiene_kardex');
        });
    }
};
