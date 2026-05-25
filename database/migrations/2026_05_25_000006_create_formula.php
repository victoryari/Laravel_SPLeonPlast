<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formula_produccion', function (Blueprint $table) {
            $table->string('codigo', 20)->primary();
            $table->string('descripcion', 100);
            $table->boolean('estado')->default(true)->comment('1=Activo, 0=Anulado');
            $table->string('codigo_producto_resultante', 20)->nullable()->comment('Código del producto PEP que genera esta fórmula');
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        Schema::create('composicion_formula', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_formula', 20);
            $table->string('codigo_tipo_producto', 10);
            $table->string('codigo_producto', 20);
            $table->decimal('cantidad_nominal', 15, 2);
            $table->decimal('cantidad_real', 15, 2);
            $table->string('codigo_unidad_medida', 10);
            $table->string('codigo_molde', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('composicion_formula');
        Schema::dropIfExists('formula_produccion');
    }
};
