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
        Schema::create('guia_remision_compras', function (Blueprint $table) {
            $table->id('id_guia');
            $table->string('proveedor', 100);
            $table->string('ruc_proveedor', 11)->nullable();
            $table->string('numero_guia', 20);
            $table->date('fecha_emision');
            $table->enum('estado', ['RECIBIDA', 'FACTURADA', 'ANULADA'])->default('RECIBIDA');
            $table->text('observaciones')->nullable();
            $table->integer('usuario_registro')->unsigned();
            $table->timestamp('fecha_registro')->useCurrent();
        });

        Schema::create('detalle_guia_compras', function (Blueprint $table) {
            $table->id('id_detalle_guia');
            $table->foreignId('id_guia')->references('id_guia')->on('guia_remision_compras')->onDelete('cascade');
            $table->string('codigo_producto', 20);
            $table->string('descripcion_producto', 200)->nullable();
            $table->decimal('cantidad', 15, 2);
            $table->string('codigo_unidad_medida', 10)->nullable();
            $table->string('codigo_almacen', 10)->default('P2A'); // ALMACEN COMPRAS NAC/IMP
            $table->string('lote', 50)->nullable();
            $table->date('fecha_vencimiento')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_guia_compras');
        Schema::dropIfExists('guia_remision_compras');
    }
};
