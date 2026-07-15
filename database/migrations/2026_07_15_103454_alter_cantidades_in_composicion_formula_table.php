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
        Schema::table('composicion_formula', function (Blueprint $table) {
            $table->decimal('cantidad_nominal', 15, 4)->change();
            $table->decimal('cantidad_real', 15, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('composicion_formula', function (Blueprint $table) {
            $table->decimal('cantidad_nominal', 15, 2)->change();
            $table->decimal('cantidad_real', 15, 2)->change();
        });
    }
};
