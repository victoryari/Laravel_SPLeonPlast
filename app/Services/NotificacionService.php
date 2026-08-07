<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class NotificacionService
{
    /**
     * Crear una notificación para un usuario o para todos los usuarios.
     *
     * @param int|array|null $idUsuarios ID de usuario, array de IDs, o null para global
     * @param string $titulo
     * @param string $mensaje
     * @param string $tipo 'info'|'warning'|'success'|'danger'
     * @param string|null $link URL de acción
     */
    public static function crear($idUsuarios, string $titulo, string $mensaje, string $tipo = 'info', ?string $link = null)
    {
        if (is_array($idUsuarios)) {
            $notificaciones = [];
            foreach ($idUsuarios as $idUsuario) {
                $notificaciones[] = [
                    'id_usuario' => $idUsuario,
                    'titulo'     => $titulo,
                    'mensaje'    => $mensaje,
                    'tipo'       => $tipo,
                    'link'       => $link,
                    'leido'      => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Notificacion::insert($notificaciones);
        } else {
            Notificacion::create([
                'id_usuario' => $idUsuarios,
                'titulo'     => $titulo,
                'mensaje'    => $mensaje,
                'tipo'       => $tipo,
                'link'       => $link,
                'leido'      => false,
            ]);
        }
    }

    /**
     * Sincroniza y genera notificaciones del sistema según alertas actuales si no existen aún.
     */
    public static function generarAlertasSistema($idUsuario)
    {
        // 1. Alertas de Stock Bajo
        try {
            if (SchemaHasTable('almacen_inventario')) {
                $stockBajo = DB::table('almacen_inventario')
                    ->whereColumn('stock_actual', '<=', 'stock_minimo')
                    ->limit(3)
                    ->get();

                foreach ($stockBajo as $item) {
                    $titulo = "Alerta de Stock Bajo";
                    $mensaje = "El producto/material '{$item->codigo_producto}' está por debajo del stock mínimo ({$item->stock_actual} disps).";
                    
                    $existe = Notificacion::where('id_usuario', $idUsuario)
                        ->where('titulo', $titulo)
                        ->where('mensaje', $mensaje)
                        ->exists();

                    if (!$existe) {
                        self::crear($idUsuario, $titulo, $mensaje, 'warning', route('inventario.alertas_stock'));
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignorar silente si la vista o tabla varía
        }
    }
}

function SchemaHasTable($table) {
    return \Illuminate\Support\Facades\Schema::hasTable($table);
}
