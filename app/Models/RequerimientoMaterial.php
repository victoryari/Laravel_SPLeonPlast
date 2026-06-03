<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequerimientoMaterial extends Model
{
    protected $table = 'requerimientos_materiales';
    protected $primaryKey = 'id_requerimiento';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null;

    protected $fillable = [
        'codigo',
        'idop',
        'id_proceso',
        'motivo',
        'estado',
        'usuario_creacion',
        'usuario_aprobacion',
        'observaciones',
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleRequerimientoMaterial::class, 'id_requerimiento', 'id_requerimiento');
    }

    public function ordenProduccion()
    {
        return $this->belongsTo(OrdenProduccion::class, 'idop', 'idop');
    }

    public function ordenProceso()
    {
        return $this->belongsTo(OrdenProceso::class, 'id_proceso', 'id');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'usuario_creacion', 'id_usuario');
    }

    public function aprobador()
    {
        return $this->belongsTo(Usuario::class, 'usuario_aprobacion', 'id_usuario');
    }

    public function despachosLotes()
    {
        return $this->hasMany(DespachoRequerimientoLote::class, 'id_requerimiento', 'id_requerimiento');
    }
}
