@props(['name' => 'search', 'placeholder' => 'Buscar...', 'value' => ''])

<div {{ $attributes->merge(['class' => 'relative']) }}>
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <i class="fas fa-search text-slate-400"></i>
    </div>
    <input type="text" name="{{ $name }}" value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        class="input-field pl-10">
</div>