<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE compras MODIFY COLUMN tipo_documento ENUM('FACTURA','BOLETA','RECIBO_HONORARIOS','NOTA_CREDITO','NOTA_DEBITO','GUIA_REMISION','DAM','OTRO','GUIA','TICKET') NOT NULL DEFAULT 'FACTURA'");
        DB::statement("ALTER TABLE compras MODIFY COLUMN estado ENUM('PENDIENTE','RECIBIDA','PARCIAL','COMPLETADA','CANCELADA','ANULADA') NOT NULL DEFAULT 'PENDIENTE'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE compras MODIFY COLUMN tipo_documento ENUM('FACTURA','BOLETA','RECIBO_HONORARIOS','NOTA_CREDITO','NOTA_DEBITO','GUIA_REMISION','DAM','OTRO') NOT NULL DEFAULT 'FACTURA'");
        DB::statement("ALTER TABLE compras MODIFY COLUMN estado ENUM('PENDIENTE','RECIBIDA','PARCIAL','COMPLETADA','CANCELADA') NOT NULL DEFAULT 'PENDIENTE'");
    }
};
