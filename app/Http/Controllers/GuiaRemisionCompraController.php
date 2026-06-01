<?php

namespace App\Http\Controllers;

use App\Models\GuiaRemisionCompra;
use App\Models\DetalleGuiaCompra;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Services\KardexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GuiaRemisionCompraController extends Controller
{
    public function index()
    {
        $guias = GuiaRemisionCompra::with('datosProveedor', 'creador')
            ->orderBy('fecha_registro', 'desc')
            ->paginate(15);

        return view('guia_compras.index', compact('guias'));
    }

    public function create()
    {
        $proveedores = Proveedor::where('activo', 1)->get();
        $productos = Producto::where('activo', 1)->get();

        return view('guia_compras.create', compact('proveedores', 'productos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proveedor' => 'required|string|max:100',
            'ruc_proveedor' => 'nullable|string|max:11',
            'numero_guia' => 'required|string|max:20',
            'fecha_emision' => 'required|date',
            'productos' => 'required|array|min:1',
            'productos.*.codigo_producto' => 'required|string',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
            'productos.*.lote' => 'nullable|string|max:50',
            'productos.*.fecha_vencimiento' => 'nullable|date',
            'observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // 1. Guardar la Guía
            $guia = GuiaRemisionCompra::create([
                'proveedor' => $request->proveedor,
                'ruc_proveedor' => $request->ruc_proveedor,
                'numero_guia' => $request->numero_guia,
                'fecha_emision' => $request->fecha_emision,
                'estado' => 'RECIBIDA',
                'observaciones' => $request->observaciones,
                'usuario_registro' => Auth::id()
            ]);

            $kardexService = app(KardexService::class);

            // 2. Guardar Detalles e Ingresar al Kardex
            foreach ($request->productos as $item) {
                $producto = Producto::where('codigo', $item['codigo_producto'])->first();
                $almacen_destino = 'P2A'; // ALMACEN COMPRAS NAC/IMP

                $detalle = DetalleGuiaCompra::create([
                    'id_guia' => $guia->id_guia,
                    'codigo_producto' => $item['codigo_producto'],
                    'descripcion_producto' => $producto->descripcion ?? '',
                    'cantidad' => $item['cantidad'],
                    'codigo_unidad_medida' => $producto->unidad_medida_codigo ?? 'NIU',
                    'codigo_almacen' => $almacen_destino,
                    'lote' => $item['lote'] ?? null,
                    'fecha_vencimiento' => $item['fecha_vencimiento'] ?? null
                ]);

                // 3. Obtener el Costo Promedio Actual para valorizar la entrada de la Guía
                $ultimoKardex = DB::table('kardex')
                    ->where('codigo_producto', $item['codigo_producto'])
                    ->where('codigo_almacen', $almacen_destino)
                    ->orderBy('id_kardex', 'desc')
                    ->first();
                
                $costoPromedioActual = $ultimoKardex?->costo_promedio ?? 0;

                // Si es el primer ingreso absoluto y el costo promedio es 0, usamos 0.
                // Cuando llegue la Factura vinculada, se regularizará si es necesario o en la refacturación.

                // 4. Calcular Costos usando KardexService
                $costos = $kardexService->calcularCostos(
                    $item['codigo_producto'], 
                    $almacen_destino,
                    $item['cantidad'], // cantidad que entra
                    $costoPromedioActual, // precio de entrada (usamos el promedio actual para no alterar el promedio)
                    0, // cantidad salida
                    0  // saldo actual
                );

                $nuevo_saldo = $costos['cantidad_saldo'];

                // 5. Actualizar tabla Inventario (stock físico)
                $inventario = DB::table('inventario')
                    ->where('codigo_producto', $item['codigo_producto'])
                    ->where('codigo_almacen', $almacen_destino)
                    ->first();

                if ($inventario) {
                    DB::table('inventario')
                        ->where('id_inventario', $inventario->id_inventario)
                        ->update([
                            'stock_actual' => $nuevo_saldo,
                            'costo_promedio' => $costos['costo_promedio'],
                            'fecha_ultimo_movimiento' => now(),
                            'usuario_ultimo_movimiento' => Auth::id()
                        ]);
                } else {
                    DB::table('inventario')->insert([
                        'codigo_producto' => $item['codigo_producto'],
                        'codigo_almacen' => $almacen_destino,
                        'codigo_unidad_medida' => $producto->unidad_medida_codigo ?? 'NIU',
                        'stock_actual' => $nuevo_saldo,
                        'stock_minimo' => 0,
                        'costo_promedio' => $costos['costo_promedio'],
                        'fecha_ultimo_movimiento' => now(),
                        'usuario_ultimo_movimiento' => Auth::id()
                    ]);
                }

                // 6. Registrar en Kardex
                DB::table('kardex')->insert([
                    'codigo_producto'      => $item['codigo_producto'],
                    'codigo_almacen'       => $almacen_destino,
                    'codigo_unidad_medida' => $producto->unidad_medida_codigo ?? 'NIU',
                    'fecha_movimiento'     => now(),
                    'tipo_movimiento'      => 'INGRESO',
                    'documento'            => 'GUIA REMISION',
                    'numero_documento'     => $guia->numero_guia,
                    'cantidad_entrada'     => $item['cantidad'],
                    'costo_entrada'        => $costoPromedioActual,
                    'total_entrada'        => $item['cantidad'] * $costoPromedioActual,
                    'cantidad_salida'      => 0,
                    'costo_salida'         => 0,
                    'total_salida'         => 0,
                    'cantidad_saldo'       => $nuevo_saldo,
                    'costo_promedio'       => $costos['costo_promedio'],
                    'total_saldo'          => $costos['total_saldo'],
                    'lote'                 => $item['lote'] ?? null,
                    'usuario_registro'     => Auth::id()
                ]);
            }

            DB::commit();
            return redirect()->route('guia_compras.index')->with('success', 'Guía de Remisión registrada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error al registrar guía: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $guia = GuiaRemisionCompra::with('detalles.producto', 'creador', 'datosProveedor', 'compras')->findOrFail($id);
        return view('guia_compras.show', compact('guia'));
    }
}
