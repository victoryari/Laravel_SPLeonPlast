<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centro_trabajo_produccion', function (Blueprint $table) {
            $table->string('codigo', 10)->primary();
            $table->string('descripcion', 100);
            $table->boolean('estado')->default(true)->comment('1=Activo, 0=Anulado');
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        Schema::create('proceso_produccion', function (Blueprint $table) {
            $table->integer('codigo')->primary();
            $table->string('descripcion', 100);
            $table->boolean('estado')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        Schema::create('operacion_produccion', function (Blueprint $table) {
            $table->string('codigo', 20)->primary();
            $table->string('descripcion', 100);
            $table->boolean('estado')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        Schema::create('actividad_produccion', function (Blueprint $table) {
            $table->string('codigo', 20)->primary();
            $table->string('descripcion', 100);
            $table->boolean('estado')->default(true)->comment('1=Activo, 0=Anulado');
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        Schema::create('sub_operacion_produccion', function (Blueprint $table) {
            $table->string('codigo', 20)->primary();
            $table->string('descripcion', 100);
            $table->boolean('estado')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        Schema::create('trabajador', function (Blueprint $table) {
            $table->string('codigo', 20)->primary();
            $table->string('nombre', 100);
            $table->string('empresa', 100)->nullable();
            $table->decimal('sueldo', 10, 2)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        Schema::create('insumo', function (Blueprint $table) {
            $table->string('codigo', 20)->primary();
            $table->string('descripcion', 100);
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insumo');
        Schema::dropIfExists('trabajador');
        Schema::dropIfExists('sub_operacion_produccion');
        Schema::dropIfExists('actividad_produccion');
        Schema::dropIfExists('operacion_produccion');
        Schema::dropIfExists('proceso_produccion');
        Schema::dropIfExists('centro_trabajo_produccion');
    }
};
