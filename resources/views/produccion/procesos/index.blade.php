@extends('layouts.app')

@section('title', 'Procesos de Producción')

@section('content')
<div class="container mx-auto pb-8 md:pb-10">
    <div class="flex justify-between items-center mb-6 gap-4">
        <div>
            <nav class="flex text-sm text-gray-500 mb-2">
                <a href="{{ route('produccion.ordenes.index') }}" class="hover:text-blue-600 font-medium transition-colors">📋 Volver a Órdenes</a>
                <span class="mx-2">›</span>
                <span class="text-gray-700">Orden #{{ $orden->codigo_op }}</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-3">
                Procesos de Producción
                <span class="text-sm bg-gray-100 px-3 py-1 rounded-full text-gray-700 font-medium border border-gray-200">
                    {{ $orden->codigo_op }}
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-1">
                Producto: <span class="font-medium text-gray-700">{{ $orden->descripcion_producto_proceso ?? 'N/A' }}</span> | 
                Fecha: <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') }}</span>
            </p>
        </div>
        <a href="{{ route('ordenes.procesos.create', $orden->idop) }}" class="shrink-0 flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-3 sm:px-4 rounded-lg shadow transition">
            <i class="fas fa-plus"></i>
            <span class="hidden sm:inline ml-2">Agregar Proceso</span>
        </a>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 md:p-4 mb-6 rounded shadow-sm text-sm md:text-base">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 md:p-4 mb-6 rounded shadow-sm text-sm md:text-base">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    {{-- Tarjeta Principal --}}
    {{-- <div class="bg-white p-3 md:p-4 rounded-xl shadow-md mb-6 flex justify-between items-center bg-linear-to-r from-blue-500 to-purple-500">
        <h2 class="text-lg font-medium text-white">Procesos Activos</h2>
        <span class="bg-white bg-opacity-20 text-white px-3 py-1 rounded-full text-xs font-semibold">
            {{ count($procesos) }} proceso(s)
        </span>
    </div> --}}

    <div id="table-container" class="transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if(count($procesos) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-800 text-slate-300 uppercase tracking-wider font-semibold">
                            <th class="p-4 border-r border-slate-700 text-center">Secuencia</th>
                            <th class="p-4 border-r border-slate-700 text-center">Proceso</th>
                            <th class="p-4 border-r border-slate-700 text-center">Estado de Avance</th>
                            <th class="p-4 border-r border-slate-700 text-center">Materiales</th>
                            <th class="p-4 border-r border-slate-700 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($procesos as $p)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Sec. #{{ $p->secuencia }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <strong class="text-gray-900 font-medium">{{ $p->proceso_desc }}</strong>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $estado = $p->estado_avance ?? 'PENDIENTE';
                                @endphp
                                @if($estado == 'COMPLETADO')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">✅ Completado</span>
                                @elseif($estado == 'EN_PROCESO')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">🔄 En Proceso</span>
                                @elseif($estado == 'CANCELADO')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">❌ Cancelado</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⏳ Pendiente</span>
                                @endif
                            </td>
                            <td class="p-4 border-r border-slate-200 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    📦 {{ $p->total_componentes }} items
                                </span>
                            </td>
                            <td class="p-4 border-r border-slate-200 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('ordenes.procesos.ejecutar', [$orden->idop, $p->id]) }}" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors" title="Ejecutar proceso">
                                        <i class="fas fa-play mr-1"></i> Ejecutar
                                    </a>
                                    <button onclick="delProc({{ $p->id }}, '{{ addslashes($p->proceso_desc) }}')" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-500 hover:bg-red-600 shadow-sm" title="Anular proceso">
                                        <i class="fas fa-trash mr-1"></i> Anular
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-gray-500">
            <i class="fas fa-folder-open text-4xl text-gray-300 mb-3 block"></i>
            No hay procesos registrados para esta orden.
        </div>
        @endif
    </div>
    </div>
</div>



{{-- Formulario oculto para eliminar --}}
<form id="formDel" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function delProc(id, name) {
        if(confirm("¿Está seguro de ANULAR el proceso '" + name + "'?\n\n⚠️ Esta acción:\n• Revertirá el stock de materias primas consumidas\n• Anulará los ingresos de productos en proceso\n• Validará que el stock no haya sido utilizado posteriormente\n\nEsta acción no se puede deshacer.")){
            const form = document.getElementById('formDel');
            form.action = `/produccion/procesos/${id}`;
            form.submit();
        }
    }


</script>
@endsection
