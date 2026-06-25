<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('requerimientos_materiales', function (Blueprint $table) {
            $table->date('fecha_requerimiento')->nullable()->after('codigo');
        });
    }

    public function down()
    {
        Schema::table('requerimientos_materiales', function (Blueprint $table) {
            $table->dropColumn('fecha_requerimiento');
        });
    }
};
