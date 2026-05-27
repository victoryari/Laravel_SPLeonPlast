@props(['color' => 'slate'])

@php
    $colors = [
        'slate' => 'bg-slate-100 text-slate-700',
        'green' => 'bg-green-100 text-green-700',
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'red' => 'bg-red-100 text-red-700',
        'yellow' => 'bg-yellow-100 text-yellow-700',
        'blue' => 'bg-primary-100 text-primary',
        'purple' => 'bg-purple-100 text-purple-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
    ];
    $class = $colors[$color] ?? $colors['slate'];
@endphp

<span {{ $attributes->merge(['class' => "badge {$class}"]) }}>
    {{ $slot }}
</span>