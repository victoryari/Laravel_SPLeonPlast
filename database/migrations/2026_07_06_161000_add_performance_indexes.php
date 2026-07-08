<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // kardex — tabla más consultada del sistema
        Schema::table('kardex', function (Blueprint $table) {
            $table->index(['codigo_producto', 'codigo_almacen'], 'idx_kardex_producto_almacen');
            $table->index('fecha_movimiento', 'idx_kardex_fecha');
            $table->index(['documento', 'numero_documento'], 'idx_kardex_documento');
            $table->index('codigo_unidad_medida', 'idx_kardex_unidad');
        });

        // movimientos_inventario — segunda tabla más consultada
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->index(['codigo_producto', 'codigo_almacen'], 'idx_movinv_producto_almacen');
            $table->index(['documento_referencia', 'numero_referencia'], 'idx_movinv_referencia');
            $table->index('tipo_movimiento', 'idx_movinv_tipo');
            $table->index('idop', 'idx_movinv_idop');
        });

        // compras — filtrada por estado, documento, proveedor
        Schema::table('compras', function (Blueprint $table) {
            $table->index('estado', 'idx_compras_estado');
            $table->index(['serie_documento', 'numero_documento'], 'idx_compras_documento');
            $table->index('ruc_proveedor', 'idx_compras_ruc');
            $table->index('fecha_compra', 'idx_compras_fecha');
        });

        // detalle_compra — join crítico con compras
        Schema::table('detalle_compra', function (Blueprint $table) {
            $table->index('id_compra', 'idx_detalle_compra_id_compra');
            $table->index('codigo_producto', 'idx_detalle_compra_producto');
        });

        // orden_produccion_global — filtrada por estado y activo
        Schema::table('orden_produccion_global', function (Blueprint $table) {
            $table->index('estado', 'idx_opg_estado');
            $table->index('activo', 'idx_opg_activo');
            $table->index('codigo_op', 'idx_opg_codigo');
            $table->index('fecha', 'idx_opg_fecha');
        });

        // orden_proceso — join por idop
        Schema::table('orden_proceso', function (Blueprint $table) {
            $table->index('idop', 'idx_op_idop');
            $table->index('codigo_proceso', 'idx_op_proceso');
        });

        // componentes_orden_produccion_global — joins pesados
        Schema::table('componentes_orden_produccion_global', function (Blueprint $table) {
            $table->index('idop', 'idx_copg_idop');
            $table->index('id_proceso', 'idx_copg_id_proceso');
            $table->index('codigo_producto', 'idx_copg_producto');
            $table->index('codigo_formula_produccion', 'idx_copg_formula');
        });

        // mermas
        Schema::table('mermas', function (Blueprint $table) {
            $table->index('codigo_producto', 'idx_mermas_producto');
            $table->index('id_orden_produccion', 'idx_mermas_op');
            $table->index('codigo_almacen', 'idx_mermas_almacen');
        });

        // guia_remision_compras — filtrada por proveedor, estado
        Schema::table('guia_remision_compras', function (Blueprint $table) {
            $table->index('numero_guia', 'idx_grc_numero');
            $table->index('estado', 'idx_grc_estado');
        });
    }

    public function down(): void
    {
        Schema::table('kardex', function (Blueprint $table) {
            $table->dropIndex('idx_kardex_producto_almacen');
            $table->dropIndex('idx_kardex_fecha');
            $table->dropIndex('idx_kardex_documento');
            $table->dropIndex('idx_kardex_unidad');
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropIndex('idx_movinv_producto_almacen');
            $table->dropIndex('idx_movinv_referencia');
            $table->dropIndex('idx_movinv_tipo');
            $table->dropIndex('idx_movinv_idop');
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->dropIndex('idx_compras_estado');
            $table->dropIndex('idx_compras_documento');
            $table->dropIndex('idx_compras_ruc');
            $table->dropIndex('idx_compras_fecha');
        });

        Schema::table('detalle_compra', function (Blueprint $table) {
            $table->dropIndex('idx_detalle_compra_id_compra');
            $table->dropIndex('idx_detalle_compra_producto');
        });

        Schema::table('orden_produccion_global', function (Blueprint $table) {
            $table->dropIndex('idx_opg_estado');
            $table->dropIndex('idx_opg_activo');
            $table->dropIndex('idx_opg_codigo');
            $table->dropIndex('idx_opg_fecha');
        });

        Schema::table('orden_proceso', function (Blueprint $table) {
            $table->dropIndex('idx_op_idop');
            $table->dropIndex('idx_op_proceso');
        });

        Schema::table('componentes_orden_produccion_global', function (Blueprint $table) {
            $table->dropIndex('idx_copg_idop');
            $table->dropIndex('idx_copg_id_proceso');
            $table->dropIndex('idx_copg_producto');
            $table->dropIndex('idx_copg_formula');
        });

        Schema::table('mermas', function (Blueprint $table) {
            $table->dropIndex('idx_mermas_producto');
            $table->dropIndex('idx_mermas_op');
            $table->dropIndex('idx_mermas_almacen');
        });

        Schema::table('guia_remision_compras', function (Blueprint $table) {
            $table->dropIndex('idx_grc_numero');
            $table->dropIndex('idx_grc_estado');
        });
    }
};
