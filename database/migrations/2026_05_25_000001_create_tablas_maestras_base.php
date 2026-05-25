<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tipo_producto
        Schema::create('tipo_producto', function (Blueprint $table) {
            $table->string('codigo', 10)->primary();
            $table->string('descripcion', 100);
            $table->boolean('estado')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        // unidad_medida
        Schema::create('unidad_medida', function (Blueprint $table) {
            $table->string('codigo', 10)->primary();
            $table->string('descripcion', 100);
            $table->boolean('estado')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        // color
        Schema::create('color', function (Blueprint $table) {
            $table->string('codigo', 20)->primary();
            $table->string('descripcion', 100);
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        // molde
        Schema::create('molde', function (Blueprint $table) {
            $table->string('codigo', 20)->primary();
            $table->string('descripcion', 100);
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('molde');
        Schema::dropIfExists('color');
        Schema::dropIfExists('unidad_medida');
        Schema::dropIfExists('tipo_producto');
    }
};
