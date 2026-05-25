<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // almacen
        Schema::create('almacen', function (Blueprint $table) {
            $table->string('codigo_almacen', 10)->primary();
            $table->string('descripcion', 100);
            $table->enum('tipo_almacen', ['MATERIA_PRIMA','PRODUCTO_TERMINADO','PRODUCTO_PROCESO','INSUMOS','SUMINISTROS']);
            $table->string('direccion', 200)->nullable();
            $table->string('responsable', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        // inventario
        Schema::create('inventario', function (Blueprint $table) {
            $table->id('id_inventario');
            $table->string('codigo_almacen', 10);
            $table->string('codigo_producto', 20);
            $table->string('codigo_unidad_medida', 20)->nullable();
            $table->string('lote', 50)->nullable();
            $table->boolean('estado')->default(true);
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('stock_actual', 15, 2)->default(0);
            $table->decimal('stock_minimo', 15, 2)->default(0);
            $table->decimal('stock_maximo', 15, 2)->default(0);
            $table->decimal('costo_promedio', 10, 2)->default(0);
            $table->decimal('ultimo_costo', 15, 4)->default(0);
            $table->dateTime('fecha_ultimo_movimiento')->nullable();
            $table->integer('usuario_ultimo_movimiento')->nullable()->unsigned();

            $table->unique(['codigo_almacen', 'codigo_producto', 'lote'], 'uk_inventario');
        });

        // movimientos_inventario
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->string('codigo_almacen', 10);
            $table->string('codigo_producto', 20);
            $table->string('codigo_unidad_medida', 20)->nullable();
            $table->string('lote', 50)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->enum('tipo_movimiento', ['INGRESO','SALIDA','TRASPASO','AJUSTE']);
            $table->decimal('cantidad', 15, 2);
            $table->decimal('costo_unitario', 15, 4)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('documento_referencia', 50)->nullable();
            $table->string('numero_referencia', 50)->nullable();
            $table->integer('idop')->nullable();
            $table->text('observaciones')->nullable();
            $table->integer('usuario_movimiento')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamp('fecha_movimiento')->useCurrent();
        });

        // kardex
        Schema::create('kardex', function (Blueprint $table) {
            $table->id('id_kardex');
            $table->string('codigo_almacen', 10);
            $table->string('codigo_producto', 20);
            $table->dateTime('fecha_movimiento');
            $table->enum('tipo_movimiento', ['INGRESO','SALIDA','TRASPASO','AJUSTE','EXTORNO']);
            $table->string('documento', 50);
            $table->string('numero_documento', 50);
            $table->decimal('cantidad_entrada', 15, 2)->default(0);
            $table->decimal('costo_entrada', 15, 4)->default(0);
            $table->decimal('total_entrada', 15, 2)->default(0);
            $table->decimal('cantidad_salida', 15, 2)->default(0);
            $table->decimal('costo_salida', 15, 4)->default(0);
            $table->decimal('total_salida', 15, 2)->default(0);
            $table->decimal('cantidad_saldo', 15, 2)->default(0);
            $table->string('observaciones', 255)->nullable();
            $table->decimal('costo_promedio', 15, 4)->default(0);
            $table->decimal('total_saldo', 15, 2)->default(0);
            $table->string('lote', 50)->nullable();
            $table->integer('usuario_registro')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kardex');
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('inventario');
        Schema::dropIfExists('almacen');
    }
};
