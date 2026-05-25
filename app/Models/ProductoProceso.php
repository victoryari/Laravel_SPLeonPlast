<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoProceso extends Model
{
    use HasFactory;

    protected $table = 'producto_proceso';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // Asumiendo que no usa created_at/updated_at por defecto

    protected $fillable = [
        'codigo',
        'descripcion',
        'estado'
    ];
}
