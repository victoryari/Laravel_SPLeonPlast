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



        $fecha_desde = $request->input('fecha_desde', now()->startOfMonth()->toDateString());
        $fecha_hasta = $request->input('fecha_hasta', now()->endOfMonth()->toDateString());

        $query->where('fecha_creacion', '>=', $fecha_desde . ' 00:00:00');
        $query->where('fecha_creacion', '<=', $fecha_hasta . ' 23:59:59');

        $requerimientos = $query->orderBy('fecha_creacion', 'desc')->paginate(15);
        $requerimientos->appends($request->all());

        $estados = ['BORRADOR', 'PENDIENTE', 'APROBADO', 'RECHAZADO', 'ATENDIDO_PARCIAL', 'ATENDIDO_TOTAL', 'ANULADO'];

        return view('requerimientos_materiales.index', compact('requerimientos', 'estados', 'fecha_desde', 'fecha_hasta'));
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
            $ultimo = RequerimientoMaterial::lockForUpdate()->max('id_requerimiento') ?? 0;
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

        try {
            DB::beginTransaction();
            $requerimiento = RequerimientoMaterial::where('id_requerimiento', $id)->lockForUpdate()->firstOrFail();

            if ($requerimiento->estado !== 'BORRADOR') {
                throw new \Exception('Solo se pueden editar requerimientos en estado BORRADOR.');
            }

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
        try {
            DB::beginTransaction();
            $requerimiento = RequerimientoMaterial::where('id_requerimiento', $id)->lockForUpdate()->firstOrFail();

            if ($requerimiento->estado !== 'BORRADOR') {
                throw new \Exception('Solo se pueden enviar requerimientos en estado BORRADOR.');
            }

            $totalCantidad = DB::table('detalle_requerimientos_materiales')
                ->where('id_requerimiento', $id)
                ->sum('cantidad_solicitada');

            if ($totalCantidad <= 0) {
                throw new \Exception('El requerimiento debe tener al menos una línea con cantidad mayor a 0.');
            }

            $requerimiento->update(['estado' => 'PENDIENTE']);

            DB::commit();
            return redirect()->route('requerimientos_materiales.show', $id)
                ->with('success', 'Requerimiento enviado a aprobación.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function aprobar($id)
    {
        try {
            DB::beginTransaction();
            $requerimiento = RequerimientoMaterial::where('id_requerimiento', $id)->lockForUpdate()->firstOrFail();

            if ($requerimiento->estado !== 'PENDIENTE') {
                throw new \Exception('Solo se pueden aprobar requerimientos en estado PENDIENTE.');
            }

            $requerimiento->update([
                'estado' => 'APROBADO',
                'usuario_aprobacion' => Auth::id(),
                'fecha_aprobacion' => now(),
            ]);

            DB::commit();
            return redirect()->route('requerimientos_materiales.show', $id)
                ->with('success', 'Requerimiento aprobado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'observaciones' => 'required|string|min:10',
        ]);

        try {
            DB::beginTransaction();
            $requerimiento = RequerimientoMaterial::where('id_requerimiento', $id)->lockForUpdate()->firstOrFail();

            if (!in_array($requerimiento->estado, ['PENDIENTE', 'APROBADO'])) {
                throw new \Exception('Solo se pueden rechazar requerimientos en estado PENDIENTE o APROBADO.');
            }

            $requerimiento->update([
                'estado' => 'RECHAZADO',
                'usuario_aprobacion' => Auth::id(),
                'fecha_aprobacion' => now(),
                'observaciones' => $request->observaciones,
            ]);

            DB::commit();
            return redirect()->route('requerimientos_materiales.show', $id)
                ->with('success', 'Requerimiento rechazado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function anular($id)
    {
        try {
            DB::beginTransaction();

            $requerimiento = RequerimientoMaterial::where('id_requerimiento', $id)->lockForUpdate()->firstOrFail();

            if (in_array($requerimiento->estado, ['ATENDIDO_TOTAL', 'ATENDIDO_PARCIAL', 'ANULADO'])) {
                throw new \Exception('No se puede anular un requerimiento en estado ' . $requerimiento->estado . '.');
            }

            $requerimiento->update(['estado' => 'ANULADO']);

            DB::commit();
            return redirect()->route('requerimientos_materiales.show', $id)
                ->with('success', 'Requerimiento anulado.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
