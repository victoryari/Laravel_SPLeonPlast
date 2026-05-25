@extends('layouts.app')
@section('title', 'Roles y Permisos')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Roles y Permisos</h1>
            <p class="text-xs sm:text-sm text-gray-600">Gestión de accesos y configuración de módulos</p>
        </div>
        <a href="{{ route('roles.create') }}" class="shrink-0 flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-3 sm:px-4 rounded-lg shadow transition">
            <i class="fas fa-plus"></i>
            <span class="hidden sm:inline ml-2">Nuevo Rol</span>
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 md:p-4 mb-6 rounded shadow-sm text-sm md:text-base">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 md:p-4 mb-6 rounded shadow-sm text-sm md:text-base">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold">
                        <th class="p-4 border-r border-slate-700">Nombre del Rol</th>
                        <th class="p-4 border-r border-slate-700">Descripción</th>
                        <th class="p-4 border-r border-slate-700 text-center">Usuarios Asignados</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($roles as $rol)
                        <tr class="hover:bg-slate-50/50 transition duration-150">
                            <td class="px-4 md:px-6 py-3 md:py-4 font-bold text-gray-900">
                                <i class="fas fa-user-shield text-blue-500 mr-2"></i> {{ $rol->nombre }}
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600">
                                {{ $rol->descripcion ?? 'Sin descripción' }}
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold text-xs">
                                    {{ $rol->usuarios_count }}
                                </span>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('roles.edit', $rol->id) }}" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-all" title="Configurar Permisos">
                                        <i class="fas fa-edit text-sm md:text-lg"></i>
                                    </a>
                                    @if($rol->nombre !== 'Administrador')
                                    <form action="{{ route('roles.destroy', $rol->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de eliminar este rol?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 md:w-10 md:h-10 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all" title="Eliminar">
                                            <i class="fas fa-trash-alt text-sm md:text-lg"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 md:py-16 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-shield-alt text-3xl md:text-4xl mb-3 text-gray-200"></i>
                                    <p class="text-sm md:text-base">No se encontraron roles creados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
