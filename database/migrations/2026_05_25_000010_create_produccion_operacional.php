<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produccion_ingresos_proceso', function (Blueprint $table) {
            $table->id('id_ingreso');
            $table->integer('idop')->unsigned();
            $table->integer('id_proceso')->unsigned();
            $table->string('codigo_producto_proceso', 20);
            $table->string('descripcion_producto_proceso', 200)->nullable();
            $table->decimal('cantidad', 15, 2);
            $table->string('codigo_unidad_medida', 10)->nullable();
            $table->string('codigo_almacen', 10)->nullable();
            $table->string('lote_produccion', 50)->nullable();
            $table->dateTime('fecha_ingreso');
            $table->integer('usuario_registro')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['PENDIENTE','APROBADO','ACTIVO','ANULADO'])->default('PENDIENTE');
        });

        Schema::create('operaciones_ingreso_orden_produccion_global', function (Blueprint $table) {
            $table->id('id_op_ingreso');
            $table->integer('idop')->unsigned();
            $table->string('codigo_operacion', 20);
            $table->string('descripcion_operacion', 100)->nullable();
            $table->string('codigo_centro_trabajo', 10);
            $table->string('descripcion_centro_trabajo', 100)->nullable();
            $table->string('codigo_trabajador', 20);
            $table->string('descripcion_trabajador', 100)->nullable();
            $table->string('codigo_sub_operacion', 20);
            $table->string('descripcion_sub_operacion', 100)->nullable();
            $table->string('codigo_actividad', 20);
            $table->string('descripcion_actividad', 100)->nullable();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin')->nullable();
            $table->decimal('cantidad', 15, 2);
        });

        Schema::create('produccion_consumos', function (Blueprint $table) {
            $table->id('id_consumo');
            $table->integer('idop')->unsigned();
            $table->string('codigo_producto', 20);
            $table->string('descripcion_producto', 200)->nullable();
            $table->decimal('cantidad_requerida', 15, 2);
            $table->decimal('cantidad_consumida', 15, 2)->default(0);
            $table->decimal('costo_unitario', 15, 4)->default(0);
            $table->decimal('costo_total', 15, 2)->default(0);
            $table->string('codigo_almacen', 10);
            $table->string('lote', 50)->nullable();
            $table->dateTime('fecha_consumo')->nullable();
            $table->integer('usuario_consumo')->nullable();
            $table->enum('estado', ['PENDIENTE','PARCIAL','COMPLETADO'])->default('PENDIENTE');
        });

        Schema::create('produccion_costos', function (Blueprint $table) {
            $table->id('id_costo');
            $table->integer('idop')->unsigned();
            $table->enum('tipo_costo', ['MATERIA_PRIMA','MANO_OBRA','GASTOS_FABRICA','EQUIPOS','OTROS']);
            $table->string('descripcion', 200);
            $table->decimal('cantidad', 15, 2)->default(1);
            $table->decimal('costo_unitario', 15, 4);
            $table->decimal('costo_total', 15, 2);
            $table->enum('moneda', ['PEN','USD'])->default('PEN');
            $table->date('fecha_costo');
            $table->integer('usuario_registro')->nullable();
            $table->text('observaciones')->nullable();
        });

        Schema::create('produccion_productos_proceso', function (Blueprint $table) {
            $table->id('id_producto_proceso');
            $table->integer('idop')->unsigned();
            $table->string('codigo_producto', 20);
            $table->string('descripcion_producto', 200)->nullable();
            $table->decimal('cantidad_producida', 15, 2);
            $table->decimal('cantidad_buen_estado', 15, 2)->default(0);
            $table->decimal('cantidad_defectuosa', 15, 2)->default(0);
            $table->decimal('costo_unitario', 15, 4)->default(0);
            $table->decimal('costo_total', 15, 2)->default(0);
            $table->string('codigo_almacen', 10);
            $table->string('lote_produccion', 50)->nullable();
            $table->dateTime('fecha_produccion')->nullable();
            $table->integer('usuario_registro')->nullable();
            $table->enum('estado', ['EN_PROCESO','TERMINADO','CONTROL_CALIDAD'])->default('EN_PROCESO');
        });

        Schema::create('produccion_valorizacion', function (Blueprint $table) {
            $table->id('id_valorizacion');
            $table->integer('idop')->unsigned();
            $table->enum('etapa', ['INICIAL','EN_PROCESO','FINAL']);
            $table->decimal('costo_materia_prima', 15, 2)->default(0);
            $table->decimal('costo_mano_obra', 15, 2)->default(0);
            $table->decimal('costo_gastos_fabrica', 15, 2)->default(0);
            $table->decimal('costo_equipos', 15, 2)->default(0);
            $table->decimal('costo_otros', 15, 2)->default(0);
            $table->decimal('costo_total', 15, 2)->default(0);
            $table->decimal('cantidad_producida', 15, 2)->default(0);
            $table->decimal('costo_unitario', 15, 4)->default(0);
            $table->timestamp('fecha_valorizacion')->useCurrent();
            $table->integer('usuario_valorizacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produccion_valorizacion');
        Schema::dropIfExists('produccion_productos_proceso');
        Schema::dropIfExists('produccion_costos');
        Schema::dropIfExists('produccion_consumos');
        Schema::dropIfExists('operaciones_ingreso_orden_produccion_global');
        Schema::dropIfExists('produccion_ingresos_proceso');
    }
};
