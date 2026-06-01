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
        'observaciones',
        'usuario_registro'
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleGuiaCompra::class, 'id_guia', 'id_guia');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'id_guia_remision_compra', 'id_guia');
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
