<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuiaRemisionCompra extends Model
{
    protected $table = 'guia_remision_compras';
    protected $primaryKey = 'id_guia';
    
    const CREATED_AT = 'fecha_registro'; 
    const UPDATED_AT = null;

    protected $fillable = [
        'proveedor',
        'ruc_proveedor',
        'numero_guia',
        'fecha_emision',
        'estado',
        'id_compra',
        'observaciones',
        'usuario_registro'
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleGuiaCompra::class, 'id_guia', 'id_guia');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function datosProveedor()
    {
        return $this->belongsTo(Proveedor::class, 'ruc_proveedor', 'ruc');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'usuario_registro', 'id_usuario');
    }
}
