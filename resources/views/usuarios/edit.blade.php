@extends('layouts.app')
@section('title', 'Editar Usuario')

@section('content')
<div class="container mx-auto max-w-3xl">
    <x-page-header title="Editar Usuario" subtitle="Modifique los datos del usuario." />

    <x-card class="p-6">
        <form action="{{ route('usuarios.update', $usuario->id_usuario) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1">
                    <x-form-group label="Nombre de Usuario" required :error="$errors->first('nombre_usuario')">
                        <input type="text" name="nombre_usuario" id="nombre_usuario" value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}" class="input-field lowercase @error('nombre_usuario') border-red-500 @enderror" required>
                        @error('nombre_usuario') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </x-form-group>
                </div>

                <div class="col-span-1">
                    <x-form-group label="Nueva Contraseña">
                        <input type="password" name="password" id="password" class="input-field @error('password') border-red-500 @enderror" placeholder="Dejar vacío para no cambiar">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </x-form-group>
                </div>

                <div class="col-span-1">
                    <x-form-group label="Rol de Sistema" required>
                        <select name="rol" id="rol" class="input-field" required>
                            @foreach($roles as $rol)
                                <option value="{{ $rol }}" {{ old('rol', $usuario->rol) == $rol ? 'selected' : '' }}>{{ $rol }}</option>
                            @endforeach
                        </select>
                    </x-form-group>
                </div>

                <div class="col-span-1">
                    <x-form-group label="Correo Electrónico">
                        <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}" class="input-field" placeholder="usuario@empresa.com">
                    </x-form-group>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <x-form-group label="Vincular a Trabajador">
                        <select name="codigo_trabajador" id="codigo_trabajador" class="input-field">
                            <option value="">Ninguno (Usuario Externo / Genérico)</option>
                            @foreach($trabajadores as $trabajador)
                                <option value="{{ $trabajador->codigo }}" {{ old('codigo_trabajador', $usuario->codigo_trabajador) == $trabajador->codigo ? 'selected' : '' }}>
                                    {{ $trabajador->codigo }} - {{ $trabajador->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </x-form-group>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6">
                <a href="{{ route('usuarios.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Actualizar Usuario</button>
            </div>
        </form>
    </x-card>
</div>
@endsection