<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConciliacionTerceros extends Model
{
    use HasFactory;

    protected $table = 'conciliacion_terceros';
    protected $primaryKey = 'id_conciliacion';

    protected $fillable = [
        'id_detalle_salida',
        'id_detalle_compra',
        'cantidad_amortizada',
        'fecha_conciliacion',
    ];
}
