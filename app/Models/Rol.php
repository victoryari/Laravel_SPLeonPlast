<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';
    protected $fillable = ['nombre', 'descripcion'];

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'rol_modulo', 'rol_id', 'modulo_id')->withTimestamps();
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'rol', 'nombre');
    }
}
