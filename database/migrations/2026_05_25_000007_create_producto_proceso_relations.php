<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_proceso', function (Blueprint $table) {
            $table->integer('codigo')->primary();
            $table->string('descripcion', 100);
            $table->boolean('estado')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        Schema::create('producto_molde', function (Blueprint $table) {
            $table->integer('codigo_producto_proceso');
            $table->string('codigo_molde', 20);
            $table->primary(['codigo_producto_proceso', 'codigo_molde']);
        });

        Schema::create('proceso_centro_trabajo', function (Blueprint $table) {
            $table->id();
            $table->integer('codigo_proceso');
            $table->string('codigo_centro_trabajo', 10);
            $table->unique(['codigo_proceso', 'codigo_centro_trabajo'], 'uk_proceso_centro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proceso_centro_trabajo');
        Schema::dropIfExists('producto_molde');
        Schema::dropIfExists('producto_proceso');
    }
};
