<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;

    protected $fillable = [
        'codigo_trabajador',
        'nombre_usuario',
        'contrasena_hash',
        'email',
        'rol',
        'ultimo_login',
        'activo'
    ];

    protected $hidden = [
        'contrasena_hash',
    ];

    // Le indicamos a Laravel qué campo usar como contraseña para el Login
    public function getAuthPassword()
    {
        return $this->contrasena_hash;
    }

    public function scopeActivos(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where('activo', 1);
    }

    // Relación con la tabla Trabajador
    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'codigo_trabajador', 'codigo');
    }
}