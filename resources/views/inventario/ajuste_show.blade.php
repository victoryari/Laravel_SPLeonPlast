@extends('layouts.app')
@section('title', 'Detalle del Ajuste #' . $ajuste->id_kardex)

@section('content')
<div class="min-h-screen bg-slate-50 py-10 px-4">
    <div class="max-w-4xl mx-auto">

        <div class="mb-6">
            <a href="{{ route('inventario.ajuste.lista') }}"
                class="text-sm text-blue-600 hover:text-blue-800 transition">
                <i class="fas fa-arrow-left mr-1"></i> Volver a la Bandeja de Ajustes
            </a>
        </div>

        <div class="rounded-3xl bg-white shadow-xl border border-slate-200 overflow-hidden">
            <div class="h-2 bg-linear-to-r from-blue-600 to-indigo-600"></div>

            <div class="p-8">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Ajuste #{{ $ajuste->id_kardex }}</h1>
                        <p class="text-sm text-slate-500 mt-1">
                            Documento: <span class="font-mono font-semibold">{{ $ajuste->numero_documento }}</span>
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('inventario.ajuste.edit', $ajuste->id_kardex) }}"
                            class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold shadow transition">
                            <i class="fas fa-edit mr-1"></i> Editar
                        </a>
                        <form action="{{ route('inventario.ajuste.destroy', $ajuste->id_kardex) }}" method="POST"
                            onsubmit="return confirm('¿Está seguro de eliminar este ajuste?');"
                            class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold shadow transition">
                                <i class="fas fa-trash mr-1"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>

                @if(session('error'))
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Información del Ajuste</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Producto</dt>
                                <dd class="text-sm font-semibold text-slate-800 text-right max-w-[60%]">{{ $ajuste->codigo_producto }} - {{ $ajuste->producto }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Almacén</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ $ajuste->almacen }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Unidad de Medida</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ $ajuste->unidad_medida ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Tipo</dt>
                                <dd>
                                    @if($ajuste->cantidad_entrada > 0)
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">INGRESO</span>
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">SALIDA</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Cantidad</dt>
                                <dd class="text-sm font-semibold {{ $ajuste->cantidad_entrada > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $ajuste->cantidad_entrada > 0 ? '+' : '-' }}{{ number_format($ajuste->cantidad_entrada > 0 ? $ajuste->cantidad_entrada : $ajuste->cantidad_salida, 2) }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Saldo después del ajuste</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ number_format($ajuste->cantidad_saldo, 2) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Detalles del Registro</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Fecha del movimiento</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ \Carbon\Carbon::parse($ajuste->fecha_movimiento)->format('d/m/Y H:i:s') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Registrado por</dt>
                                <dd class="text-sm font-semibold text-slate-800">{{ $ajuste->usuario_nombre ?? 'Usuario #' . $ajuste->usuario_registro }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-slate-500">Documento</dt>
                                <dd class="text-sm font-mono font-semibold text-slate-800">{{ $ajuste->numero_documento }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 mb-8">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Motivo / Observaciones</h3>
                    <p class="text-sm text-slate-700">{{ $ajuste->observaciones ?? 'Sin observaciones' }}</p>
                </div>

                @if($movimientosPosteriores->isNotEmpty())
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 mb-8">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                        <div>
                            <h3 class="text-sm font-bold text-amber-800 mb-2">Movimientos posteriores detectados</h3>
                            <p class="text-xs text-amber-700 mb-3">
                                Este ajuste tiene {{ $movimientosPosteriores->count() }} movimiento(s) posterior(es) para el mismo producto/almacén.
                                No se puede eliminar mientras existan dependencias.
                            </p>
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="border-b border-amber-200 text-amber-700">
                                            <th class="text-left py-2 px-2 font-semibold">Fecha</th>
                                            <th class="text-left py-2 px-2 font-semibold">Tipo</th>
                                            <th class="text-left py-2 px-2 font-semibold">Documento</th>
                                            <th class="text-right py-2 px-2 font-semibold">Entrada</th>
                                            <th class="text-right py-2 px-2 font-semibold">Salida</th>
                                            <th class="text-right py-2 px-2 font-semibold">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($movimientosPosteriores as $m)
                                        <tr class="border-b border-amber-100 text-amber-900">
                                            <td class="py-2 px-2">{{ \Carbon\Carbon::parse($m->fecha_movimiento)->format('d/m/Y H:i') }}</td>
                                            <td class="py-2 px-2">
                                                <span class="inline-block px-1.5 py-0.5 rounded text-xs font-bold
                                                    {{ $m->tipo_movimiento == 'INGRESO' ? 'bg-green-100 text-green-700' : ($m->tipo_movimiento == 'SALIDA' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600') }}">
                                                    {{ $m->tipo_movimiento }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-2 font-mono">{{ $m->numero_documento }}</td>
                                            <td class="py-2 px-2 text-right font-mono text-green-600">{{ $m->cantidad_entrada > 0 ? number_format($m->cantidad_entrada, 2) : '—' }}</td>
                                            <td class="py-2 px-2 text-right font-mono text-red-600">{{ $m->cantidad_salida > 0 ? number_format($m->cantidad_salida, 2) : '—' }}</td>
                                            <td class="py-2 px-2 text-right font-mono font-semibold">{{ number_format($m->cantidad_saldo, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xs text-amber-700 mt-3">
                                <i class="fas fa-lightbulb mr-1"></i>
                                Si necesita revertir el efecto de este ajuste, utilice la opción <strong>Extornos</strong> en el menú de Inventario.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('inventario.ajuste.lista') }}"
                        class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
