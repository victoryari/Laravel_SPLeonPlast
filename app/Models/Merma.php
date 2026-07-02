<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merma extends Model
{
    use HasFactory;

    protected $table = 'mermas';
    protected $primaryKey = 'id_merma';

    protected $fillable = [
        'fecha_merma',
        'hora_inicio',
        'hora_fin',
        'codigo_trabajador',
        'codigo_producto',
        'descripcion_producto',
        'cantidad',
        'costo_unitario',
        'costo_total',
        'motivo',
        'tipo_merma',
        'codigo_almacen',
        'id_orden_produccion',
        'estado',
        'usuario_registro'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'codigo_producto', 'codigo');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'codigo_almacen', 'codigo_almacen');
    }

    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class, 'id_orden_produccion', 'idop');
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(Usuario::class, 'usuario_registro', 'id_usuario');
    }

    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'codigo_trabajador', 'codigo');
    }
}
