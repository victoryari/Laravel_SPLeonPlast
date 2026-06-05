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
        Schema::create('mermas', function (Blueprint $table) {
            $table->id('id_merma');
            $table->string('codigo_producto', 50); 
            $table->string('descripcion_producto', 255);
            $table->decimal('cantidad', 12, 4);
            $table->decimal('costo_unitario', 12, 4)->default(0);
            $table->decimal('costo_total', 12, 4)->default(0);
            $table->string('motivo', 255)->nullable();
            $table->enum('tipo_merma', ['PURA', 'RECUPERABLE', 'MOLIDO'])->default('RECUPERABLE');
            $table->string('codigo_almacen', 20);
            $table->unsignedBigInteger('id_orden_produccion')->nullable();
            $table->string('estado', 20)->default('REGISTRADA');
            $table->unsignedBigInteger('usuario_registro');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mermas');
    }
};
