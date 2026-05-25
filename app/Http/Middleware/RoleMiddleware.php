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
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usuarioRol = Auth::user()->rol;

        // Compatibilidad con rutas estáticas (ej. role:Supervisor) y acceso total a Administrador
        if ($usuarioRol === 'Administrador' || (!empty($roles) && in_array($usuarioRol, $roles))) {
            return $next($request);
        }

        $routeName = $request->route()->getName();
        
        if ($routeName) {
            $parts = explode('.', $routeName);
            $slugToCheck = $parts[0] . '.index';
            
            // Lógica específica para el módulo de inventario que tiene submódulos
            if ($parts[0] === 'inventario' && isset($parts[1])) {
                if (in_array($parts[1], ['recepciones', 'procesar_recepcion'])) {
                    $slugToCheck = 'inventario.recepciones';
                } elseif (in_array($parts[1], ['ajuste', 'store_ajuste'])) {
                    $slugToCheck = 'inventario.ajuste';
                } elseif (in_array($parts[1], ['extornos', 'procesar_extorno'])) {
                    $slugToCheck = 'inventario.extornos';
                } else {
                    $slugToCheck = 'inventario.' . $parts[1];
                }
            }

            // Validar acceso dinámico contra la base de datos
            $hasAccess = \Illuminate\Support\Facades\DB::table('roles')
                ->where('roles.nombre', $usuarioRol)
                ->join('rol_modulo', 'roles.id', '=', 'rol_modulo.rol_id')
                ->join('modulos', 'modulos.id', '=', 'rol_modulo.modulo_id')
                ->where('modulos.slug', $slugToCheck)
                ->exists();

            if ($hasAccess) {
                return $next($request);
            }
        }

        return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a este módulo.');
    }
}