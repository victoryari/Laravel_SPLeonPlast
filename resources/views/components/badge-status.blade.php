@props(['estado' => ''])

@php
    $estado = strtoupper(trim($estado));

    $estados = [
        'BORRADOR'       => ['bg' => 'bg-slate-100',   'text' => 'text-slate-700',   'icon' => 'fa-pencil'],
        'PENDIENTE'      => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'icon' => 'fa-clock'],
        'EN_PROCESO'     => ['bg' => 'bg-blue-100',    'text' => 'text-blue-700',    'icon' => 'fa-spinner'],
        'APROBADO'       => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'fa-check'],
        'COMPLETADO'     => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'fa-check-circle'],
        'ATENDIDO_TOTAL' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'fa-check-double'],
        'RECHAZADO'      => ['bg' => 'bg-red-100',     'text' => 'text-red-700',     'icon' => 'fa-times'],
        'ANULADO'        => ['bg' => 'bg-red-100',     'text' => 'text-red-700',     'icon' => 'fa-ban'],
        'CANCELADO'      => ['bg' => 'bg-red-100',     'text' => 'text-red-700',     'icon' => 'fa-times-circle'],
    ];

    $config = $estados[$estado] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'icon' => 'fa-circle-info'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold shadow-sm ' . $config['bg'] . ' ' . $config['text']]) }}>
    @if(isset($config['icon']))
        <i class="fas {{ $config['icon'] }} mr-1.5 opacity-70"></i>
    @endif
    {{ str_replace('_', ' ', $estado) ?: 'DESCONOCIDO' }}
</span>
