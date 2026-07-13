@props(['id' => null, 'label' => '', 'name' => null, 'required' => false, 'error' => null])

<div class="mb-4 w-full">
    @if($label)
        <label @if($id) for="{{ $id }}" @endif class="block text-sm font-semibold text-slate-700 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-red-500 ml-1" title="Requerido">*</span>
            @endif
        </label>
    @endif
    
    {{ $slot }}
    
    @php
        $errorName = $error ?? $name;
    @endphp
    
    @if($errorName)
        @error($errorName)
            <p class="mt-1.5 text-xs text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
        @enderror
    @endif
</div>
