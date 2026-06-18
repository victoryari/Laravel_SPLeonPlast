<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('terceros_mapeo_productos', function (Blueprint $table) {
            $table->id('id_mapeo');
            $table->string('codigo_producto_origen', 50)->comment('El PEP que se envía al tercero (ej. C100_Z002)');
            $table->string('codigo_producto_destino', 50)->comment('El material que retorna el tercero (ej. 01.20.020.002)');
            $table->text('descripcion_proceso')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();
        });

        Schema::create('guia_remision_terceros_salida', function (Blueprint $table) {
            $table->id('id_guia_salida');
            $table->string('numero_guia', 50)->unique();
            $table->date('fecha_emision');
            $table->string('codigo_almacen_origen', 20);
            $table->string('proveedor_destino', 200);
            $table->string('ruc_proveedor', 20)->nullable();
            $table->string('motivo_traslado', 100)->default('SERVICIOS DE TERCEROS');
            $table->text('observaciones')->nullable();
            $table->string('estado_guia', 20)->default('EMITIDA'); // EMITIDA, EN_PROCESO, CERRADA, ANULADA
            $table->unsignedBigInteger('usuario_registro');
            $table->timestamps();
        });

        Schema::create('guia_remision_terceros_salida_detalle', function (Blueprint $table) {
            $table->id('id_detalle_salida');
            $table->unsignedBigInteger('id_guia_salida');
            $table->string('codigo_producto', 50);
            $table->decimal('cantidad_enviada', 12, 2);
            $table->decimal('cantidad_devuelta', 12, 2)->default(0);
            $table->decimal('cantidad_merma', 12, 2)->default(0);
            $table->string('estado_detalle', 20)->default('PENDIENTE'); // PENDIENTE, PARCIAL, COMPLETADO, CERRADO_CON_MERMA
            $table->timestamps();

            $table->foreign('id_guia_salida', 'fk_guia_salida_terceros')
                  ->references('id_guia_salida')->on('guia_remision_terceros_salida')
                  ->onDelete('cascade');
        });

        Schema::create('conciliacion_terceros', function (Blueprint $table) {
            $table->id('id_conciliacion');
            $table->unsignedBigInteger('id_detalle_salida');
            $table->unsignedBigInteger('id_detalle_compra')->comment('Referencia a detalle_guia_compras (el ingreso)');
            $table->decimal('cantidad_amortizada', 12, 2);
            $table->date('fecha_conciliacion');
            $table->timestamps();

            $table->foreign('id_detalle_salida', 'fk_concilia_detalle_salida')
                  ->references('id_detalle_salida')->on('guia_remision_terceros_salida_detalle')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('conciliacion_terceros');
        Schema::dropIfExists('guia_remision_terceros_salida_detalle');
        Schema::dropIfExists('guia_remision_terceros_salida');
        Schema::dropIfExists('terceros_mapeo_productos');
    }
};
