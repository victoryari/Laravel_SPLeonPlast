@props([
    'name' => '',
    'id' => null,
    'url' => null,
    'placeholder' => 'Buscar...',
    'minInput' => 1,
    'value' => '',
    'label' => '',
])

@php
    $inputId = $id ?? 'select-searchable-' . Str::random(6);
@endphp

<div class="relative select-searchable-wrapper" data-url="{{ $url }}" data-min="{{ $minInput }}" data-name="{{ $name }}">
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" class="select-searchable-hidden">

    <button type="button" class="input-field flex items-center justify-between text-left select-searchable-trigger" data-target="{{ $inputId }}">
        <span class="select-searchable-label {{ $value ? 'text-slate-800' : 'text-slate-400' }}">{{ $value ? $label : $placeholder }}</span>
        <i class="fas fa-chevron-down text-xs text-slate-400 transition-transform"></i>
    </button>

    <div id="{{ $inputId }}" class="select-searchable-dropdown hidden absolute z-50 mt-1 w-full bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
        <div class="p-2 border-b border-slate-100">
            <input type="text" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none select-searchable-query" placeholder="Buscar...">
        </div>

        <div class="select-searchable-loading hidden p-4 text-center text-sm text-slate-400">
            <i class="fas fa-spinner fa-spin mr-2"></i>Buscando...
        </div>

        <div class="select-searchable-empty hidden p-4 text-center text-sm text-slate-400">
            Sin resultados
        </div>

        <div class="select-searchable-options max-h-48 overflow-y-auto"></div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.select-searchable-wrapper').forEach(function(wrapper) {
        const trigger = wrapper.querySelector('.select-searchable-trigger');
        const dropdown = wrapper.querySelector('.select-searchable-dropdown');
        const hidden = wrapper.querySelector('.select-searchable-hidden');
        const label = wrapper.querySelector('.select-searchable-label');
        const query = wrapper.querySelector('.select-searchable-query');
        const options = wrapper.querySelector('.select-searchable-options');
        const loading = wrapper.querySelector('.select-searchable-loading');
        const empty = wrapper.querySelector('.select-searchable-empty');
        const url = wrapper.dataset.url;
        const minInput = parseInt(wrapper.dataset.min) || 1;
        const icon = trigger.querySelector('.fa-chevron-down');

        let open = false;
        let timeout = null;
        let selectedValue = hidden.value;

        function toggle() {
            open ? close() : openDropdown();
        }

        function openDropdown() {
            open = true;
            dropdown.classList.remove('hidden');
            icon.classList.add('rotate-180');
            query.value = '';
            query.focus();
            if (!url) {
                filterOptions('');
            }
        }

        function close() {
            open = false;
            dropdown.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }

        function select(id, text) {
            selectedValue = id;
            hidden.value = id;
            label.textContent = text;
            label.className = 'select-searchable-label text-slate-800';
            close();
        }

        function filterOptions(search) {
            const items = wrapper.querySelectorAll('.select-searchable-static-option');
            let visible = 0;
            items.forEach(function(item) {
                const text = item.dataset.searchText || item.textContent.toLowerCase();
                if (text.includes(search.toLowerCase())) {
                    item.classList.remove('hidden');
                    visible++;
                } else {
                    item.classList.add('hidden');
                }
            });
            empty.classList.toggle('hidden', visible > 0 || search.length === 0);
        }

        function fetchResults(search) {
            loading.classList.remove('hidden');
            empty.classList.add('hidden');
            options.innerHTML = '';

            const separator = url.includes('?') ? '&' : '?';
            const searchUrl = url + separator + 'q=' + encodeURIComponent(search);

            fetch(searchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    loading.classList.add('hidden');
                    const results = data.results || data.data || data;
                    if (results.length === 0) {
                        empty.classList.remove('hidden');
                        return;
                    }
                    results.forEach(function(item) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'w-full text-left px-4 py-2.5 text-sm hover:bg-primary-50 transition-colors ' +
                            (selectedValue == item.id ? 'bg-primary-50 text-primary font-semibold' : 'text-slate-700');
                        btn.textContent = item.text;
                        btn.dataset.value = item.id;
                        btn.addEventListener('click', function() {
                            select(item.id, item.text);
                        });
                        options.appendChild(btn);
                    });
                })
                .catch(function() {
                    loading.classList.add('hidden');
                    empty.classList.remove('hidden');
                    empty.textContent = 'Error al buscar';
                });
        }

        function doSearch() {
            const search = query.value.trim();
            if (search.length < minInput) {
                options.innerHTML = '';
                empty.classList.add('hidden');
                return;
            }
            if (url) {
                fetchResults(search);
            } else {
                filterOptions(search);
            }
        }

        // Events
        trigger.addEventListener('click', toggle);

        query.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(doSearch, 300);
        });

        query.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') close();
            if (e.key === 'Enter') e.preventDefault();
        });

        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) close();
        });

        // If static options exist, render them initially
        if (!url) {
            const staticOptions = wrapper.querySelectorAll('template[data-option]');
            if (staticOptions.length > 0) {
                staticOptions.forEach(function(tpl) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'w-full text-left px-4 py-2.5 text-sm hover:bg-primary-50 transition-colors select-searchable-static-option text-slate-700';
                    btn.textContent = tpl.content.textContent.trim();
                    btn.dataset.value = tpl.dataset.value;
                    btn.dataset.searchText = tpl.dataset.searchText || btn.textContent.toLowerCase();
                    btn.addEventListener('click', function() {
                        select(btn.dataset.value, btn.textContent);
                    });
                    options.appendChild(btn);
                });
            }
        }
    });
});
</script>
@endpush