import codecs

path = r'C:\laragon\www\LeonPlast\LeonPlast-Laravel\resources\views\produccion\procesos\ejecucion.blade.php'
with codecs.open(path, 'r', 'utf-8') as f:
    content = f.read()

target1 = '''                @if($es_inyectado || $es_ensamblado || $es_molido || $es_troquelado || $es_horneado)
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1" id="lbl_centro">{{ $es_troquelado ? 'Troqueladora (Centro)' : ($es_horneado ? 'Horno (Centro)' : ($es_molido ? 'Molino (Centro)' : ($es_ensamblado ? 'Ensambladora (Centro)' : 'Inyectora (Centro)'))) }}</label>'''

replacement1 = '''                @if($es_inyectado || $es_ensamblado || $es_molido || $es_troquelado || $es_horneado || $es_mezclado)
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1" id="lbl_centro">{{ $es_troquelado ? 'Troqueladora (Centro)' : ($es_horneado ? 'Horno (Centro)' : ($es_molido ? 'Molino (Centro)' : ($es_ensamblado ? 'Ensambladora (Centro)' : ($es_mezclado ? 'Mezcladora (Centro)' : 'Inyectora (Centro)')))) }}</label>'''

content = content.replace(target1, replacement1)

target2 = '''        } else if (esEnsamblado || esMolido) {
            centro = document.getElementById('centro_global').value;
            if (!centro) return window.toast(`Seleccione ${esMolido ? 'Molino' : 'Ensambladora'} (Centro).`, 'warning');'''

replacement2 = '''        } else if (esEnsamblado || esMolido || (typeof esMezclado !== 'undefined' && esMezclado) || document.getElementById('tipo_operacion')?.value === 'mezclado' || document.getElementById('centro_global')) {
            centro = document.getElementById('centro_global') ? document.getElementById('centro_global').value : '';
            if (!centro) return window.toast(`Seleccione ${esMolido ? 'Molino' : 'Centro de Trabajo'}.`, 'warning');'''

content = content.replace(target2, replacement2)

with codecs.open(path, 'w', 'utf-8') as f:
    f.write(content)
