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
        Schema::create('transferencias_almacen_detalle', function (Blueprint $table) {
            $table->id('id_detalle');
            $table->unsignedBigInteger('id_transferencia');
            $table->string('codigo_producto', 20)->collation('utf8mb4_general_ci');
            $table->string('lote', 50)->nullable();
            $table->decimal('cantidad', 10, 4);
            $table->timestamps();

            $table->foreign('id_transferencia')->references('id_transferencia')->on('transferencias_almacen')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferencias_almacen_detalle');
    }
};
