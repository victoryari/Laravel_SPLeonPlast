@extends('layouts.app')

@section('title', 'Detalle de Notificación')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Encabezado con navegación -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-bell text-emerald-600"></i>
                Detalle de Notificación
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Información completa de la notificación recibida.
            </p>
        </div>
        <a href="{{ route('notificaciones.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl border border-slate-200 transition-colors flex items-center gap-2 self-start">
            <i class="fas fa-arrow-left"></i>
            <span>Volver a Notificaciones</span>
        </a>
    </div>

    <!-- Tarjeta de Detalle -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <!-- Cabecera con Tipo e Icono -->
        @php
            $iconConfig = match($notificacion->tipo) {
                'warning' => ['icon' => 'fa-exclamation-triangle', 'color' => 'text-amber-500', 'bg' => 'bg-amber-100', 'border' => 'border-amber-200', 'label' => 'Advertencia', 'labelBg' => 'bg-amber-100 text-amber-800'],
                'danger'  => ['icon' => 'fa-exclamation-circle', 'color' => 'text-red-500', 'bg' => 'bg-red-100', 'border' => 'border-red-200', 'label' => 'Alerta Crítica', 'labelBg' => 'bg-red-100 text-red-800'],
                'success' => ['icon' => 'fa-check-circle', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-100', 'border' => 'border-emerald-200', 'label' => 'Éxito', 'labelBg' => 'bg-emerald-100 text-emerald-800'],
                default   => ['icon' => 'fa-info-circle', 'color' => 'text-blue-500', 'bg' => 'bg-blue-100', 'border' => 'border-blue-200', 'label' => 'Información', 'labelBg' => 'bg-blue-100 text-blue-800'],
            };
        @endphp

        <div class="p-6 bg-slate-50 border-b border-slate-200">
            <div class="flex items-start gap-4">
                <div class="h-14 w-14 rounded-full shrink-0 flex items-center justify-center {{ $iconConfig['bg'] }} {{ $iconConfig['color'] }} text-2xl">
                    <i class="fas {{ $iconConfig['icon'] }}"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <h2 class="text-lg font-bold text-slate-800">{{ $notificacion->titulo }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $iconConfig['labelBg'] }}">
                            {{ $iconConfig['label'] }}
                        </span>
                        @if($notificacion->leido)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-200 text-slate-600">
                                <i class="fas fa-check mr-1"></i> Leída
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-500">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-clock"></i>
                            {{ $notificacion->created_at ? $notificacion->created_at->format('d/m/Y H:i:s') : 'N/A' }}
                        </span>
                        <span>•</span>
                        <span>{{ $notificacion->tiempo_hace }}</span>
                        @if($notificacion->read_at)
                            <span>•</span>
                            <span class="flex items-center gap-1">
                                <i class="fas fa-eye"></i>
                                Leída: {{ $notificacion->read_at->format('d/m/Y H:i') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Cuerpo del Mensaje -->
        <div class="p-6 space-y-4">
            <div>
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">Mensaje</h3>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $notificacion->mensaje }}</p>
                </div>
            </div>

            @if($notificacion->link)
                @php
                    $linkHref = $notificacion->link;
                    $parsed = parse_url($linkHref);
                    if (isset($parsed['host']) && $parsed['host'] !== request()->getHost()) {
                        $linkHref = ($parsed['path'] ?? '/') . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
                    }
                @endphp
                <div>
                    <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">Enlace Relacionado</h3>
                    <a href="{{ $linkHref }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-sm font-semibold rounded-xl border border-emerald-200 transition-colors">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Ir al recurso relacionado</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            @endif
        </div>

        <!-- Acciones -->
        <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                @if(!$notificacion->leido)
                    <form action="{{ route('notificaciones.marcar_leida', $notificacion->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-sm font-semibold rounded-xl border border-emerald-200 transition-colors flex items-center gap-2">
                            <i class="fas fa-check"></i>
                            <span>Marcar como leída</span>
                        </button>
                    </form>
                @endif
            </div>

            <form action="{{ route('notificaciones.destroy', $notificacion->id) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar esta notificación?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold rounded-xl border border-red-200 transition-colors flex items-center gap-2">
                    <i class="fas fa-trash-alt"></i>
                    <span>Eliminar</span>
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
