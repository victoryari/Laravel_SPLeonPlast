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
        Schema::create('producto_proceso_rutas', function (Blueprint $table) {
            $table->id();
            $table->integer('codigo_producto_proceso');
            $table->integer('codigo_proceso');
            $table->integer('secuencia')->default(10);
            $table->timestamps();

            // Asegurar que no se repitan los mismos procesos para un producto
            $table->unique(['codigo_producto_proceso', 'codigo_proceso'], 'unq_producto_proceso');
            
            // Relaciones
            $table->foreign('codigo_producto_proceso', 'fk_ruta_prod')->references('codigo')->on('producto_proceso')->onDelete('cascade');
            $table->foreign('codigo_proceso', 'fk_ruta_proc')->references('codigo')->on('proceso_produccion')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_proceso_rutas');
    }
};
