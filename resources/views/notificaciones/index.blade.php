@extends('layouts.app')

@section('title', 'Gestor de Notificaciones')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-bell text-emerald-600"></i>
                Gestor de Notificaciones
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Centro de alertas y avisos del sistema en tiempo real.
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if($conteoNoLeidas > 0)
                <form action="{{ route('notificaciones.marcar_todas_leidas') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-sm font-semibold rounded-xl border border-emerald-200 transition-colors flex items-center gap-2">
                        <i class="fas fa-check-double"></i>
                        <span>Marcar todas como leídas</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Filtros por Pestañas -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="flex border-b border-slate-200 bg-slate-50/50 px-6 pt-4 gap-2">
            <a href="{{ route('notificaciones.index', ['filtro' => 'todas']) }}" 
               class="px-4 py-2.5 text-sm font-semibold rounded-t-xl transition-colors flex items-center gap-2 border-b-2 {{ $filtro === 'todas' ? 'border-emerald-600 text-emerald-700 bg-white shadow-sm' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/50' }}">
                <i class="fas fa-inbox"></i>
                <span>Todas</span>
                <span class="px-2 py-0.5 text-xs rounded-full bg-slate-200 text-slate-700 font-bold">{{ $conteoTodas }}</span>
            </a>

            <a href="{{ route('notificaciones.index', ['filtro' => 'no_leidas']) }}" 
               class="px-4 py-2.5 text-sm font-semibold rounded-t-xl transition-colors flex items-center gap-2 border-b-2 {{ $filtro === 'no_leidas' ? 'border-emerald-600 text-emerald-700 bg-white shadow-sm' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/50' }}">
                <i class="fas fa-envelope"></i>
                <span>No Leídas</span>
                <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700 font-bold">{{ $conteoNoLeidas }}</span>
            </a>

            <a href="{{ route('notificaciones.index', ['filtro' => 'leidas']) }}" 
               class="px-4 py-2.5 text-sm font-semibold rounded-t-xl transition-colors flex items-center gap-2 border-b-2 {{ $filtro === 'leidas' ? 'border-emerald-600 text-emerald-700 bg-white shadow-sm' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/50' }}">
                <i class="fas fa-envelope-open"></i>
                <span>Leídas</span>
                <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-700 font-bold">{{ $conteoLeidas }}</span>
            </a>
        </div>

        <!-- Lista de Notificaciones -->
        <div class="divide-y divide-slate-100">
            @forelse($notificaciones as $n)
                @php
                    $bgStatus = $n->leido ? 'bg-white' : 'bg-emerald-50/30';
                    $iconClass = match($n->tipo) {
                        'warning' => 'fa-exclamation-triangle text-amber-500 bg-amber-100',
                        'danger'  => 'fa-exclamation-circle text-red-500 bg-red-100',
                        'success' => 'fa-check-circle text-emerald-500 bg-emerald-100',
                        default   => 'fa-info-circle text-blue-500 bg-blue-100',
                    };
                @endphp
                <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/80 transition-colors {{ $bgStatus }}">
                    <div class="flex items-start space-x-4">
                        <div class="h-10 w-10 shrink-0 rounded-full flex items-center justify-center text-lg {{ $iconClass }}">
                            <i class="fas {{ strtok($iconClass, ' ') }}"></i>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm font-bold text-slate-800 {{ !$n->leido ? 'text-slate-900 font-extrabold' : '' }}">
                                    {{ $n->titulo }}
                                </h3>
                                @if(!$n->leido)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white">
                                        Nuevo
                                    </span>
                                @endif
                                <span class="text-xs text-slate-400 font-medium">
                                    &bull; {{ $n->tiempo_hace }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-600">
                                {{ $n->mensaje }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                        <a href="{{ route('notificaciones.show', $n->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1">
                            <span>Ver detalle</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>

                        @if(!$n->leido)
                            <form action="{{ route('notificaciones.marcar_leida', $n->id) }}" method="POST">
                                @csrf
                                <button type="submit" title="Marcar como leída" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('notificaciones.destroy', $n->id) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar esta notificación?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Eliminar" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center space-y-3">
                    <div class="h-16 w-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto text-2xl">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <h3 class="text-base font-semibold text-slate-700">No hay notificaciones</h3>
                    <p class="text-slate-400 text-sm max-w-sm mx-auto">
                        No se encontraron avisos o notificaciones en esta sección por el momento.
                    </p>
                </div>
            @endforelse
        </div>

        @if($notificaciones->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $notificaciones->appends(['filtro' => $filtro])->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
