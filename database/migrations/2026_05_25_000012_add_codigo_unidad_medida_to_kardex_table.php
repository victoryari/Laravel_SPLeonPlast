<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kardex', function (Blueprint $table) {
            $table->string('codigo_unidad_medida', 10)->nullable()->after('codigo_producto');
        });
    }

    public function down(): void
    {
        Schema::table('kardex', function (Blueprint $table) {
            $table->dropColumn('codigo_unidad_medida');
        });
    }
};
