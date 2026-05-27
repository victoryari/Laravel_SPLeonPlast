@props(['message' => '', 'type' => 'success'])

@php
    $colors = [
        'success' => '#059669',
        'error'   => '#dc2626',
        'warning' => '#d97706',
        'info'    => '#0284c7',
    ];
    $icons = [
        'success' => 'fa-check-circle',
        'error'   => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info'    => 'fa-info-circle',
    ];
    $bg = $colors[$type] ?? $colors['success'];
    $icon = $icons[$type] ?? $icons['success'];
@endphp

@if($message)
<div class="toast" style="background-color: {{ $bg }}" data-autohide="4000">
    <i class="fas {{ $icon }}"></i>
    <span>{{ $message }}</span>
    <button class="toast-close" aria-label="Cerrar">&times;</button>
</div>
@endif
