@extends('layouts.app')
@section('title', 'Nuevo Usuario')

@section('content')
<div class="container mx-auto max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Registrar Usuario</h1>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-500">
        <form action="{{ route('usuarios.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nombre de Usuario --}}
                <div class="col-span-1">
                    <label for="nombre_usuario" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre de Usuario <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre_usuario" id="nombre_usuario" value="{{ old('nombre_usuario') }}" 
                           class="w-full px-4 py-2 border rounded-lg lowercase focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('nombre_usuario') ? 'border-red-500' : 'border-gray-300' }}" 
                           placeholder="Ej: jsmith" required>
                    @error('nombre_usuario')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div class="col-span-1">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" id="password" 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('password') ? 'border-red-500' : 'border-gray-300' }}" 
                           placeholder="Mínimo 6 caracteres" required>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Rol --}}
                <div class="col-span-1">
                    <label for="rol" class="block text-sm font-medium text-gray-700 mb-1">
                        Rol de Sistema <span class="text-red-500">*</span>
                    </label>
                    <select name="rol" id="rol" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Seleccione...</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol }}" {{ old('rol') == $rol ? 'selected' : '' }}>{{ $rol }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Email --}}
                <div class="col-span-1">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo Electrónico <span class="text-xs text-gray-400 font-normal">(Opcional)</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="usuario@empresa.com">
                </div>

                {{-- Trabajador --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="codigo_trabajador" class="block text-sm font-medium text-gray-700 mb-1">
                        Vincular a Trabajador <span class="text-red-500">*</span>
                    </label>
                    <select name="codigo_trabajador" id="codigo_trabajador" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Seleccione un trabajador...</option>
                        @foreach($trabajadores as $trabajador)
                            <option value="{{ $trabajador->codigo }}" {{ old('codigo_trabajador') == $trabajador->codigo ? 'selected' : '' }}>
                                {{ $trabajador->codigo }} - {{ $trabajador->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6">
                <a href="{{ route('usuarios.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md transition font-semibold">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>
@endsection