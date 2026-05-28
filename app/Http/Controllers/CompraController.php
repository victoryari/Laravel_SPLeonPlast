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

        return view('compras.create', compact('proveedores', 'almacenes', 'unidades_medida'));
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
            'numero_documento' => 'required|string|max:50',
            'fecha_compra' => 'required|date',
            'ruc_proveedor' => 'required|string|max:20|exists:proveedores,ruc',
            'productos' => 'required|array|min:1',
            'productos.*.codigo' => 'required|string|max:50',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.codigo_almacen' => 'required|string|max:20',
            'productos.*.codigo_unidad_medida' => 'nullable|string|max:10',
            'moneda' => 'required|in:PEN,USD',
            'tipo_cambio' => 'nullable|numeric|min:0.001',
        ]);

        try {
            DB::beginTransaction();
            $prov = Proveedor::where('ruc', $request->ruc_proveedor)->firstOrFail();

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
                'estado'           => 'PENDIENTE',
                'usuario_creacion' => Auth::id()
            ]);

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
                    'codigo_almacen' => $item['codigo_almacen']
                ]);
            }
            DB::commit();
            return redirect()->route('compras.index')->with('success', 'Compra registrada.');
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
            'numero_documento' => 'required|string|max:50',
            'fecha_compra' => 'required|date',
            'ruc_proveedor' => 'required|string|max:20|exists:proveedores,ruc',
            'productos' => 'required|array|min:1',
            'productos.*.codigo' => 'required|string|max:50',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.codigo_almacen' => 'required|string|max:20',
            'productos.*.codigo_unidad_medida' => 'nullable|string|max:10',
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
                    'codigo_almacen' => $item['codigo_almacen']
                ]);
            }
            DB::commit();
            return redirect()->route('compras.index')->with('success', 'Actualizado.');
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