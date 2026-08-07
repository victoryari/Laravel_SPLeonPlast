<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notificacion;
use App\Services\NotificacionService;

class NotificacionController extends Controller
{
    /**
     * Muestra la vista principal del Gestor de Notificaciones.
     */
    public function index(Request $request)
    {
        $userId = Auth::user()->id_usuario;
        
        // Generar alertas del sistema si corresponden
        NotificacionService::generarAlertasSistema($userId);

        $filtro = $request->query('filtro', 'todas');

        $query = Notificacion::paraUsuario($userId);

        if ($filtro === 'no_leidas') {
            $query->noLeidas();
        } elseif ($filtro === 'leidas') {
            $query->leidas();
        }

        $notificaciones = $query->orderBy('created_at', 'desc')->paginate(15);
        $conteoNoLeidas = Notificacion::paraUsuario($userId)->noLeidas()->count();
        $conteoLeidas   = Notificacion::paraUsuario($userId)->leidas()->count();
        $conteoTodas    = Notificacion::paraUsuario($userId)->count();

        return view('notificaciones.index', compact(
            'notificaciones',
            'filtro',
            'conteoNoLeidas',
            'conteoLeidas',
            'conteoTodas'
        ));
    }

    /**
     * Muestra el detalle de una notificación individual.
     */
    public function show($id)
    {
        $userId = Auth::user()->id_usuario;

        $notificacion = Notificacion::paraUsuario($userId)->findOrFail($id);

        // Marcar como leída automáticamente al ver el detalle
        if (!$notificacion->leido) {
            $notificacion->update([
                'leido'   => true,
                'read_at' => now(),
            ]);
        }

        return view('notificaciones.show', compact('notificacion'));
    }

    /**
     * API JSON para la campanita del header.
     */
    public function getUnreadApi()
    {
        $userId = Auth::user()->id_usuario;

        // Generar alertas automáticas si aplica
        NotificacionService::generarAlertasSistema($userId);

        $unreadCount = Notificacion::paraUsuario($userId)->noLeidas()->count();

        $notifications = Notificacion::paraUsuario($userId)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get()
            ->map(function ($n) {
                return [
                    'id'          => $n->id,
                    'titulo'      => $n->titulo,
                    'mensaje'     => $n->mensaje,
                    'tipo'        => $n->tipo,
                    'link'        => $n->link,
                    'leido'       => $n->leido,
                    'tiempo_hace' => $n->tiempo_hace,
                ];
            });

        return response()->json([
            'status'        => 'success',
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Marcar una notificación como leída.
     */
    public function marcarLeida(Request $request, $id)
    {
        $userId = Auth::user()->id_usuario;

        $notificacion = Notificacion::paraUsuario($userId)->findOrFail($id);
        $notificacion->update([
            'leido' => true,
            'read_at' => now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Notificación marcada como leída']);
        }

        if ($notificacion->link) {
            return redirect($notificacion->link);
        }

        return back()->with('success', 'Notificación marcada como leída.');
    }

    /**
     * Marcar todas las notificaciones como leídas.
     */
    public function marcarTodasLeidas(Request $request)
    {
        $userId = Auth::user()->id_usuario;

        Notificacion::paraUsuario($userId)
            ->noLeidas()
            ->update([
                'leido' => true,
                'read_at' => now(),
            ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Todas las notificaciones fueron marcadas como leídas']);
        }

        return back()->with('success', 'Todas las notificaciones fueron marcadas como leídas.');
    }

    /**
     * Eliminar una notificación.
     */
    public function destroy(Request $request, $id)
    {
        $userId = Auth::user()->id_usuario;

        $notificacion = Notificacion::paraUsuario($userId)->findOrFail($id);
        $notificacion->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Notificación eliminada']);
        }

        return back()->with('success', 'Notificación eliminada correctamente.');
    }
}
