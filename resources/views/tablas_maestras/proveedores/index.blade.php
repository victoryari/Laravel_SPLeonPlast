@extends('layouts.app')
@section('title', 'Proveedores')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <x-page-header title="Maestro de Proveedores" subtitle="Gestión de proveedores y contactos comerciales">
        <x-slot:actions>
            <a href="{{ route('proveedores.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline ml-2">Nuevo Proveedor</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white p-3 md:p-4 rounded-xl shadow-md border border-slate-200/80 mb-5">
        <form action="{{ route('proveedores.index') }}" method="GET" class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400 text-sm"></i>
            </div>
            <input type="text" name="search" id="searchInput" value="{{ $search }}" class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm text-slate-800 outline-none transition shadow-xs" placeholder="Buscar por RUC, Razón Social o Contacto...">
        </form>
    </div>

    <div id="table-container" class="transition-opacity duration-300">
        <div class="bg-white rounded-xl shadow-md border border-slate-200/80 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-800 text-white text-[11px] uppercase tracking-wider font-bold">
                            <th class="py-3 px-4 md:px-6 w-36">RUC</th>
                            <th class="py-3 px-4 md:px-6">Razón Social</th>
                            <th class="py-3 px-4 md:px-6">Teléfono / Email</th>
                            <th class="py-3 px-4 md:px-6 text-center w-36">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($proveedores as $prov)
                            <tr class="hover:bg-slate-50/80 transition duration-150">
                                <td class="px-4 md:px-6 py-2.5 font-bold text-xs md:text-sm text-slate-900 whitespace-nowrap">{{ $prov->ruc }}</td>
                                <td class="px-4 md:px-6 py-2.5 text-xs md:text-sm font-medium text-slate-800 uppercase">{{ $prov->razon_social }}</td>
                                <td class="px-4 md:px-6 py-2.5 text-xs">
                                    <div class="text-slate-700 font-medium"><i class="fas fa-phone text-slate-400 mr-1"></i> {{ $prov->telefono ?? 'S/T' }}</div>
                                    <div class="text-primary font-medium"><i class="fas fa-envelope text-slate-400 mr-1"></i> {{ $prov->email ?? '-' }}</div>
                                </td>
                                <td class="px-4 md:px-6 py-2.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5 md:gap-2">
                                        <a href="{{ route('proveedores.edit', $prov->id_proveedor) }}" class="inline-flex items-center justify-center w-8 h-8 text-primary bg-primary-50 hover:bg-primary hover:text-white rounded-lg transition-all shadow-2xs" title="Editar">
                                            <i class="fas fa-edit text-xs md:text-sm"></i>
                                        </a>
                                        <form action="{{ route('proveedores.destroy', $prov->id_proveedor) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de desactivar este proveedor?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all shadow-2xs" title="Desactivar">
                                                <i class="fas fa-trash-alt text-xs md:text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10">
                                    <x-empty-state icon="fa-truck" message="No se encontraron proveedores." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($proveedores->hasPages())
                <div class="px-4 md:px-6 py-3.5 border-t border-slate-100 bg-slate-50">
                    {{ $proveedores->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection