<?php

namespace App\Http\Controllers;

use App\Models\{RequerimientoMaterial, DetalleRequerimientoMaterial, DespachoRequerimientoLote, Producto, Almacen, OrdenProduccion};
use App\Services\KardexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Auth};

class RequerimientoMaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = RequerimientoMaterial::with(['detalles', 'creador']);

        if ($request->codigo) {
            $query->where('codigo', 'LIKE', "%{$request->codigo}%");
        }

        if ($request->estado) {
            $query->where('estado', $request->estado);
        }

        if ($request->fecha_desde) {
            $query->where('fecha_creacion', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->fecha_hasta) {
            $query->where('fecha_creacion', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        $requerimientos = $query->orderBy('fecha_creacion', 'desc')->paginate(15);
        $requerimientos->appends($request->all());

        $estados = ['BORRADOR', 'PENDIENTE', 'APROBADO', 'RECHAZADO', 'ATENDIDO_PARCIAL', 'ATENDIDO_TOTAL', 'ANULADO'];

        return view('requerimientos_materiales.index', compact('requerimientos', 'estados'));
    }

    public function create()
    {
        $almacenes = Almacen::where('activo', 1)->get();
        $ordenes = OrdenProduccion::where('estado', '!=', 'CANCELADO')->get();

        return view('requerimientos_materiales.create', compact('almacenes', 'ordenes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'idop' => 'nullable|exists:orden_produccion_global,idop',
            'id_proceso' => 'nullable|exists:orden_proceso,id',
            'motivo' => 'nullable|string|max:500',
            'observaciones' => 'nullable|string',
            'productos' => 'required|array|min:1',
            'productos.*.codigo_producto' => 'required|string|exists:producto,codigo',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
            'productos.*.observaciones' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $ultimo = RequerimientoMaterial::max('id_requerimiento') ?? 0;
            $codigo = 'REQ-' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);

            $requerimiento = RequerimientoMaterial::create([
                'codigo' => $codigo,
                'idop' => $request->idop,
                'id_proceso' => $request->id_proceso,
                'motivo' => $request->motivo,
                'estado' => 'BORRADOR',
                'usuario_creacion' => Auth::id(),
                'observaciones' => $request->observaciones,
            ]);

            foreach ($request->productos as $item) {
                DetalleRequerimientoMaterial::create([
                    'id_requerimiento' => $requerimiento->id_requerimiento,
                    'codigo_producto' => $item['codigo_producto'],
                    'codigo_almacen_origen' => null,
                    'codigo_almacen_destino' => null,
                    'cantidad_solicitada' => $item['cantidad'],
                    'lote_preferente' => null,
                    'observaciones' => $item['observaciones'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('requerimientos_materiales.show', $requerimiento->id_requerimiento)
                ->with('success', 'Requerimiento creado como BORRADOR.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear requerimiento: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $requerimiento = RequerimientoMaterial::with([
            'detalles.producto',
            'detalles.almacenOrigen',
            'detalles.almacenDestino',
            'ordenProduccion',
            'creador',
            'aprobador',
            'despachosLotes',
        ])->findOrFail($id);

        return view('requerimientos_materiales.show', compact('requerimiento'));
    }

    public function edit($id)
    {
        $requerimiento = RequerimientoMaterial::with('detalles')->findOrFail($id);

        if ($requerimiento->estado !== 'BORRADOR') {
            return back()->with('error', 'Solo se pueden editar requerimientos en estado BORRADOR.');
        }

        $almacenes = Almacen::where('activo', 1)->get();
        $ordenes = OrdenProduccion::where('estado', '!=', 'CANCELADO')->get();

        return view('requerimientos_materiales.edit', compact('requerimiento', 'almacenes', 'ordenes'));
    }

    public function update(Request $request, $id)
    {
        $requerimiento = RequerimientoMaterial::findOrFail($id);

        if ($requerimiento->estado !== 'BORRADOR') {
            return back()->with('error', 'Solo se pueden editar requerimientos en estado BORRADOR.');
        }

        $request->validate([
            'idop' => 'nullable|exists:orden_produccion_global,idop',
            'id_proceso' => 'nullable|exists:orden_proceso,id',
            'motivo' => 'nullable|string|max:500',
            'observaciones' => 'nullable|string',
            'productos' => 'required|array|min:1',
            'productos.*.codigo_producto' => 'required|string|exists:producto,codigo',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
            'productos.*.observaciones' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $requerimiento->update([
                'idop' => $request->idop,
                'id_proceso' => $request->id_proceso,
                'motivo' => $request->motivo,
                'observaciones' => $request->observaciones,
            ]);

            $requerimiento->detalles()->delete();

            foreach ($request->productos as $item) {
                DetalleRequerimientoMaterial::create([
                    'id_requerimiento' => $requerimiento->id_requerimiento,
                    'codigo_producto' => $item['codigo_producto'],
                    'codigo_almacen_origen' => null,
                    'codigo_almacen_destino' => null,
                    'cantidad_solicitada' => $item['cantidad'],
                    'lote_preferente' => null,
                    'observaciones' => $item['observaciones'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('requerimientos_materiales.show', $id)
                ->with('success', 'Requerimiento actualizado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar requerimiento: ' . $e->getMessage());
        }
    }

    public function enviar($id)
    {
        $requerimiento = RequerimientoMaterial::with('detalles')->findOrFail($id);

        if ($requerimiento->estado !== 'BORRADOR') {
            return back()->with('error', 'Solo se pueden enviar requerimientos en estado BORRADOR.');
        }

        if ($requerimiento->detalles->sum('cantidad_solicitada') <= 0) {
            return back()->with('error', 'El requerimiento debe tener al menos una línea con cantidad mayor a 0.');
        }

        $requerimiento->update(['estado' => 'PENDIENTE']);

        return redirect()->route('requerimientos_materiales.show', $id)
            ->with('success', 'Requerimiento enviado a aprobación.');
    }

    public function aprobar($id)
    {
        $requerimiento = RequerimientoMaterial::findOrFail($id);

        if ($requerimiento->estado !== 'PENDIENTE') {
            return back()->with('error', 'Solo se pueden aprobar requerimientos en estado PENDIENTE.');
        }

        $requerimiento->update([
            'estado' => 'APROBADO',
            'usuario_aprobacion' => Auth::id(),
            'fecha_aprobacion' => now(),
        ]);

        return redirect()->route('requerimientos_materiales.show', $id)
            ->with('success', 'Requerimiento aprobado.');
    }

    public function rechazar(Request $request, $id)
    {
        $requerimiento = RequerimientoMaterial::findOrFail($id);

        if ($requerimiento->estado !== 'PENDIENTE') {
            return back()->with('error', 'Solo se pueden rechazar requerimientos en estado PENDIENTE.');
        }

        $request->validate([
            'observaciones' => 'required|string|min:10',
        ]);

        $requerimiento->update([
            'estado' => 'RECHAZADO',
            'usuario_aprobacion' => Auth::id(),
            'fecha_aprobacion' => now(),
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('requerimientos_materiales.show', $id)
            ->with('success', 'Requerimiento rechazado.');
    }

    public function anular($id)
    {
        $requerimiento = RequerimientoMaterial::findOrFail($id);

        if (in_array($requerimiento->estado, ['ATENDIDO_TOTAL', 'ANULADO'])) {
            return back()->with('error', 'No se puede anular un requerimiento en este estado.');
        }

        $requerimiento->update(['estado' => 'ANULADO']);

        return redirect()->route('requerimientos_materiales.show', $id)
            ->with('success', 'Requerimiento anulado.');
    }
}
