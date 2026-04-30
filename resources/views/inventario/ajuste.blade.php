@extends('layouts.app')
@section('title', 'Ajuste Manual de Inventario')

@section('content')
<div class="min-h-screen bg-slate-50 py-10 px-4">
    <div class="max-w-3xl mx-auto">

        @if (session('error'))
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-800">
                Ajuste Manual de Inventario
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Registra correcciones por merma, sobrante o validación de inventario físico.
            </p>
        </div>

        <!-- Card -->
        <div class="rounded-3xl bg-white shadow-xl border border-slate-200 overflow-hidden">
            
            <!-- Top Accent -->
            <div class="h-2 bg-linear-to-r from-blue-600 to-indigo-600"></div>

            <form action="{{ route('inventario.store_ajuste') }}" method="POST" class="p-8 space-y-6">
                @csrf

                <!-- Producto -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Producto
                    </label>
                    <select name="codigo_producto"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                        required>
                        <option value="">-- Buscar producto --</option>
                        @foreach($productos as $p)
                            <option value="{{ $p->codigo }}">
                                {{ $p->codigo }} - {{ $p->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Almacén -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Almacén afectado
                    </label>
                    <select name="codigo_almacen"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                        required>
                        <option value="">-- Seleccione --</option>
                        @foreach($almacenes as $a)
                            <option value="{{ $a->codigo_almacen }}">
                                {{ $a->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tipo -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Tipo de operación
                    </label>
                    <select name="tipo"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                        required>
                        <option value="INGRESO">INGRESO (+)</option>
                        <option value="SALIDA">SALIDA (-)</option>
                    </select>
                </div>

                <!-- Cantidad -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Cantidad
                    </label>
                    <input type="number" name="cantidad" step="0.01" min="0.01"
                        placeholder="0.00"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                        required>
                </div>

                <!-- Motivo -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Motivo del ajuste
                    </label>
                    <textarea name="observaciones" rows="3"
                        placeholder="Escriba el motivo del ajuste..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition"
                        required></textarea>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-4 pt-4 border-t border-slate-100">
                    <a href="{{ route('inventario.index') }}"
                        class="px-6 py-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition">
                        Cancelar
                    </a>

                    <button type="submit"
                        class="px-6 py-3 rounded-xl bg-linear-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold shadow-lg transition">
                        <i class="fas fa-save mr-2"></i>
                        Procesar Ajuste
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection