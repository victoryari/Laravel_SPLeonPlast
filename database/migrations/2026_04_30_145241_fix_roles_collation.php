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
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE roles CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE modulos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE rol_modulo CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
