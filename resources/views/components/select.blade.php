@props(['name' => '', 'placeholder' => 'Seleccionar...', 'selected' => null])

<select name="{{ $name }}" {{ $attributes->merge(['class' => 'input-field appearance-none bg-white cursor-pointer']) }}>
    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif
    {{ $slot }}
</select>