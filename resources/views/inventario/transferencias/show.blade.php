@extends('layouts.app')
@section('title', 'Detalle de Transferencia')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('inventario.transferencias.index') }}" class="hover:text-blue-600 transition"><i class="fas fa-arrow-left"></i> Volver a Historial</a>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Detalle de Transferencia</h1>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition">
                <i class="fas fa-print"></i> Imprimir
            </button>
            @if($transferencia->estado === 'COMPLETADO')
                <form action="{{ route('inventario.transferencias.anular', $transferencia->id_transferencia) }}" method="POST" onsubmit="return confirm('¿Está seguro de anular esta transferencia? Se revertirán los movimientos en ambos almacenes.');">
                    @csrf
                    <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-600 hover:text-white border border-red-200 px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition">
                        <i class="fas fa-ban"></i> Anular
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Cabecera de Documento -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-6 overflow-hidden print-container">
        <div class="bg-slate-800 p-6 flex flex-col md:flex-row justify-between items-center text-white">
            <div class="flex items-center gap-4">
                <div class="bg-blue-500/20 p-3 rounded-xl">
                    <i class="fas fa-exchange-alt text-3xl text-blue-300"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black tracking-wider">{{ $transferencia->numero_transferencia }}</h2>
                    <p class="text-slate-400 text-sm">Transferencia Interna</p>
                </div>
            </div>
            <div class="mt-4 md:mt-0 text-center md:text-right">
                <p class="text-xs text-slate-400 uppercase font-bold">Estado</p>
                @if($transferencia->estado == 'COMPLETADO')
                    <span class="inline-block bg-green-500/20 text-green-300 px-3 py-1 rounded border border-green-500/30 text-sm font-black mt-1">COMPLETADO</span>
                @else
                    <span class="inline-block bg-red-500/20 text-red-300 px-3 py-1 rounded border border-red-500/30 text-sm font-black mt-1">ANULADO</span>
                @endif
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 bg-slate-50 border-b border-slate-100">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Fecha y Hora</p>
                <p class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($transferencia->fecha_transferencia)->format('d/m/Y h:i A') }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Almacén Origen</p>
                <p class="font-bold text-slate-800"><i class="fas fa-sign-out-alt text-red-400 mr-1"></i> {{ $transferencia->almacenOrigen->descripcion ?? $transferencia->codigo_almacen_origen }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Almacén Destino</p>
                <p class="font-bold text-slate-800"><i class="fas fa-sign-in-alt text-green-500 mr-1"></i> {{ $transferencia->almacenDestino->descripcion ?? $transferencia->codigo_almacen_destino }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase mb-1">Registrado por</p>
                <p class="font-bold text-slate-800">{{ $transferencia->usuario->nombres ?? 'Sistema' }}</p>
            </div>
        </div>
        
        @if($transferencia->observaciones)
        <div class="px-6 py-4 border-b border-slate-100">
            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Observaciones</p>
            <p class="text-sm text-slate-700 italic">{{ $transferencia->observaciones }}</p>
        </div>
        @endif

        <div class="p-0">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 border-b border-slate-200">
                    <tr>
                        <th class="p-4 font-bold text-slate-600 text-xs uppercase tracking-wider">Código</th>
                        <th class="p-4 font-bold text-slate-600 text-xs uppercase tracking-wider">Descripción</th>
                        <th class="p-4 font-bold text-slate-600 text-xs uppercase tracking-wider text-center">Lote Transferido</th>
                        <th class="p-4 font-bold text-slate-600 text-xs uppercase tracking-wider text-right">Cantidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($transferencia->detalles as $det)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-mono text-xs text-slate-500">{{ $det->codigo_producto }}</td>
                        <td class="p-4 font-bold text-slate-800">{{ $det->producto->descripcion ?? 'Producto Desconocido' }}</td>
                        <td class="p-4 text-center">
                            <span class="inline-block bg-slate-100 border border-slate-200 px-2 py-1 rounded text-xs font-mono font-bold text-slate-700">{{ $det->lote ?? 'N/A' }}</span>
                        </td>
                        <td class="p-4 text-right font-black text-blue-600 text-base">
                            {{ number_format($det->cantidad, 2) }} <span class="text-xs font-bold text-slate-400 ml-1">{{ $det->producto->codigo_unidad_medida ?? 'UND' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bloque de firmas para impresión -->
    <div class="hidden print:flex justify-around mt-20 pt-10">
        <div class="text-center w-48 border-t-2 border-slate-800 pt-2">
            <p class="text-xs font-bold text-slate-800">ENTREGADO POR</p>
            <p class="text-[10px] text-slate-500 mt-1">Almacén Origen</p>
        </div>
        <div class="text-center w-48 border-t-2 border-slate-800 pt-2">
            <p class="text-xs font-bold text-slate-800">RECIBIDO POR</p>
            <p class="text-[10px] text-slate-500 mt-1">Almacén Destino</p>
        </div>
    </div>
</div>

<style>
    @media print {
        body { background: white !important; }
        .container { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
        nav, header, aside, .btn, button, a { display: none !important; }
        .print-container { box-shadow: none !important; border: 1px solid #e2e8f0 !important; border-radius: 0 !important; margin-top: 20px !important; }
        .hidden.print\:flex { display: flex !important; }
    }
</style>
@endsection
