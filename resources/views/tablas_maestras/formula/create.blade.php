@extends('layouts.app')
@section('title', 'Nueva Fórmula')

@section('content')
<div class="container mx-auto max-w-2xl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Registrar Fórmula Base</h1>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-500">
        <form action="{{ route('formulas.store') }}" method="POST">
            @csrf
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Código de Fórmula <span class="text-red-500">*</span></label>
                <input type="text" name="codigo" value="{{ old('codigo') }}" class="w-full px-4 py-2 border rounded-lg uppercase focus:ring-blue-500" required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                <input type="text" name="descripcion" value="{{ old('descripcion') }}" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" required>
            </div>
            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('formulas.index') }}" class="px-6 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 transition">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md transition font-semibold">Crear Formula</button>
            </div>
        </form>
    </div>
</div>
@endsection