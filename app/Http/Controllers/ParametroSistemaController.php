<?php

namespace App\Http\Controllers;

use App\Models\ParametroSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ParametroSistemaController extends Controller
{
    public function index()
    {
        // Obtener todos los parámetros y agruparlos por categoría
        $parametros = ParametroSistema::orderBy('id_parametro', 'asc')->get();
        $categorias = $parametros->groupBy('categoria');

        return view('parametros.index', compact('categorias', 'parametros'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo_parametro' => 'required|string|unique:parametros_sistema,codigo_parametro|max:50',
            'descripcion' => 'required|string|max:255',
            'valor' => 'required|string',
            'categoria' => 'required|string|max:50',
            'tipo' => 'required|in:TEXTO,NUMERICO,BOOLEANO',
        ]);

        ParametroSistema::create([
            'codigo_parametro' => strtoupper(str_replace(' ', '_', $request->codigo_parametro)),
            'descripcion' => $request->descripcion,
            'valor' => $request->valor,
            'categoria' => strtoupper($request->categoria),
            'tipo' => $request->tipo,
            'editable' => 1,
            'fecha_actualizacion' => now()
        ]);

        return redirect()->route('parametros.index')->with('success', 'Parámetro agregado exitosamente.');
    }

    public function updateBulk(Request $request)
    {
        $data = $request->except('_token', '_method');

        foreach ($data as $codigo => $valor) {
            $parametro = ParametroSistema::where('codigo_parametro', $codigo)->first();
            
            if ($parametro && $parametro->editable) {
                // Validación básica según tipo (opcional, pero buena práctica)
                if ($parametro->tipo === 'NUMERICO' && !is_numeric($valor)) {
                    continue; // Ignorar si se envió texto donde va un número
                }

                $parametro->valor = $valor;
                $parametro->save();
            }
        }

        return redirect()->route('parametros.index')->with('success', 'Parámetros actualizados correctamente.');
    }

    public function fetchTipoCambio()
    {
        try {
            // Usamos la API pública de apis.net.pe para obtener el tipo de cambio de la SUNAT
            // Nota: Algunas APIs pueden requerir token. Si es pública como esta, puede variar.
            // Para asegurar funcionalidad, podemos usar esta o un fallback si falla.
            $response = Http::timeout(10)->get('https://api.apis.net.pe/v1/tipo-cambio-sunat');

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['venta'])) {
                    $parametro = ParametroSistema::where('codigo_parametro', 'TIPO_CAMBIO_USD')->first();
                    if ($parametro) {
                        $parametro->valor = $data['venta'];
                        $parametro->save();

                        return redirect()->route('parametros.index')->with('success', 'Tipo de cambio actualizado a S/ ' . $data['venta'] . ' exitosamente desde SUNAT.');
                    }
                }
            }

            return redirect()->route('parametros.index')->with('error', 'No se pudo obtener el tipo de cambio de la API en este momento. Intenta ingresarlo manualmente.');
            
        } catch (\Exception $e) {
            return redirect()->route('parametros.index')->with('error', 'Error de conexión con el servicio de tipo de cambio.');
        }
    }

    public function limpiarDB()
    {
        if (\Illuminate\Support\Facades\Auth::user()->rol !== 'Administrador') {
            return back()->with('error', 'No tienes permisos para realizar esta acción.');
        }

        try {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            $tablas_transaccionales = [
                'compras',
                'despacho_requerimiento_lotes',
                'detalle_compra',
                'detalle_guia_compras',
                'detalle_requerimientos_materiales',
                'guia_remision_compras',
                'inventario',
                'kardex',
                'mermas',
                'movimientos_inventario',
                'orden_proceso',
                'orden_produccion_global',
                'componentes_orden_produccion_global',
                'produccion_costos',
                'produccion_ingresos_proceso',
                'requerimientos_materiales',
                'transferencias_almacen',
                'transferencias_almacen_detalle',
                'guia_remision_terceros_salida',
                'guia_remision_terceros_salida_detalle',
                'conciliacion_terceros',
            ];

            foreach ($tablas_transaccionales as $tabla) {
                \Illuminate\Support\Facades\DB::table($tabla)->truncate();
            }
            
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            return redirect()->route('parametros.index')->with('success', 'Base de datos operativa vaciada exitosamente. Las tablas maestras se han conservado.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'Error al purgar la base de datos: ' . $e->getMessage());
        }
    }
}