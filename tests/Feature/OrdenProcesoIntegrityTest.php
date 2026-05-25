<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrdenProcesoIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('users')->insert([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        DB::table('orden_produccion_global')->insert([
            'idop' => 1,
            'codigo_op' => 'OP-2025-001',
            'codigo_producto_proceso' => 1,
            'codigo_centro_trabajo_produccion' => 'CT-01',
            'fecha' => now(),
            'cantidad' => 100,
            'activo' => 1,
            'estado' => 'PENDIENTE',
        ]);

        DB::table('proceso_produccion')->insert([
            ['codigo' => 1, 'descripcion' => 'Actividad', 'estado' => 1],
            ['codigo' => 99, 'descripcion' => 'Proceso Produccion', 'estado' => 1],
        ]);

        DB::table('orden_proceso')->insert([
            'id' => 1,
            'idop' => 1,
            'secuencia' => 10,
            'codigo_proceso' => '1',
            'descripcion_proceso' => 'Proceso Test',
            'estado' => 1,
            'estado_avance' => 'PENDIENTE',
            'fecha_inicio' => now(),
        ]);

        DB::table('producto')->insert([
            ['codigo' => 'PROD-001', 'descripcion' => 'Producto Test', 'codigo_tipo_producto' => 'MTP', 'estado' => 1],
            ['codigo' => 'ACT-001', 'descripcion' => 'Actividad Test', 'codigo_tipo_producto' => 'ACT', 'estado' => 1],
            ['codigo' => 'MZ07-001', 'descripcion' => 'Mezclado Test', 'codigo_tipo_producto' => 'PEP', 'estado' => 1],
        ]);

        DB::table('tipo_producto')->insert([
            ['codigo' => 'MTP', 'descripcion' => 'MATERIA PRIMA'],
            ['codigo' => 'PEP', 'descripcion' => 'PRODUCTO EN PROCESO'],
            ['codigo' => 'ACT', 'descripcion' => 'ACTIVIDAD'],
        ]);

        DB::table('unidad_medida')->insert([
            ['codigo' => 'KG', 'descripcion' => 'KILOGRAMOS'],
            ['codigo' => 'UNI', 'descripcion' => 'UNIDADES'],
        ]);

        DB::table('almacen')->insert([
            ['codigo_almacen' => 'ALM-01', 'descripcion' => 'Almacen Test', 'tipo_almacen' => 'MATERIA_PRIMA', 'activo' => 1],
        ]);

        DB::table('inventario')->insert([
            'id_inventario' => 1,
            'codigo_almacen' => 'ALM-01',
            'codigo_producto' => 'PROD-001',
            'lote' => 'LOTE-001',
            'stock_actual' => 100,
            'costo_promedio' => 10,
            'estado' => 1,
        ]);
    }

    public function test_store_componentes_stock_insuficiente_lanza_excepcion(): void
    {
        DB::table('orden_proceso')->insert([
            'id' => 10,
            'idop' => 1,
            'secuencia' => 200,
            'codigo_proceso' => '99',
            'descripcion_proceso' => 'Stock Test',
            'estado' => 1,
            'estado_avance' => 'PENDIENTE',
            'fecha_inicio' => now(),
        ]);

        $user = \App\Models\User::find(1);
        $this->actingAs($user);

        $response = $this->post(route('ordenes.procesos.componentes.store', ['orden' => 1, 'proceso' => 10]), [
            'componentes_json' => json_encode([
                [
                    'codigo_producto' => 'PROD-001',
                    'codigo_tipo_producto' => 'MTP',
                    'cantidad' => 999999,
                    'codigo_unidad_medida' => 'KG',
                    'fecha_inicio' => now()->toDateString(),
                    'fecha_fin' => now()->toDateString(),
                    'hora_inicio' => '08:00',
                    'hora_fin' => '17:00',
                ],
            ]),
            'merma_kg' => 0,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('STOCK INSUFICIENTE', session('error'));
    }

    public function test_store_componentes_merma_mayor_que_insumos_lanza_excepcion(): void
    {
        DB::table('orden_proceso')->insert([
            'id' => 11,
            'idop' => 1,
            'secuencia' => 210,
            'codigo_proceso' => '99',
            'descripcion_proceso' => 'Merma Test',
            'estado' => 1,
            'estado_avance' => 'PENDIENTE',
            'fecha_inicio' => now(),
        ]);

        $user = \App\Models\User::find(1);
        $this->actingAs($user);

        $response = $this->post(route('ordenes.procesos.componentes.store', ['orden' => 1, 'proceso' => 11]), [
            'componentes_json' => json_encode([
                [
                    'codigo_producto' => 'PROD-001',
                    'codigo_tipo_producto' => 'MTP',
                    'cantidad' => 10,
                    'codigo_unidad_medida' => 'KG',
                    'fecha_inicio' => now()->toDateString(),
                    'fecha_fin' => now()->toDateString(),
                    'hora_inicio' => '08:00',
                    'hora_fin' => '17:00',
                ],
            ]),
            'merma_kg' => 50,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('merma', session('error'));
    }

    public function test_destroy_proceso_completado_lanza_excepcion(): void
    {
        DB::table('orden_proceso')->where('id', 1)->update(['estado_avance' => 'COMPLETADO']);

        $user = \App\Models\User::find(1);
        $this->actingAs($user);

        $response = $this->delete(route('ordenes.procesos.destroy', ['proceso' => 1]));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('COMPLETADO', session('error'));
    }

    public function test_finalizar_sin_componentes_lanza_error(): void
    {
        $user = \App\Models\User::find(1);
        $this->actingAs($user);

        $response = $this->post(route('ordenes.procesos.finalizar', ['orden' => 1, 'proceso' => 1]));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Debe registrar al menos', session('error'));
    }

    public function test_actividades_se_registran_sin_inventario(): void
    {
        $user = \App\Models\User::find(1);
        $this->actingAs($user);

        $response = $this->post(route('ordenes.procesos.componentes.store', ['orden' => 1, 'proceso' => 1]), [
            'componentes_json' => json_encode([
                [
                    'codigo_producto' => 'ACT-001',
                    'codigo_tipo_producto' => 'ACT',
                    'cantidad' => 1,
                    'codigo_trabajador' => null,
                    'fecha_inicio' => now()->toDateString(),
                    'fecha_fin' => now()->toDateString(),
                    'hora_inicio' => '08:00',
                    'hora_fin' => '17:00',
                ],
            ]),
            'merma_kg' => 0,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('componentes_orden_produccion_global', [
            'id_proceso' => 1,
            'codigo_tipo_producto' => 'ACT',
            'estado' => 1,
        ]);
    }

    public function test_determinar_pep_desde_formula(): void
    {
        DB::table('formula_produccion')->insert([
            'codigo' => 'F-TEST',
            'descripcion' => 'Formula de prueba',
            'codigo_producto_resultante' => 'MZ07-001',
            'estado' => 1,
        ]);

        DB::table('color')->insert([
            'codigo' => 'ROJO',
            'descripcion' => 'Rojo',
            'activo' => 1,
        ]);

        DB::table('orden_proceso')->insert([
            'id' => 2,
            'idop' => 1,
            'secuencia' => 20,
            'codigo_proceso' => '99',
            'descripcion_proceso' => 'Proceso PEP Test',
            'estado' => 1,
            'estado_avance' => 'PENDIENTE',
            'fecha_inicio' => now(),
        ]);

        DB::table('inventario')->where('id_inventario', 1)->update(['stock_actual' => 999999]);

        $user = \App\Models\User::find(1);
        $this->actingAs($user);

        $response = $this->post(route('ordenes.procesos.componentes.store', ['orden' => 1, 'proceso' => 2]), [
            'componentes_json' => json_encode([
                [
                    'codigo_producto' => 'PROD-001',
                    'codigo_tipo_producto' => 'MTP',
                    'cantidad' => 50,
                    'codigo_unidad_medida' => 'KG',
                    'codigo_formula' => 'F-TEST',
                    'codigo_color' => 'ROJO',
                    'codigo_centro_trabajo' => null,
                    'codigo_molde' => null,
                    'codigo_trabajador' => null,
                    'fecha_inicio' => now()->toDateString(),
                    'fecha_fin' => now()->toDateString(),
                    'hora_inicio' => '08:00',
                    'hora_fin' => '17:00',
                ],
            ]),
            'merma_kg' => 0,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('produccion_ingresos_proceso', [
            'id_proceso' => 2,
            'codigo_producto_proceso' => 'MZ07-001',
            'estado' => 'PENDIENTE',
        ]);
    }
}
