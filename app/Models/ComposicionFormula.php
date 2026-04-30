<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComposicionFormula extends Model
{
    use HasFactory;

    protected $table = 'composicion_formula';
    
    public $timestamps = false;

    protected $fillable = [
        'codigo_formula',
        'codigo_producto',
        'codigo_tipo_producto', /* <-- CAMPO AGREGADO */
        'cantidad_nominal',
        'cantidad_real',
        'codigo_unidad_medida',
        'codigo_molde'
    ];

    protected $casts = [
        'cantidad_nominal' => 'decimal:4',
        'cantidad_real' => 'decimal:4',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'codigo_producto', 'codigo');
    }

    public function unidad()
    {
        return $this->belongsTo(UnidadMedida::class, 'codigo_unidad_medida', 'codigo');
    }
}