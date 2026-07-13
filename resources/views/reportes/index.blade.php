@extends('layouts.app')
@section('title', 'Reportes')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Módulo de Reportes</h1>
        <p class="text-sm text-slate-600 mt-1">Seleccione el tipo de reporte que desea consultar</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="{{ route('reportes.produccion') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 hover:shadow-md hover:border-primary/20 transition-all group">
            <div class="w-16 h-16 rounded-2xl bg-primary-50 flex items-center justify-center text-primary text-3xl mb-4 group-hover:bg-primary-100 transition">
                <i class="fas fa-industry"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Reporte de Producción</h3>
            <p class="text-sm text-slate-500">Órdenes de producción, cantidades procesadas y eficiencia por período.</p>
        </a>

        <a href="{{ route('reportes.inventario') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 hover:shadow-md hover:border-green-200 transition-all group">
            <div class="w-16 h-16 rounded-2xl bg-green-50 flex items-center justify-center text-green-500 text-3xl mb-4 group-hover:bg-green-100 transition">
                <i class="fas fa-boxes"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Reporte de Inventario</h3>
            <p class="text-sm text-slate-500">Stock actual de productos, insumos y materiales por almacén.</p>
        </a>

        <a href="#" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 opacity-60 cursor-not-allowed">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 text-3xl mb-4">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Reporte de Compras</h3>
            <p class="text-sm text-slate-500">Próximamente</p>
        </a>
    </div>
</div>
@endsection
