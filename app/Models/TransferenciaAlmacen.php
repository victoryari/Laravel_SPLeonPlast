<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferenciaAlmacen extends Model
{
    use HasFactory;

    protected $table = 'transferencias_almacen';
    protected $primaryKey = 'id_transferencia';

    protected $fillable = [
        'numero_transferencia',
        'codigo_almacen_origen',
        'codigo_almacen_destino',
        'fecha_transferencia',
        'observaciones',
        'estado',
        'usuario_registro',
    ];

    public function almacenOrigen()
    {
        return $this->belongsTo(Almacen::class, 'codigo_almacen_origen', 'codigo_almacen');
    }

    public function almacenDestino()
    {
        return $this->belongsTo(Almacen::class, 'codigo_almacen_destino', 'codigo_almacen');
    }

    public function detalles()
    {
        return $this->hasMany(TransferenciaAlmacenDetalle::class, 'id_transferencia', 'id_transferencia');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_registro', 'id_usuario');
    }
}
