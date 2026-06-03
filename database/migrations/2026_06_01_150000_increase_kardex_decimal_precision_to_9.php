<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kardex', function (Blueprint $table) {
            $table->decimal('costo_promedio', 20, 9)->default(0)->change();
            $table->decimal('total_saldo', 20, 9)->default(0)->change();
            $table->decimal('costo_entrada', 20, 9)->default(0)->change();
            $table->decimal('costo_salida', 20, 9)->default(0)->change();
        });

        Schema::table('inventario', function (Blueprint $table) {
            $table->decimal('costo_promedio', 20, 9)->default(0)->change();
            $table->decimal('ultimo_costo', 20, 9)->default(0)->change();
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->decimal('costo_unitario', 20, 9)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('kardex', function (Blueprint $table) {
            $table->decimal('costo_promedio', 15, 4)->default(0)->change();
            $table->decimal('total_saldo', 15, 2)->default(0)->change();
            $table->decimal('costo_entrada', 15, 4)->default(0)->change();
            $table->decimal('costo_salida', 15, 4)->default(0)->change();
        });

        Schema::table('inventario', function (Blueprint $table) {
            $table->decimal('costo_promedio', 10, 2)->default(0)->change();
            $table->decimal('ultimo_costo', 15, 4)->default(0)->change();
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->decimal('costo_unitario', 15, 4)->default(0)->change();
        });
    }
};