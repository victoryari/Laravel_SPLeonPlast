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
        Schema::create('transferencias_almacen', function (Blueprint $table) {
            $table->id('id_transferencia');
            $table->string('numero_transferencia', 20)->unique();
            $table->string('codigo_almacen_origen', 10)->collation('utf8mb4_general_ci');
            $table->string('codigo_almacen_destino', 10)->collation('utf8mb4_general_ci');
            $table->dateTime('fecha_transferencia');
            $table->text('observaciones')->nullable();
            $table->string('estado', 20)->default('COMPLETADO'); // COMPLETADO, ANULADO
            $table->unsignedBigInteger('usuario_registro')->nullable();
            $table->timestamps();

            $table->foreign('codigo_almacen_origen')->references('codigo_almacen')->on('almacen');
            $table->foreign('codigo_almacen_destino')->references('codigo_almacen')->on('almacen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferencias_almacen');
    }
};
