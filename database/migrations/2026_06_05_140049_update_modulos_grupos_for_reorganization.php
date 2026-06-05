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
        DB::table('modulos')->where('nombre', 'Proveedores')->update(['grupo' => 'Cuentas por Pagar']);
        DB::table('modulos')->where('nombre', 'Compras')->update(['grupo' => 'Cuentas por Pagar']);
        DB::table('modulos')->where('nombre', 'Kardex de Movimientos')->update(['grupo' => 'Contabilidad']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('modulos')->where('nombre', 'Proveedores')->update(['grupo' => 'Tablas Maestras']);
        DB::table('modulos')->where('nombre', 'Compras')->update(['grupo' => 'Principal']);
        DB::table('modulos')->where('nombre', 'Kardex de Movimientos')->update(['grupo' => 'Inventario']);
    }
};
