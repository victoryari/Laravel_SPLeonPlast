<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapeoTerceroController extends Controller
{
    public function index()
    {
        $mapeos = DB::table('terceros_mapeo_productos as m')
            ->select(
                'm.id_mapeo',
                'm.codigo_producto_origen',
                'm.codigo_producto_destino',
                'm.descripcion_proceso',
                'm.estado'
            )
            ->where('m.estado', 1)
            ->orderBy('m.id_mapeo', 'desc')
            ->paginate(20);

        $codigos = collect($mapeos->items())->pluck('codigo_producto_origen')
            ->merge(collect($mapeos->items())->pluck('codigo_producto_destino'))
            ->unique()
            ->toArray();

        $productos = DB::table('producto')
            ->whereIn('codigo', $codigos)
            ->pluck('descripcion', 'codigo');

        foreach ($mapeos->items() as $mapeo) {
            $mapeo->descripcion_origen = $productos[$mapeo->codigo_producto_origen] ?? 'Desconocido';
            $mapeo->descripcion_destino = $productos[$mapeo->codigo_producto_destino] ?? 'Desconocido';
        }

        return view('maestros.mapeo_terceros.index', compact('mapeos'));
    }

    public function create()
    {
        // Solo PEP pueden ser origen (los que salen a maquila)
        $productosOrigen = DB::table('producto')
            ->where('codigo_tipo_producto', 'PEP')
            ->where('estado', 1)
            ->get();
            
        // Los destinos pueden ser cualquier producto o tal vez solo PEP o PT
        $productosDestino = DB::table('producto')
            ->where('estado', 1)
            ->get();

        return view('maestros.mapeo_terceros.create', compact('productosOrigen', 'productosDestino'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo_producto_origen' => 'required|string',
            'codigo_producto_destino' => 'required|string',
            'descripcion_proceso' => 'nullable|string'
        ]);

        // Evitar duplicados
        $existe = DB::table('terceros_mapeo_productos')
            ->where('codigo_producto_origen', $request->codigo_producto_origen)
            ->where('codigo_producto_destino', $request->codigo_producto_destino)
            ->where('estado', 1)
            ->exists();

        if ($existe) {
            return redirect()->back()->withInput()->with('error', 'Ya existe esta regla de mapeo.');
        }

        DB::table('terceros_mapeo_productos')->insert([
            'codigo_producto_origen' => $request->codigo_producto_origen,
            'codigo_producto_destino' => $request->codigo_producto_destino,
            'descripcion_proceso' => $request->descripcion_proceso,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('mapeo-terceros.index')->with('success', 'Regla de mapeo creada exitosamente.');
    }

    public function destroy($id)
    {
        DB::table('terceros_mapeo_productos')
            ->where('id_mapeo', $id)
            ->update([
                'estado' => 0,
                'updated_at' => now()
            ]);

        return redirect()->route('mapeo-terceros.index')->with('success', 'Regla de mapeo eliminada correctamente.');
    }
}
