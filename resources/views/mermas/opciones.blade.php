@extends('layouts.app')
@section('title', 'Opciones de Registro de Merma')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">
    <x-page-header title="Registro de Mermas y Molido" subtitle="Seleccione el tipo de merma o material recuperado que desea registrar">
        <x-slot:actions>
            <a href="{{ route('mermas.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mt-6">
        <!-- Tarjeta: Merma Pura -->
        <a href="{{ route('mermas.create', ['tipo' => 'pura']) }}" class="group bg-white rounded-2xl shadow-sm border border-slate-200 p-8 flex flex-col items-center text-center hover:shadow-md transition-all hover:border-red-200 cursor-pointer">
            <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mb-6 group-hover:bg-red-500 group-hover:text-white transition-colors">
                <i class="fas fa-trash-alt text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Merma Pura</h3>
            <p class="text-sm text-slate-500">Material irrecuperable que va a la basura. Genera salida del inventario.</p>
        </a>

        <!-- Tarjeta: Molido -->
        <a href="{{ route('mermas.create', ['tipo' => 'molido']) }}" class="group bg-white rounded-2xl shadow-sm border border-slate-200 p-8 flex flex-col items-center text-center hover:shadow-md transition-all hover:border-emerald-200 cursor-pointer">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mb-6 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                <i class="fas fa-recycle text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Molido (Recuperable)</h3>
            <p class="text-sm text-slate-500">Material defectuoso que se vuelve a moler. Genera ingreso de material REC.</p>
        </a>

        <!-- Tarjeta: Limpieza -->
        <a href="{{ route('mermas.create', ['tipo' => 'limpieza']) }}" class="group bg-white rounded-2xl shadow-sm border border-slate-200 p-8 flex flex-col items-center text-center hover:shadow-md transition-all hover:border-blue-200 cursor-pointer">
            <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mb-6 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                <i class="fas fa-broom text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Limpieza (Purga)</h3>
            <p class="text-sm text-slate-500">Consumo proporcional de insumos vírgenes por purga de máquina.</p>
        </a>

        <!-- Tarjeta: Recuperado en Máquina -->
        <a href="{{ route('mermas.create', ['tipo' => 'recuperado_maquina']) }}" class="group bg-white rounded-2xl shadow-sm border border-slate-200 p-8 flex flex-col items-center text-center hover:shadow-md transition-all hover:border-purple-200 cursor-pointer">
            <div class="w-16 h-16 bg-purple-50 text-purple-500 rounded-full flex items-center justify-center mb-6 group-hover:bg-purple-500 group-hover:text-white transition-colors">
                <i class="fas fa-cogs text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Recuperado Molido</h3>
            <p class="text-sm text-slate-500">Material ya molido directamente en la máquina. Genera ingreso de MO07.</p>
        </a>
    </div>
</div>
@endsection
