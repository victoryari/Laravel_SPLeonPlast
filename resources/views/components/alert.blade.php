@props(['errors' => null, 'type' => 'danger', 'title' => null, 'message' => null])

@php
    $config = [
        'danger' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-200',
            'text' => 'text-red-800',
            'icon' => 'fa-exclamation-triangle',
            'iconColor' => 'text-red-500',
            'defaultTitle' => 'Se encontraron errores'
        ],
        'warning' => [
            'bg' => 'bg-amber-50',
            'border' => 'border-amber-200',
            'text' => 'text-amber-800',
            'icon' => 'fa-exclamation-circle',
            'iconColor' => 'text-amber-500',
            'defaultTitle' => 'Atenci\u00f3n'
        ],
        'success' => [
            'bg' => 'bg-emerald-50',
            'border' => 'border-emerald-200',
            'text' => 'text-emerald-800',
            'icon' => 'fa-check-circle',
            'iconColor' => 'text-emerald-500',
            'defaultTitle' => '\u00c9xito'
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'text' => 'text-blue-800',
            'icon' => 'fa-info-circle',
            'iconColor' => 'text-blue-500',
            'defaultTitle' => 'Informaci\u00f3n'
        ]
    ];
    
    $style = $config[$type] ?? $config['info'];
    $displayTitle = $title ?? $style['defaultTitle'];
@endphp

@if(($errors && $errors->any()) || $message)
    <div class="{{ $style['bg'] }} border-l-4 {{ $style['border'] }} p-4 rounded-r-lg mb-6 shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas {{ $style['icon'] }} {{ $style['iconColor'] }} text-lg"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-bold {{ $style['text'] }}">
                    {{ $displayTitle }}
                </h3>
                
                @if($errors && $errors->any())
                    <div class="mt-2 text-sm {{ $style['text'] }} opacity-90">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @elseif($message)
                    <div class="mt-2 text-sm {{ $style['text'] }} opacity-90">
                        <p>{{ $message }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
