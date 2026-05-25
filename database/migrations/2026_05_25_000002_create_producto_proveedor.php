<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // producto
        Schema::create('producto', function (Blueprint $table) {
            $table->string('codigo', 20)->primary();
            $table->string('descripcion', 200);
            $table->string('codigo_tipo_producto', 10)->nullable();
            $table->boolean('es_producto_proceso')->default(false);
            $table->string('codigo_color', 20)->nullable();
            $table->string('codigo_unidad_medida', 20)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamp('fecha_creacion')->useCurrent();

            $table->index('es_producto_proceso');
        });

        // proveedores
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id('id_proveedor');
            $table->string('ruc', 11)->unique();
            $table->string('razon_social', 200);
            $table->string('nombre_comercial', 200)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('contacto', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_registro')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('producto');
    }
};
