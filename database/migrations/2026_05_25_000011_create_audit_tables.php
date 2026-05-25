<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // usuarios (the actual auth table used by the app)
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('codigo_trabajador', 12)->nullable();
            $table->string('nombre_usuario', 50)->unique();
            $table->string('contrasena_hash', 255);
            $table->string('email', 100)->nullable();
            $table->string('rol', 50);
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('ultimo_login')->nullable();
            $table->boolean('activo')->default(true);
        });

        // bitacora_sistema
        Schema::create('bitacora_sistema', function (Blueprint $table) {
            $table->id('id_bitacora');
            $table->integer('id_usuario')->nullable()->unsigned();
            $table->string('accion', 100);
            $table->string('modulo', 50);
            $table->text('descripcion')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
        });

        // parametros_sistema
        Schema::create('parametros_sistema', function (Blueprint $table) {
            $table->id('id_parametro');
            $table->string('codigo_parametro', 50)->unique();
            $table->string('descripcion', 200);
            $table->text('valor');
            $table->enum('tipo', ['TEXTO','NUMERICO','BOOLEANO','FECHA'])->default('TEXTO');
            $table->string('categoria', 50)->default('GENERAL');
            $table->boolean('editable')->default(true);
            $table->timestamp('fecha_actualizacion')->useCurrent()->useCurrentOnUpdate();
            $table->integer('usuario_actualizacion')->nullable()->unsigned();
        });

        // user_logins
        Schema::create('user_logins', function (Blueprint $table) {
            $table->id('id_login');
            $table->integer('id_usuario')->unsigned();
            $table->timestamp('fecha_login')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('exito')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_logins');
        Schema::dropIfExists('parametros_sistema');
        Schema::dropIfExists('bitacora_sistema');
        Schema::dropIfExists('usuarios');
    }
};
