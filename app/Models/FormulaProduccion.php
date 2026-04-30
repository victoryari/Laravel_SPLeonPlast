<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormulaProduccion extends Model
{
    use HasFactory;

    protected $table = 'formula_produccion';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'descripcion',
        'estado',
        'fecha_creacion'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'estado' => 'integer',
    ];

    // Relación: Una fórmula tiene muchas composiciones
    public function composiciones()
    {
        return $this->hasMany(ComposicionFormula::class, 'codigo_formula', 'codigo');
    }
}