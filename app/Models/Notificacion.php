<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'id_usuario',
        'titulo',
        'mensaje',
        'tipo',
        'link',
        'leido',
        'read_at',
    ];

    protected $casts = [
        'leido' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Relación con el usuario destinatario.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Scope para obtener notificaciones no leídas.
     */
    public function scopeNoLeidas($query)
    {
        return $query->where('leido', false);
    }

    /**
     * Scope para obtener notificaciones leídas.
     */
    public function scopeLeidas($query)
    {
        return $query->where('leido', true);
    }

    /**
     * Scope para filtrar por usuario específico o globales (id_usuario null).
     */
    public function scopeParaUsuario($query, $idUsuario)
    {
        return $query->where(function ($q) use ($idUsuario) {
            $q->where('id_usuario', $idUsuario)
              ->orWhereNull('id_usuario');
        });
    }

    /**
     * Formato dinámico del tiempo transcurrido (hace X min, hace X h).
     */
    public function getTiempoHaceAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }
}
