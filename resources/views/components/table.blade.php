@props(['headers' => [], 'actions' => false, 'responsive' => true])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if(isset($header))
        <div class="px-6 py-4 border-b border-slate-100">
            {{ $header }}
        </div>
    @endif
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            @if(count($headers) > 0)
            <thead>
                <tr class="bg-slate-900 text-slate-200 uppercase tracking-wider text-xs font-bold">
                    @foreach($headers as $index => $header)
                        <th class="px-4 py-3.5 text-left font-bold {{ $index < count($headers) - 1 ? 'border-r border-slate-800' : '' }} {{ isset($header['class']) ? $header['class'] : '' }}">
                            {{ $header['label'] ?? $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            @endif
            <tbody class="divide-y divide-slate-100">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @if(isset($footer))
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50">
            {{ $footer }}
        </div>
    @endif
</div>