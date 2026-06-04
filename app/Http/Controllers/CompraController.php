<?php

namespace App\Http\Controllers;

use App\Models\{Compra, DetalleCompra, Proveedor, Producto, Almacen, UnidadMedida};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Auth};

class CompraController extends Controller
{
    public function index(Request $request) {
        $query = Compra::with(['datosProveedor', 'creador']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('numero_documento', 'LIKE', "%{$request->search}%")
                  ->orWhere('proveedor', 'LIKE', "%{$request->search}%")
                  ->orWhere('serie_documento', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->estado) {
            $query->where('estado', $request->estado);
        }

        $compras = $query->orderBy('fecha_creacion', 'desc')->paginate(15);
        $compras->appends(['search' => $request->search, 'estado' => $request->estado]);

        return view('compras.index', compact('compras'));
    }

    public function create() 
    {
        $proveedores = Proveedor::where('activo', 1)->get();
        $almacenes = Almacen::where('activo', 1)->get();
        $unidades_medida = UnidadMedida::where('estado', 1)->get();
        $guiasPendientes = \App\Models\GuiaRemisionCompra::whereIn('estado', ['RECIBIDA', 'UBICADA'])
            ->whereNull('id_compra')
            ->get();

        return view('compras.create', compact('proveedores', 'almacenes', 'unidades_medida', 'guiasPendientes'));
    }

    public function getGuiaAjax($id) 
    {
        $guia = \App\Models\GuiaRemisionCompra::with(['detalles.producto', 'datosProveedor'])->findOrFail($id);
        return response()->json($guia);
    }

    public function getGuiasMultiAjax(Request $request) 
    {
        $ids = $request->input('ids', []);
        $guias = \App\Models\GuiaRemisionCompra::with(['detalles.producto', 'datosProveedor'])
            ->whereIn('id_guia', $ids)
            ->get();
        return response()->json($guias);
    }

    public function show($id)
    {
        // Cargamos la compra con sus relaciones para evitar consultas extra en la vista
        $compra = Compra::with(['datosProveedor', 'detalles.producto', 'creador'])
            ->findOrFail($id);

        return view('compras.show', compact('compra'));
    }

    public function store(Request $request) {
        $request->validate([
            'tipo_documento' => 'required|string|max:50',
            'serie_documento' => 'required|string|max:20',
            'numero_documento' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('compras')->where(function ($query) use ($request) {
                    return $query->where('ruc_proveedor', $request->ruc_proveedor)
                                 ->where('serie_documento', strtoupper($request->serie_documento))
                                 ->where('tipo_documento', $request->tipo_documento);
                })
            ],
            'fecha_compra' => 'required|date',
            'ruc_proveedor' => 'required|string|max:20|exists:proveedores,ruc',
            'productos' => 'required|array|min:1',
            'productos.*.codigo' => 'required|string|max:50',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
            'productos.*.precio' => 'required|numeric|decimal:0,9|min:0',
            'productos.*.codigo_almacen' => 'required|string|max:20',
            'productos.*.codigo_unidad_medida' => 'nullable|string|max:10',
            'productos.*.lote' => 'nullable|string|max:50',
            'productos.*.fecha_vencimiento' => 'nullable|date',
            'moneda' => 'required|in:PEN,USD',
            'tipo_cambio' => 'nullable|numeric|min:0.001',
            'ids_guias' => 'nullable|array',
            'ids_guias.*' => 'exists:guia_remision_compras,id_guia'
        ]);

        try {
            DB::beginTransaction();
            $prov = Proveedor::where('ruc', $request->ruc_proveedor)->firstOrFail();

            $estadoCompra = !empty($request->ids_guias) ? 'RECIBIDA' : 'PENDIENTE';

            $compra = Compra::create([
                'tipo_documento'   => $request->tipo_documento,
                'serie_documento'  => strtoupper($request->serie_documento),
                'numero_documento' => $request->numero_documento,
                'proveedor'        => $prov->razon_social,
                'ruc_proveedor'    => $request->ruc_proveedor,
                'fecha_compra'     => $request->fecha_compra,
                'subtotal'         => $request->total_subtotal,
                'igv'              => $request->total_impuestos,
                'total'            => $request->total_general,
                'moneda'           => $request->moneda,
                'tipo_cambio'      => $request->moneda === 'USD' ? $request->tipo_cambio : 1.000,
                'estado'           => $estadoCompra,
                'usuario_creacion' => Auth::id()
            ]);

            if (!empty($request->ids_guias)) {
                \App\Models\GuiaRemisionCompra::whereIn('id_guia', $request->ids_guias)
                    ->update([
                        'id_compra' => $compra->id_compra,
                        'estado' => 'FACTURADA'
                    ]);
            }

            foreach ($request->productos as $item) {
                $prod = Producto::where('codigo', $item['codigo'])->first();
                $sub = $item['cantidad'] * $item['precio'];
                $igv_item = $sub * 0.18;
                
                DetalleCompra::create([
                    'id_compra' => $compra->id_compra,
                    'codigo_producto' => $item['codigo'],
                    'descripcion_producto' => $prod->descripcion ?? '',
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'codigo_unidad_medida' => $item['codigo_unidad_medida'] ?? null,
                    'subtotal' => $sub,
                    'igv' => $igv_item,
                    'total' => $sub + $igv_item,
                    'codigo_almacen' => $item['codigo_almacen'],
                    'lote' => $item['lote'] ?? null,
                    'fecha_vencimiento' => $item['fecha_vencimiento'] ?? null
                ]);
            }
            DB::commit();

            if (!empty($request->ids_guias)) {
                $kardexService = app(\App\Services\KardexService::class);
                $guiasToUpdate = \App\Models\GuiaRemisionCompra::whereIn('id_guia', $request->ids_guias)->get();
                foreach ($guiasToUpdate as $guia) {
                    foreach ($request->productos as $item) {
                        DB::table('kardex')
                            ->where('numero_documento', $guia->numero_guia)
                            ->where('tipo_movimiento', 'INGRESO')
                            ->where('codigo_producto', $item['codigo'])
                            ->update([
                                'costo_entrada' => $item['precio']
                            ]);
                            
                        $almacenesAfectados = DB::table('kardex')
                            ->where('numero_documento', $guia->numero_guia)
                            ->where('codigo_producto', $item['codigo'])
                            ->pluck('codigo_almacen')
                            ->unique();
                            
                        foreach ($almacenesAfectados as $almacen) {
                            $kardexService->recalcular($item['codigo'], $almacen);
                        }
                    }
                }
            }

            return redirect()->route('compras.index')->with('success', 'Compra registrada y Kardex actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id) {
        $compra = Compra::with('detalles')->findOrFail($id);
        $proveedores = Proveedor::where('activo', 1)->get();
        $almacenes = Almacen::where('activo', 1)->get();
        $unidades_medida = UnidadMedida::where('estado', 1)->get();
        return view('compras.edit', compact('compra', 'proveedores', 'almacenes', 'unidades_medida'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'tipo_documento' => 'required|string|max:50',
            'serie_documento' => 'required|string|max:20',
            'numero_documento' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('compras')->where(function ($query) use ($request) {
                    return $query->where('ruc_proveedor', $request->ruc_proveedor)
                                 ->where('serie_documento', strtoupper($request->serie_documento))
                                 ->where('tipo_documento', $request->tipo_documento);
                })->ignore($id, 'id_compra')
            ],
            'fecha_compra' => 'required|date',
            'ruc_proveedor' => 'required|string|max:20|exists:proveedores,ruc',
            'productos' => 'required|array|min:1',
            'productos.*.codigo' => 'required|string|max:50',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
            'productos.*.precio' => 'required|numeric|decimal:0,9|min:0',
            'productos.*.codigo_almacen' => 'required|string|max:20',
            'productos.*.codigo_unidad_medida' => 'nullable|string|max:10',
            'productos.*.lote' => 'nullable|string|max:50',
            'productos.*.fecha_vencimiento' => 'nullable|date',
            'moneda' => 'required|in:PEN,USD',
            'tipo_cambio' => 'nullable|numeric|min:0.001',
        ]);

        try {
            DB::beginTransaction();
            $compra = Compra::findOrFail($id);

            if ($compra->estado !== 'PENDIENTE') {
                return back()->with('error', 'Solo se pueden editar compras en estado PENDIENTE.');
            }

            $prov = Proveedor::where('ruc', $request->ruc_proveedor)->firstOrFail();

            $compra->update([
                'tipo_documento' => $request->tipo_documento,
                'serie_documento' => strtoupper($request->serie_documento),
                'numero_documento' => $request->numero_documento,
                'fecha_compra' => $request->fecha_compra,
                'proveedor' => $prov->razon_social,
                'ruc_proveedor' => $request->ruc_proveedor,
                'subtotal' => $request->total_subtotal,
                'igv' => $request->total_impuestos,
                'total' => $request->total_general,
                'moneda' => $request->moneda,
                'tipo_cambio' => $request->moneda === 'USD' ? $request->tipo_cambio : 1.000,
                'usuario_aprobacion' => Auth::id(),
            ]);

            DetalleCompra::where('id_compra', $id)->delete();
            foreach ($request->productos as $item) {
                $prod = Producto::where('codigo', $item['codigo'])->first();
                $sub = $item['cantidad'] * $item['precio'];
                DetalleCompra::create([
                    'id_compra' => $id,
                    'codigo_producto' => $item['codigo'],
                    'descripcion_producto' => $prod->descripcion ?? '',
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'codigo_unidad_medida' => $item['codigo_unidad_medida'] ?? null,
                    'subtotal' => $sub,
                    'igv' => $sub * 0.18,
                    'total' => $sub * 1.18,
                    'codigo_almacen' => $item['codigo_almacen'],
                    'lote' => $item['lote'] ?? null,
                    'fecha_vencimiento' => $item['fecha_vencimiento'] ?? null
                ]);
            }
            DB::commit();

            if ($compra->guias && $compra->guias->count() > 0) {
                $kardexService = app(\App\Services\KardexService::class);
                foreach ($compra->guias as $guia) {
                    foreach ($request->productos as $item) {
                        DB::table('kardex')
                            ->where('numero_documento', $guia->numero_guia)
                            ->where('tipo_movimiento', 'INGRESO')
                            ->where('codigo_producto', $item['codigo'])
                            ->update([
                                'costo_entrada' => $item['precio']
                            ]);
                            
                        $almacenesAfectados = DB::table('kardex')
                            ->where('numero_documento', $guia->numero_guia)
                            ->where('codigo_producto', $item['codigo'])
                            ->pluck('codigo_almacen')
                            ->unique();
                            
                        foreach ($almacenesAfectados as $almacen) {
                            $kardexService->recalcular($item['codigo'], $almacen);
                        }
                    }
                }
            }

            return redirect()->route('compras.index')->with('success', 'Actualizado y Kardex recalculado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function anular(Request $request, $id) {
        try {
            DB::beginTransaction();
            $compra = Compra::findOrFail($id);

            if ($compra->estado !== 'PENDIENTE') {
                return back()->with('error', 'Solo se pueden anular compras en estado PENDIENTE.');
            }

            $compra->update([
                'estado' => 'CANCELADA',
                'motivo_anulacion' => $request->confirmacion ?? 'Anulación solicitada',
                'usuario_aprobacion' => Auth::id(),
            ]);
            DB::commit();
            return redirect()->route('compras.index')->with('success', 'Compra anulada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}