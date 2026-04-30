@extends('layouts.app')
@section('title', 'Panel de Supervisor')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Panel de Control - Supervisor</h1>
        <p class="text-sm text-gray-600 mt-1">Bienvenido al sistema, <span class="font-bold text-blue-600">{{ Auth::user()->nombre_usuario }}</span>.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-b-4 border-b-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Órdenes Activas</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">0</p>
                </div>
                <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 text-2xl shadow-inner">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-b-4 border-b-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pendientes de Validar</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">0</p>
                </div>
                <div class="w-14 h-14 rounded-full bg-yellow-50 flex items-center justify-center text-yellow-500 text-2xl shadow-inner">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-b-4 border-b-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Producción del Día</p>
                    <p class="text-3xl font-black text-gray-800 mt-2">0 <span class="text-sm font-medium text-gray-500">Kg</span></p>
                </div>
                <div class="w-14 h-14 rounded-full bg-green-50 flex items-center justify-center text-green-500 text-2xl shadow-inner">
                    <i class="fas fa-box-open"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 h-64 flex items-center justify-center">
        <div class="text-center">
            <i class="fas fa-chart-area text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500 font-medium">Área de trabajo del supervisor</p>
            <p class="text-xs text-gray-400 mt-1">Aquí irán las validaciones de inventario y procesos de planta.</p>
        </div>
    </div>
</div>
@endsection