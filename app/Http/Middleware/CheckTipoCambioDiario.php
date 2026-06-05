<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ParametroSistema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CheckTipoCambioDiario
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo ejecutar en rutas GET para no interrumpir envíos de formularios (POST/PUT/DELETE)
        if (!$request->isMethod('get') || $request->ajax()) {
            return $next($request);
        }

        // Usamos cache diario para evitar tocar la base de datos en absolutamente cada petición
        $fechaCache = Cache::remember('tipo_cambio_fecha', 60*60*24, function () {
            $parametro = ParametroSistema::where('codigo_parametro', 'TIPO_CAMBIO_USD')->first();
            return $parametro ? Carbon::parse($parametro->fecha_actualizacion)->format('Y-m-d') : null;
        });

        $hoy = now()->format('Y-m-d');

        // Si la fecha guardada no es de hoy
        if ($fechaCache !== $hoy) {
            try {
                // Consultar API Sunat
                $response = Http::timeout(5)->get('https://api.apis.net.pe/v1/tipo-cambio-sunat');

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['venta'])) {
                        $parametro = ParametroSistema::where('codigo_parametro', 'TIPO_CAMBIO_USD')->first();
                        if ($parametro) {
                            $parametro->valor = $data['venta'];
                            $parametro->fecha_actualizacion = now();
                            $parametro->save();

                            // Actualizar el cache para que no vuelva a consultar hoy
                            Cache::put('tipo_cambio_fecha', $hoy, 60*60*24);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Silencioso. Si falla la API de la SUNAT, el sistema debe seguir funcionando
                // con el tipo de cambio del último día hábil.
            }
        }

        return $next($request);
    }
}
