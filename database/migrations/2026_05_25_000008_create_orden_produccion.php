<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_produccion_global', function (Blueprint $table) {
            $table->id('idop');
            $table->string('codigo_op', 50)->nullable();
            $table->integer('codigo_producto_proceso')->nullable()->unsigned();
            $table->string('descripcion_producto_proceso', 100)->nullable();
            $table->integer('codigo_proceso_produccion')->nullable()->unsigned();
            $table->string('codigo_centro_trabajo_produccion', 10)->nullable();
            $table->string('descripcion_centro_trabajo_produccion', 100)->nullable();
            $table->string('descripcion_proceso_produccion', 100)->nullable();
            $table->date('fecha')->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->text('texto_obs')->nullable();
            $table->enum('estado', ['PENDIENTE','EN_PROCESO','COMPLETADO','CANCELADO'])->default('PENDIENTE');
            $table->decimal('cantidad', 15, 2)->nullable();
            $table->string('codigo_formula_produccion', 20)->nullable();
            $table->string('codigo_color', 20)->nullable();
            $table->string('descripcion_color', 100)->nullable();
            $table->string('codigo_materia_prima', 20)->nullable();
            $table->string('descripcion_materia_prima', 200)->nullable();
            $table->string('codigo_molde', 20)->nullable();
            $table->string('codigo_insumo', 20)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        Schema::create('orden_proceso', function (Blueprint $table) {
            $table->id();
            $table->integer('idop')->unsigned();
            $table->integer('secuencia');
            $table->string('codigo_proceso', 20);
            $table->string('descripcion_proceso', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('estado')->default(true)->comment('1=Activo, 0=Anulado (Soft Delete)');
            $table->string('estado_avance', 20)->default('PENDIENTE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_proceso');
        Schema::dropIfExists('orden_produccion_global');
    }
};
