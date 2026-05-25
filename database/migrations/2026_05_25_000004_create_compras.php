<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // compras
        Schema::create('compras', function (Blueprint $table) {
            $table->id('id_compra');
            $table->enum('tipo_documento', ['FACTURA','BOLETA','RECIBO_HONORARIOS','NOTA_CREDITO','NOTA_DEBITO','GUIA_REMISION','DAM','OTRO'])->default('FACTURA');
            $table->string('serie_documento', 10)->nullable();
            $table->string('numero_documento', 20)->nullable();
            $table->string('proveedor', 100);
            $table->string('ruc_proveedor', 11)->nullable();
            $table->date('fecha_compra');
            $table->date('fecha_entrega')->nullable();
            $table->enum('condicion_pago', ['CONTADO','CREDITO_15','CREDITO_30','CREDITO_45','CREDITO_60','OTRO'])->default('CONTADO');
            $table->enum('estado', ['PENDIENTE','RECIBIDA','PARCIAL','COMPLETADA','CANCELADA'])->default('PENDIENTE');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('igv', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('moneda', ['PEN','USD'])->default('PEN');
            $table->decimal('tipo_cambio', 8, 3)->default(1);
            $table->boolean('afecto_igv')->default(true);
            $table->text('observaciones')->nullable();
            $table->text('glosa')->nullable();
            $table->integer('usuario_creacion')->nullable()->unsigned();
            $table->integer('usuario_aprobacion')->nullable()->unsigned();
            $table->dateTime('fecha_aprobacion')->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        // detalle_compra
        Schema::create('detalle_compra', function (Blueprint $table) {
            $table->id('id_detalle_compra');
            $table->integer('id_compra')->unsigned();
            $table->string('codigo_producto', 20);
            $table->string('descripcion_producto', 200)->nullable();
            $table->decimal('cantidad', 15, 2);
            $table->decimal('precio_unitario', 15, 4);
            $table->string('codigo_unidad_medida', 10)->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('igv', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->string('codigo_almacen', 10)->nullable();
            $table->string('lote', 50)->nullable();
            $table->date('fecha_vencimiento')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_compra');
        Schema::dropIfExists('compras');
    }
};
