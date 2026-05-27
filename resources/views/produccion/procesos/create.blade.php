@extends('layouts.app')

@section('title', 'Agregar Proceso')

@section('content')
<div class="container mx-auto max-w-3xl pb-8 md:pb-10">
    <!-- Breadcrumbs / Header -->
    <div class="mb-6">
        <nav class="flex text-sm text-gray-500 mb-2">
            <a href="{{ route('produccion.ordenes.index') }}" class="hover:text-primary transition-colors">Órdenes</a>
            <span class="mx-2">›</span>
            <a href="{{ route('ordenes.procesos.index', $orden->idop) }}" class="hover:text-primary transition-colors">Orden #{{ $orden->codigo_op }}</a>
            <span class="mx-2">›</span>
            <span class="text-gray-700">Agregar Proceso</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-plus-circle mr-3 text-primary"></i> Agregar Nuevo Proceso
        </h1>
        <p class="text-sm text-gray-600 mt-1">
            Orden de Producción: <span class="font-semibold text-gray-800">{{ $orden->codigo_op }}</span>
        </p>
    </div>

    <!-- Mensajes de Error -->
    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario Principal -->
    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-primary">
        <form action="{{ route('ordenes.procesos.store', $orden->idop) }}" method="POST">
            @csrf
            
            <div class="space-y-6">
                <div>
                    <label for="codigo_proceso" class="block text-sm font-medium text-gray-700 mb-2">
                        Seleccionar Tipo de Proceso <span class="text-red-500">*</span>
                    </label>
                    <select name="codigo_proceso" id="codigo_proceso" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                        <option value="">-- Seleccione un proceso --</option>
                        @foreach($cat_procesos as $c)
                            <option value="{{ $c->codigo }}" {{ old('codigo_proceso') == $c->codigo ? 'selected' : '' }}>
                                {{ $c->descripcion }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500">
                        Los procesos se agregarán secuencialmente (10, 20, 30...) para facilitar el orden de ejecución.
                    </p>
                </div>

                <!-- Detalles Informativos -->
                <div class="bg-primary-50 border border-primary-50 rounded-lg p-4">
                    <h4 class="text-primary font-semibold text-sm mb-2 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i> Información del Producto
                    </h4>
                    <p class="text-xs text-primary leading-relaxed">
                        El proceso seleccionado se vinculará a la orden de fabricación del producto: 
                        <span class="font-bold uppercase">{{ $orden->descripcion_producto_proceso ?? 'N/A' }}</span>.
                    </p>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row justify-end gap-3">
                <a href="{{ route('ordenes.procesos.index', $orden->idop) }}" class="flex items-center justify-center px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium">
                    Cancelar
                </a>
                <button type="submit" class="flex items-center justify-center px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark shadow-md transition font-bold">
                    <i class="fas fa-save mr-2"></i> Guardar y Continuar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
