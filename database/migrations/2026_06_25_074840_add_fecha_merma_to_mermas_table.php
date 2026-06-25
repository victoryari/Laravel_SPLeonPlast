<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mermas', function (Blueprint $table) {
            $table->date('fecha_merma')->nullable()->after('id_merma');
        });
    }

    public function down()
    {
        Schema::table('mermas', function (Blueprint $table) {
            $table->dropColumn('fecha_merma');
        });
    }
};
