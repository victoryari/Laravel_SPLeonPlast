@extends('layouts.app')
@section('title', 'Proveedores')

@section('content')
<div class="container mx-auto pb-8">
    <x-page-header title="Maestro de Proveedores" subtitle="Gestión de proveedores y contactos comerciales">
        <x-slot:actions>
            <a href="{{ route('proveedores.create') }}" class="btn-primary">
                <i class="fas fa-plus mr-2"></i> Nuevo Proveedor
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white p-4 rounded-xl shadow-md mb-6">
        <form action="{{ route('proveedores.index') }}" method="GET" class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" name="search" id="searchInput" value="{{ $search }}" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none" placeholder="Buscar por RUC, Razón Social o Contacto...">
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold">
                        <th class="p-4 border-r border-slate-700 text-center">RUC</th>
                        <th class="p-4 border-r border-slate-700 text-center">Razón Social</th>
                        {{-- <tr><th class="px-6 py-4 font-bold uppercase">Contacto</th> --}}
                        <th class="p-4 border-r border-slate-700 text-center">Teléfono / Email</th>
                        <th class="p-4 border-r border-slate-700 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse ($proveedores as $prov)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-600">{{ $prov->ruc }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $prov->razon_social }}</td>
                            {{-- <td class="px-6 py-4 text-gray-600">{{ $prov->contacto ?? '-' }}</td> --}}
                            <td class="px-6 py-4">
                                <div class="text-xs text-gray-500"><i class="fas fa-phone mr-1"></i> {{ $prov->telefono ?? 'S/T' }}</div>
                                <div class="text-xs text-primary"><i class="fas fa-envelope mr-1"></i> {{ $prov->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <a href="{{ route('proveedores.edit', $prov->id_proveedor) }}" class="inline-flex items-center justify-center w-9 h-9 text-primary bg-primary-50 hover:bg-primary hover:text-white rounded-lg transition">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('proveedores.destroy', $prov->id_proveedor) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de desactivar este proveedor?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-9 h-9 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-empty-state icon="fa-truck" message="No se encontraron proveedores." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($proveedores->hasPages())
            <div class="px-6 py-4 border-t bg-gray-50">
                {{ $proveedores->links() }}
            </div>
        @endif
    </div>
</div>
@endsection