<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadProduccion extends Model
{
    use HasFactory;

    protected $table = 'actividad_produccion';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;

    protected $fillable = [
        'codigo',
        'descripcion',
        'estado'
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }
}