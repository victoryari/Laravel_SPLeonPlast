<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('componentes_orden_produccion_global', function (Blueprint $table) {
            $table->string('lote_produccion_pep', 100)->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('componentes_orden_produccion_global', function (Blueprint $table) {
            $table->dropColumn('lote_produccion_pep');
        });
    }
};
