<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trabajador extends Model
{
    use HasFactory;

    protected $table = 'trabajador'; // <-- Nombre de tabla corregido
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;

    protected $fillable = [
        'codigo',
        'nombre',
        'empresa',
        'sueldo',
        'estado'
    ];

    protected $casts = [
        'sueldo' => 'decimal:2',
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }
}