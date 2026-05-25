<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('componentes_orden_produccion_global', function (Blueprint $table) {
            $table->id('id_op_componentes');
            $table->integer('idop')->unsigned();
            $table->integer('id_proceso')->nullable()->unsigned();
            $table->string('codigo_tipo_producto', 10);
            $table->string('descripcion_tipo_producto', 100)->nullable();
            $table->string('codigo_producto', 20);
            $table->string('descripcion_producto', 200)->nullable();
            $table->string('codigo_centro_trabajo', 10)->nullable();
            $table->string('descripcion_centro_trabajo', 100)->nullable();
            $table->string('codigo_molde', 20)->nullable();
            $table->string('descripcion_molde', 100)->nullable();
            $table->string('codigo_unidad_medida', 10);
            $table->string('descripcion_unidad_medida', 100)->nullable();
            $table->decimal('cantidad', 15, 2);
            $table->string('codigo_color', 20)->nullable();
            $table->string('descripcion_color', 100)->nullable();
            $table->string('codigo_trabajador', 20)->nullable();
            $table->string('descripcion_trabajador', 100)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('codigo_formula_produccion', 20)->nullable();
            $table->string('descripcion_formula_produccion', 100)->nullable();
            $table->integer('codigo_composicion_formula')->nullable()->unsigned();
            $table->boolean('estado')->default(true)->comment('1=Activo, 0=Inactivo (Eliminado lógico)');
            $table->timestamp('fecha_creacion')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('componentes_orden_produccion_global');
    }
};
