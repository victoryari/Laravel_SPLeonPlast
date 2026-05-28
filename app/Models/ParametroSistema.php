<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametroSistema extends Model
{
    use HasFactory;

    protected $table = 'parametros_sistema';
    protected $primaryKey = 'id_parametro';

    // Deshabilitar los timestamps de Laravel, usaremos fecha_actualizacion
    public $timestamps = false;

    protected $fillable = [
        'codigo_parametro',
        'descripcion',
        'valor',
        'tipo',
        'categoria',
        'editable',
        'fecha_actualizacion',
        'usuario_actualizacion'
    ];

    // Para que Laravel maneje fecha_actualizacion automáticamente al guardar
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            $model->fecha_actualizacion = now();
            if (auth()->check()) {
                $model->usuario_actualizacion = auth()->id();
            }
        });

        static::creating(function ($model) {
            $model->fecha_actualizacion = now();
            if (auth()->check()) {
                $model->usuario_actualizacion = auth()->id();
            }
        });
    }
}
