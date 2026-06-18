<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $table = 'modulos';
    protected $fillable = ['nombre', 'slug', 'grupo', 'icono'];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_modulo', 'modulo_id', 'rol_id')->withTimestamps();
    }
}
