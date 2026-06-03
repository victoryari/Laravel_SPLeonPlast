<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kardex Valorizado</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; color: #1e3a8a; }
        .header p { margin: 2px 0; font-size: 11px; color: #4b5563; }
        .filters { margin-bottom: 15px; background-color: #f3f4f6; padding: 10px; border-radius: 4px; }
        .filters strong { margin-right: 5px; }
        .filters span { margin-right: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #d1d5db; padding: 5px; text-align: center; }
        th { background-color: #4f46e5; color: white; font-size: 9px; }
        td { font-size: 9px; color: #111827; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background-color: #e5e7eb; }
        .badge { padding: 2px 4px; border-radius: 3px; font-size: 8px; color: white; }
        .badge-green { background-color: #10b981; }
        .badge-red { background-color: #ef4444; }
        .badge-gray { background-color: #6b7280; }
    </style>
</head>
<body>

    <div class="header">
        <h1>REPORTE DE KARDEX VALORIZADO</h1>
        <p>Generado el: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="filters">
        @if(!empty($filtros['fecha_desde'])) <strong>Desde:</strong> <span>{{ $filtros['fecha_desde'] }}</span> @endif
        @if(!empty($filtros['fecha_hasta'])) <strong>Hasta:</strong> <span>{{ $filtros['fecha_hasta'] }}</span> @endif
        @if(!empty($filtros['codigo_producto'])) <strong>Producto:</strong> <span>{{ $filtros['codigo_producto'] }}</span> @endif
        @if(!empty($filtros['codigo_almacen'])) <strong>Almacén:</strong> <span>{{ $filtros['codigo_almacen'] }}</span> @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>FECHA</th>
                <th>PRODUCTO</th>
                <th>DOC. REF</th>
                <th>TIPO</th>
                <th>ENTRADA (CANT)</th>
                <th>C.U. ENT</th>
                <th>TOTAL ENT</th>
                <th>SALIDA (CANT)</th>
                <th>C.U. SAL</th>
                <th>TOTAL SAL</th>
                <th>SALDO (CANT)</th>
                <th>C. PROMEDIO</th>
                <th>TOTAL SALDO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientos as $mov)
            <tr>
                <td>{{ \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/y H:i') }}</td>
                <td class="text-left">{{ Str::limit($mov->producto, 20) }}</td>
                <td>{{ $mov->numero_documento }}</td>
                <td>{{ $mov->tipo_movimiento }}</td>
                <td>{{ $mov->cantidad_entrada > 0 ? number_format($mov->cantidad_entrada, 2) : '-' }}</td>
                <td>{{ $mov->costo_entrada > 0 ? number_format($mov->costo_entrada, 4) : '-' }}</td>
                <td>{{ $mov->total_entrada > 0 ? number_format($mov->total_entrada, 2) : '-' }}</td>
                <td>{{ $mov->cantidad_salida > 0 ? number_format($mov->cantidad_salida, 2) : '-' }}</td>
                <td>{{ $mov->costo_salida > 0 ? number_format($mov->costo_salida, 4) : '-' }}</td>
                <td>{{ $mov->total_salida > 0 ? number_format($mov->total_salida, 2) : '-' }}</td>
                <td><strong>{{ number_format($mov->cantidad_saldo, 2) }}</strong></td>
                <td>{{ number_format($mov->costo_promedio, 9) }}</td>
                <td><strong>{{ number_format($mov->total_saldo, 9) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTALES:</td>
                <td>{{ number_format($resumen->total_entradas, 2) }}</td>
                <td></td>
                <td>{{ number_format($resumen->total_entradas_val, 2) }}</td>
                <td>{{ number_format($resumen->total_salidas, 2) }}</td>
                <td></td>
                <td>{{ number_format($resumen->total_salidas_val, 2) }}</td>
                <td>{{ number_format($resumen->saldo_final_cantidad, 2) }}</td>
                <td></td>
                <td>{{ number_format($resumen->saldo_final_val, 2) }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
