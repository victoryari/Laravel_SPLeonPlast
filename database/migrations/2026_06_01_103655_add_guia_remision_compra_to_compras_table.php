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
        Schema::table('compras', function (Blueprint $table) {
            $table->unsignedBigInteger('id_guia_remision_compra')->nullable()->after('id_compra');
            
            // Note: because we use id_compra as primary key in compras (which is bigIncrements usually, or id()), we just link it.
            // But since 'compras' might use standard bigInteger for id, let's just make it a simple column for now.
            // If needed, we can add a foreign key constraint, but LeonPlast uses MyISAM mostly or InnoDB without strict constraints in some tables.
            $table->foreign('id_guia_remision_compra')->references('id_guia')->on('guia_remision_compras')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropForeign(['id_guia_remision_compra']);
            $table->dropColumn('id_guia_remision_compra');
        });
    }
};
