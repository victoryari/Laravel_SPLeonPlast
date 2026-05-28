<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcesoProduccion extends Model
{
    use HasFactory;

    protected $table = 'proceso_produccion';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'descripcion',
        'codigo_almacen',
        'estado',
        'fecha_creacion'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'estado' => 'integer',
    ];

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'codigo_almacen', 'codigo_almacen');
    }
}