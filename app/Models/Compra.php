<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';
    protected $primaryKey = 'id_compra';
    
    const CREATED_AT = 'fecha_creacion'; 
    const UPDATED_AT = null;

    protected $fillable = [
    'tipo_documento',
    'serie_documento',
    'numero_documento',
    'proveedor',      // Nuevo: Es NOT NULL en la BD
    'ruc_proveedor',
    'fecha_compra',
    'subtotal',
    'igv',            // Corregido: En la BD es 'igv', no 'impuestos'
    'total',
    'estado',
    'usuario_creacion',
    'usuario_aprobacion'
];

    public function datosProveedor()
    {
        return $this->belongsTo(Proveedor::class, 'ruc_proveedor', 'ruc');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'id_compra', 'id_compra');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'id_almacen', 'id_almacen');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'usuario_creacion', 'id_usuario');
    }
}