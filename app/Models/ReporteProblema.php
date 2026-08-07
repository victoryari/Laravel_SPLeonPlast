<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteProblema extends Model
{
    use HasFactory;

    protected $table = 'reportes_problemas';
    protected $primaryKey = 'id_reporte';

    protected $fillable = [
        'id_usuario',
        'titulo',
        'tipo_problema',
        'prioridad',
        'descripcion',
        'estado',
        'respuesta_admin',
    ];

    /**
     * Relación con el usuario que reportó el problema.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Labels legibles para el tipo de problema.
     */
    public static function tiposProblema(): array
    {
        return [
            'ERROR_SISTEMA'    => 'Error del Sistema',
            'CONSULTA_DUDA'    => 'Consulta / Duda',
            'MEJORA_SOLICITUD' => 'Solicitud de Mejora',
            'FALLO_DATOS'      => 'Fallo en Datos',
            'OTRO'             => 'Otro',
        ];
    }

    /**
     * Labels legibles para la prioridad.
     */
    public static function prioridades(): array
    {
        return [
            'BAJA'    => 'Baja',
            'MEDIA'   => 'Media',
            'ALTA'    => 'Alta',
            'CRITICA' => 'Crítica',
        ];
    }

    /**
     * Labels legibles para el estado.
     */
    public static function estados(): array
    {
        return [
            'PENDIENTE'   => 'Pendiente',
            'EN_REVISION' => 'En Revisión',
            'RESUELTO'    => 'Resuelto',
            'RECHAZADO'   => 'Rechazado',
        ];
    }
}
