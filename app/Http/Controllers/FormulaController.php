<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormulaProduccion;
use App\Models\ComposicionFormula;
use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Models\Molde;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FormulaController extends Controller
{
    // --- 1. CRUD DE LA FÓRMULA (CABECERA) ---

    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $query = FormulaProduccion::withCount('composiciones')->where('estado', 1);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('codigo', 'LIKE', "%{$search}%")
                  ->orWhere('descripcion', 'LIKE', "%{$search}%");
            });
        }

        $formulas = $query->orderBy('descripcion', 'asc')->paginate(10);
        $formulas->appends(['search' => $search]);

        return view('tablas_maestras.formula.index', compact('formulas', 'search'));
    }

    public function create()
    {
        $materialesReciclados = \Illuminate\Support\Facades\DB::table('producto')
            ->whereIn('codigo_tipo_producto', ['REC', 'PEP'])
            ->where('estado', 1)
            ->get();
            
        return view('tablas_maestras.formula.create', compact('materialesReciclados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:20|unique:formula_produccion,codigo',
            'descripcion' => 'required|string|max:150',
            'codigo_material_reciclado' => 'nullable|string|exists:producto,codigo'
        ]);

        FormulaProduccion::create([
            'codigo' => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'codigo_material_reciclado' => $request->codigo_material_reciclado,
            'estado' => 1,
            'fecha_creacion' => Carbon::now(),
        ]);

        return redirect()->route('formulas.index')
            ->with('success', 'Fórmula base creada exitosamente. Ya puede agregarle su composición.');
    }

    public function edit($codigo)
    {
        $formula = FormulaProduccion::where('codigo', $codigo)->firstOrFail();
        if ($formula->estado == 0) return redirect()->route('formulas.index')->with('error', 'Fórmula anulada.');
        
        $materialesReciclados = \Illuminate\Support\Facades\DB::table('producto')
            ->whereIn('codigo_tipo_producto', ['REC', 'PEP'])
            ->where('estado', 1)
            ->get();
            
        return view('tablas_maestras.formula.edit', compact('formula', 'materialesReciclados'));
    }

    public function update(Request $request, $codigo)
    {
        $formula = FormulaProduccion::where('codigo', $codigo)->firstOrFail();
        $request->validate([
            'descripcion' => 'required|string|max:150',
            'codigo_material_reciclado' => 'nullable|string|exists:producto,codigo'
        ]);
        
        $formula->update([
            'descripcion' => $request->descripcion,
            'codigo_material_reciclado' => $request->codigo_material_reciclado
        ]);
        
        return redirect()->route('formulas.index')->with('success', 'Fórmula actualizada.');
    }

    public function destroy($codigo)
    {
        $formula = FormulaProduccion::where('codigo', $codigo)->firstOrFail();
        $formula->estado = 0; 
        $formula->save();
        return redirect()->route('formulas.index')->with('success', 'Fórmula anulada.');
    }


    // --- 2. GESTIÓN DE LA COMPOSICIÓN (DETALLE) ---

    public function composicion($codigo)
    {
        $formula = FormulaProduccion::where('codigo', $codigo)->firstOrFail();
        if ($formula->estado == 0) return redirect()->route('formulas.index')->with('error', 'Fórmula anulada.');

        $composiciones = ComposicionFormula::with(['producto', 'unidad'])
                            ->where('codigo_formula', $codigo)->get();

        $productos = Producto::where('estado', 1)->orderBy('descripcion', 'asc')->get();
        $unidades = UnidadMedida::where('estado', 1)->orderBy('descripcion', 'asc')->get();
        
        // CORRECCIÓN APLICADA: Ahora busca por el campo 'activo'
        $moldes = Molde::where('activo', 1)->orderBy('descripcion', 'asc')->get(); 

        return view('tablas_maestras.formula.composicion', compact('formula', 'composiciones', 'productos', 'unidades', 'moldes'));
    }

    public function storeComposicion(Request $request, $codigo)
    {
        $formula = FormulaProduccion::where('codigo', $codigo)->firstOrFail();

        $request->validate([
            'productos' => 'nullable|array',
            'productos.*' => 'required|string',
            'tipos' => 'nullable|array', /* <-- Validación agregada */
            'cantidades_nominales' => 'nullable|array',
            'cantidades_reales' => 'nullable|array',
            'unidades' => 'nullable|array',
            'moldes' => 'nullable|array',
            'moldes.*' => 'nullable|exists:molde,codigo',
        ]);

        try {
            DB::beginTransaction();

            // Usamos soft delete o delete físico según esté configurado
            ComposicionFormula::where('codigo_formula', $codigo)->delete();

            if ($request->has('productos')) {
                foreach ($request->productos as $key => $productoCod) {
                    ComposicionFormula::create([
                        'codigo_formula' => $codigo,
                        'codigo_producto' => $productoCod,
                        'codigo_tipo_producto' => $request->tipos[$key] ?? null, /* <-- Se guarda en BD */
                        'cantidad_nominal' => $request->cantidades_nominales[$key] ?? 0,
                        'cantidad_real' => $request->cantidades_reales[$key] ?? 0,
                        'codigo_unidad_medida' => $request->unidades[$key] ?? null,
                        'codigo_molde' => $request->moldes[$key] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('formulas.index')->with('success', 'Composición de la fórmula guardada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al guardar la composición: ' . $e->getMessage());
        }
    }
}