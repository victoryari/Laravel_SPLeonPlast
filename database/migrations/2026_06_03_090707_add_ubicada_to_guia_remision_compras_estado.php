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
        DB::statement("ALTER TABLE guia_remision_compras MODIFY COLUMN estado ENUM('RECIBIDA', 'FACTURADA', 'ANULADA', 'UBICADA') NOT NULL DEFAULT 'RECIBIDA'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE guia_remision_compras MODIFY COLUMN estado ENUM('RECIBIDA', 'FACTURADA', 'ANULADA') NOT NULL DEFAULT 'RECIBIDA'");
    }
};
