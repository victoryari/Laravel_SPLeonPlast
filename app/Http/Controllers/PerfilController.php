<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\ReporteProblema;
use App\Models\Notificacion;
use App\Models\Usuario;

class PerfilController extends Controller
{
    /**
     * Muestra la vista del perfil del usuario logueado.
     */
    public function index()
    {
        $user = Auth::user();
        $trabajador = $user->trabajador;

        // Historial de reportes del usuario
        $reportes = ReporteProblema::where('id_usuario', $user->id_usuario)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('perfil.index', compact('user', 'trabajador', 'reportes'));
    }

    /**
     * Actualiza la contraseña del usuario logueado.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_actual'       => 'required|string',
            'password_nuevo'        => 'required|string|min:6|confirmed',
        ], [
            'password_actual.required'       => 'La contraseña actual es obligatoria.',
            'password_nuevo.required'        => 'La nueva contraseña es obligatoria.',
            'password_nuevo.min'             => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password_nuevo.confirmed'       => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        $user = Auth::user();

        // Verificar que la contraseña actual sea correcta
        if (!Hash::check($request->password_actual, $user->contrasena_hash)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual es incorrecta.'])->withInput();
        }

        // Verificar que la nueva contraseña sea diferente a la actual
        if (Hash::check($request->password_nuevo, $user->contrasena_hash)) {
            return back()->withErrors(['password_nuevo' => 'La nueva contraseña debe ser diferente a la actual.'])->withInput();
        }

        // Actualizar la contraseña
        $user->contrasena_hash = Hash::make($request->password_nuevo);
        $user->save();

        return redirect()->route('perfil.index')->with('success', '¡Contraseña actualizada correctamente!');
    }

    /**
     * Almacena un nuevo reporte de problema enviado por el usuario.
     */
    public function storeReporteProblema(Request $request)
    {
        $request->validate([
            'titulo'        => 'required|string|max:255',
            'tipo_problema' => 'required|in:ERROR_SISTEMA,CONSULTA_DUDA,MEJORA_SOLICITUD,FALLO_DATOS,OTRO',
            'prioridad'     => 'required|in:BAJA,MEDIA,ALTA,CRITICA',
            'descripcion'   => 'required|string|max:5000',
        ], [
            'titulo.required'        => 'El título del reporte es obligatorio.',
            'titulo.max'             => 'El título no puede exceder los 255 caracteres.',
            'tipo_problema.required' => 'Seleccione un tipo de problema.',
            'tipo_problema.in'       => 'El tipo de problema seleccionado no es válido.',
            'prioridad.required'     => 'Seleccione un nivel de prioridad.',
            'prioridad.in'           => 'La prioridad seleccionada no es válida.',
            'descripcion.required'   => 'La descripción del problema es obligatoria.',
            'descripcion.max'        => 'La descripción no puede exceder los 5000 caracteres.',
        ]);

        $user = Auth::user();

        // Crear el reporte
        $reporte = ReporteProblema::create([
            'id_usuario'    => $user->id_usuario,
            'titulo'        => $request->titulo,
            'tipo_problema' => $request->tipo_problema,
            'prioridad'     => $request->prioridad,
            'descripcion'   => $request->descripcion,
            'estado'        => 'PENDIENTE',
        ]);

        // Notificar a todos los administradores
        $admins = Usuario::where('rol', 'Administrador')
            ->where('activo', 1)
            ->get();

        $tiposLabel = ReporteProblema::tiposProblema();
        $tipoLabel = $tiposLabel[$request->tipo_problema] ?? $request->tipo_problema;

        foreach ($admins as $admin) {
            Notificacion::create([
                'id_usuario' => $admin->id_usuario,
                'titulo'     => 'Nuevo reporte de problema',
                'mensaje'    => "El usuario {$user->nombre_usuario} ha reportado: \"{$request->titulo}\" (Tipo: {$tipoLabel}, Prioridad: {$request->prioridad})",
                'tipo'       => $request->prioridad === 'CRITICA' ? 'danger' : ($request->prioridad === 'ALTA' ? 'warning' : 'info'),
                'link'       => route('perfil.index', ['tab' => 'reporte']),
                'leido'      => false,
            ]);
        }

        return redirect()->route('perfil.index')->with('success', '¡Reporte de problema enviado correctamente! Los administradores han sido notificados.');
    }
}
