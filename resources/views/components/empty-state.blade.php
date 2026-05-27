@props(['icon' => 'fa-inbox', 'message' => 'No se encontraron registros.', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'px-6 py-12 text-center']) }}>
    <i class="fas {{ $icon }} text-4xl text-slate-300 mb-4 block mx-auto"></i>
    <p class="text-sm font-medium text-slate-500">{{ $message }}</p>
    @if($subtitle)
        <p class="text-xs text-slate-400 mt-1">{{ $subtitle }}</p>
    @endif
</div>