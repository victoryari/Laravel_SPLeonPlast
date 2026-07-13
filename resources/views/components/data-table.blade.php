@props([
    'headers' => [],
    'hasActions' => true,
    'emptyMessage' => 'No se encontraron registros.'
])

<div class="card overflow-x-auto">
    <table class="w-full whitespace-nowrap">
        <thead class="bg-slate-800 text-white text-xs uppercase tracking-wider text-left">
            <tr>
                @foreach($headers as $header)
                    <th class="px-6 py-4 font-semibold {{ $header['class'] ?? '' }}">{{ $header['label'] ?? $header }}</th>
                @endforeach
                @if($hasActions)
                    <th class="px-6 py-4 font-semibold text-center w-24">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            {{ $slot }}
        </tbody>
    </table>
    
    @if(isset($empty) || (!isset($slot) || trim($slot) === ''))
        <div class="p-8 text-center text-slate-500">
            {{ $empty ?? $emptyMessage }}
        </div>
    @endif
</div>
