<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Control de Producción</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .header td {
            vertical-align: top;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table th, .info-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .info-table th {
            background-color: #f2f2f2;
            width: 25%;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        .data-table th {
            background-color: #e0e0e0;
            font-weight: bold;
        }
        .text-left {
            text-align: left !important;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td style="width: 30%;">
                    <!-- Aquí se podría colocar un logo si existiera ruta absoluta o base64 -->
                    <h2>LEONPLAST</h2>
                </td>
                <td style="width: 40%; text-align: center;">
                    <div class="title">REPORTE DE CONTROL DE PRODUCCIÓN</div>
                    <div>OP: {{ $op->codigo_op ?? 'S/N' }}</div>
                </td>
                <td style="width: 30%; text-align: right;">
                    Fecha de Impresión: {{ date('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <th>Centro de Trabajo:</th>
            <td>{{ $op->descripcion_centro_trabajo_produccion ?? '-' }}</td>
            <th>Producto:</th>
            <td>{{ $op->descripcion_producto_proceso ?? '-' }}</td>
        </tr>
        <tr>
            <th>Kilos por Color:</th>
            <td>{{ number_format($kilosPorColor, 2) }} kg</td>
            <th>Orden de Producción:</th>
            <td>{{ $op->codigo_op ?? '-' }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">Fecha</th>
                <th style="width: 12%;">Color</th>
                <th style="width: 20%;">Operario</th>
                <th style="width: 8%;">Hora Inicio</th>
                <th style="width: 8%;">Hora Fin</th>
                <th style="width: 10%;">Cantidad (kg)</th>
                <th style="width: 34%;">Observación (Motivo)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registros as $reg)
            <tr style="{{ $reg->tipo == 'MERMA' ? 'color: #d9534f;' : '' }}">
                <td>{{ \Carbon\Carbon::parse($reg->fecha)->format('d/m/Y') }}</td>
                <td>{{ $reg->color }}</td>
                <td class="text-left">{{ $reg->trabajador_nombre ?? '-' }}</td>
                <td>{{ $reg->hora_inicio ? \Carbon\Carbon::parse($reg->hora_inicio)->format('H:i') : '-' }}</td>
                <td>{{ $reg->hora_fin ? \Carbon\Carbon::parse($reg->hora_fin)->format('H:i') : '-' }}</td>
                <td>{{ number_format($reg->cantidad, 2) }}</td>
                <td class="text-left">
                    @if($reg->tipo == 'INGRESO')
                        <strong style="color: #5cb85c;">[PRODUCCIÓN]</strong> 
                    @elseif($reg->tipo == 'ACTIVIDAD')
                        <strong style="color: #14b8a6;">[ACTIVIDAD]</strong> 
                    @else
                        <strong style="color: #d9534f;">{{ $reg->prefix ?? '[MERMA]' }}</strong> 
                    @endif
                    {{ $reg->motivo ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">No hay registros de control ingresados para esta Orden de Producción.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
