@props(['title' => '', 'subtitle' => '', 'actions' => ''])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6']) }}>
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-sm text-slate-500 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>
    @if($actions)
        <div class="shrink-0 flex items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>