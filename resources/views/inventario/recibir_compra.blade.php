@extends('layouts.app')
@section('title', 'Recibir Mercadería')

@section('content')
<div class="container mx-auto pb-10">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Confirmar Recepción Física</h1>
        <p class="text-sm text-gray-500">Verifique las cantidades reales antes de ingresar al Kardex.</p>
    </div>

    <form action="{{ route('inventario.procesar_recepcion', $compra->id_compra) }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow border overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b">
                            <tr class="text-xs font-bold text-gray-500 uppercase">
                                <th class="p-4">Producto</th>
                                <th class="p-4 text-center">Almacén Destino</th>
                                <th class="p-4 text-center">Cant. Comprada</th>
                                <th class="p-4 text-center w-40">Cant. Recibida</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($compra->detalles as $detalle)
                                <tr>
                                    <td class="p-4 font-medium">
                                        {{ $detalle->producto->descripcion ?? $detalle->descripcion_producto ?? 'Producto no encontrado' }}
                                        
                                        <input type="hidden" name="items[{{ $detalle->id_detalle_compra }}][codigo_producto]" value="{{ $detalle->codigo_producto }}">
                                        <input type="hidden" name="items[{{ $detalle->id_detalle_compra }}][codigo_almacen]" value="{{ $detalle->codigo_almacen }}">
                                        <input type="hidden" name="items[{{ $detalle->id_detalle_compra }}][precio]" value="{{ $detalle->precio_unitario }}">
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs font-bold">
                                            {{ $detalle->almacen->descripcion ?? $detalle->codigo_almacen ?? 'S/N' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center text-gray-600">{{ number_format($detalle->cantidad, 2) }}</td>
                                    <td class="p-4">
                                        <input type="number" name="items[{{ $detalle->id_detalle_compra }}][cantidad]" 
                                               value="{{ $detalle->cantidad }}" step="0.01" 
                                               class="w-full border-blue-200 bg-blue-50 rounded-lg text-center font-bold text-blue-700 focus:ring-blue-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow border sticky top-6">
                    <h2 class="font-bold text-gray-700 mb-4 border-b pb-2 italic text-sm">Resumen de Recepción</h2>
                    <div class="space-y-3 text-xs mb-6">
                        <p><strong>Documento:</strong> {{ $compra->tipo_documento }} {{ $compra->serie_documento }}-{{ $compra->numero_documento }}</p>
                        <p><strong>Proveedor:</strong> {{ $compra->datosProveedor->razon_social ?? $compra->proveedor ?? 'S/N' }}</p>
                        <p><strong>Total Items:</strong> {{ $compra->detalles->count() }}</p>
                    </div>
                    <button type="submit" class="w-full py-4 bg-green-600 text-white rounded-xl font-black hover:bg-green-700 shadow-xl transition">
                        <i class="fas fa-save mr-2"></i> FINALIZAR INGRESO
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection