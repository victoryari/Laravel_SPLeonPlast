<div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div>
            <p class="text-xs text-gray-500 font-bold uppercase">Código y Producto Origen</p>
            <p class="text-sm font-semibold text-gray-900">{{ $merma->codigo_producto }}</p>
            <p class="text-sm text-gray-700">{{ $merma->descripcion_producto }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-bold uppercase">Orden de Producción</p>
            <p class="text-sm font-semibold text-gray-900">OP-{{ $merma->ordenProduccion->codigo_op ?? 'N/A' }}</p>
            <p class="text-sm text-gray-700">{{ $merma->ordenProduccion->descripcion_producto_proceso ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-bold uppercase">Fecha y Almacén</p>
            <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($merma->fecha_merma)->format('d/m/Y') }} | {{ $merma->codigo_almacen }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-bold uppercase">Trabajador y Horario</p>
            @if($merma->trabajador)
                <p class="text-sm text-gray-900 font-semibold">{{ $merma->trabajador->nombre }}</p>
                <p class="text-xs text-gray-600">{{ $merma->hora_inicio ? \Carbon\Carbon::parse($merma->hora_inicio)->format('H:i') : '--:--' }} a {{ $merma->hora_fin ? \Carbon\Carbon::parse($merma->hora_fin)->format('H:i') : '--:--' }}</p>
            @else
                <p class="text-sm text-gray-500 italic">No asignado</p>
            @endif
        </div>
        <div class="md:col-span-2">
            <p class="text-xs text-gray-500 font-bold uppercase">Motivo</p>
            <p class="text-sm text-gray-700">{{ $merma->motivo ?: 'Sin motivo especificado' }}</p>
        </div>
    </div>

    <h4 class="text-md font-bold text-slate-800 mb-3 border-b pb-2">Movimientos de Inventario Generados (Kardex)</h4>
    <div class="overflow-x-auto">
        <table class="w-full text-left whitespace-nowrap text-sm border">
            <thead class="bg-slate-100 text-slate-700 border-b">
                <tr>
                    <th class="px-4 py-2 font-bold uppercase">Tipo</th>
                    <th class="px-4 py-2 font-bold uppercase">Producto</th>
                    <th class="px-4 py-2 font-bold uppercase text-right">Cantidad</th>
                    <th class="px-4 py-2 font-bold uppercase text-right">Costo Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($movimientos as $mov)
                <tr>
                    <td class="px-4 py-2 text-center">
                        @if($mov->tipo_movimiento === 'SALIDA')
                            <span class="px-2 py-1 text-xs font-bold text-red-600 bg-red-100 rounded">SALIDA (Consumo)</span>
                        @else
                            <span class="px-2 py-1 text-xs font-bold text-green-600 bg-green-100 rounded">INGRESO (Recuperado)</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        <div class="font-bold text-gray-900">{{ $mov->codigo_producto }}</div>
                    </td>
                    <td class="px-4 py-2 text-right font-medium">
                        {{ number_format($mov->tipo_movimiento === 'SALIDA' ? $mov->cantidad_salida : $mov->cantidad_entrada, 2) }}
                    </td>
                    <td class="px-4 py-2 text-right font-medium text-slate-600">
                        S/ {{ number_format($mov->tipo_movimiento === 'SALIDA' ? $mov->total_salida : $mov->total_entrada, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">No se encontraron movimientos para esta merma.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
