<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Maneja la restricción de acceso por rol.
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Verificamos que el usuario esté autenticado y que su rol coincida exactamente
        if (!Auth::check() || trim(Auth::user()->rol) !== trim($role)) {
            // Si no tiene permiso, lo enviamos al dashboard general con un mensaje de error
            return redirect()->route('dashboard')->with('error', 'No tienes permisos de ' . $role . ' para acceder aquí.');
        }

        return $next($request);
    }
}