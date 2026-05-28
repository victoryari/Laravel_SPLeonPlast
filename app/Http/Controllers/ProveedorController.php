<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Proveedor::activos();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('ruc', 'LIKE', "%$search%")
                  ->orWhere('razon_social', 'LIKE', "%$search%")
                  ->orWhere('contacto', 'LIKE', "%$search%");
            });
        }

        $proveedores = $query->orderBy('razon_social', 'asc')->paginate(10);

        return view('tablas_maestras.proveedores.index', compact('proveedores', 'search'));
    }

    public function create()
    {
        return view('tablas_maestras.proveedores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'ruc' => 'required|string|size:11|unique:proveedores,ruc',
            'razon_social' => 'required|string|max:150',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|numeric|digits_between:1,9',
        ],
        [// Aquí agregas tus mensajes personalizados:
            'ruc.unique' => 'Este RUC ya se encuentra registrado en el sistema.',
            'ruc.size' => 'El RUC debe tener exactamente 11 dígitos.',
            'ruc.required' => 'El campo RUC es obligatorio.'
        ]);

        Proveedor::create([
            'ruc' => $request->ruc,
            'razon_social' => strtoupper($request->razon_social),
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'contacto' => $request->contacto,
            'activo' => 1
        ]);

        return redirect()->route('proveedores.index')->with('success', 'Proveedor registrado exitosamente.');
    }

    public function edit($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return view('tablas_maestras.proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $request->validate([
            'ruc' => 'required|string|size:11|unique:proveedores,ruc,' . $id . ',id_proveedor',
            'razon_social' => 'required|string|max:255',
            'email' => 'nullable|email|max:100',
        ]);

        $proveedor->update([
            'ruc' => $request->ruc,
            'razon_social' => strtoupper($request->razon_social),
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'contacto' => $request->contacto
        ]);

        return redirect()->route('proveedores.index')->with('success', 'Datos del proveedor actualizados.');
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        // Desactivación lógica según tu requerimiento
        $proveedor->update(['activo' => 0]);

        return redirect()->route('proveedores.index')->with('success', 'Proveedor desactivado correctamente.');
    }

    /**
     * Guarda un proveedor desde un modal vía AJAX
     */
    public function storeAjax(Request $request)
    {
        // Validamos que el RUC no exista previamente
        $request->validate([
            'ruc' => 'required|string|max:11|unique:proveedores,ruc',
            'razon_social' => 'required|string|max:255'
        ]);

        try {
            // Asegúrate de que tu modelo Proveedor tenga 'ruc', 'razon_social' y 'activo' en su $fillable
            $proveedor = Proveedor::create([
                'ruc' => $request->ruc,
                'razon_social' => strtoupper($request->razon_social),
                'activo' => 1
            ]);
            
            return response()->json(['success' => true, 'proveedor' => $proveedor]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

}