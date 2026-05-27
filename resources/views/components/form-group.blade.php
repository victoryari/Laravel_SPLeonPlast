@props(['label' => '', 'required' => false, 'error' => null])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    @if($label)
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif
    {{ $slot }}
    @if($error)
        <p class="text-xs text-red-600 mt-0.5">{{ $error }}</p>
    @endif
</div>