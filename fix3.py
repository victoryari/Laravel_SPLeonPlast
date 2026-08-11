import re

with open('C:/laragon/www/LeonPlast/LeonPlast-Laravel/resources/views/produccion/procesos/ejecucion.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

search = '''                                              <div class="flex gap-1">
                                                  <input type="time" name="hora_inicio_maquina" value="{{ $r->hora_inicio_maquina ?? '' }}"
                                                      class="w-1/2 border-slate-300 rounded-md text-sm py-1.5 px-1 focus:ring-primary focus:border-primary">
                                                  <input type="time" name="hora_fin_maquina" value="{{ $r->hora_fin_maquina ?? '' }}"
                                                      class="w-1/2 border-slate-300 rounded-md text-sm py-1.5 px-1 focus:ring-primary focus:border-primary">
                                              </div>
                                          </div>'''
replace = '''                                              <div class="flex gap-1">
                                                  <input type="time" name="hora_inicio_maquina" value="{{ $r->hora_inicio_maquina ?? '' }}"
                                                      class="w-1/2 border-slate-300 rounded-md text-sm py-1.5 px-1 focus:ring-primary focus:border-primary">
                                                  <input type="time" name="hora_fin_maquina" value="{{ $r->hora_fin_maquina ?? '' }}"
                                                      class="w-1/2 border-slate-300 rounded-md text-sm py-1.5 px-1 focus:ring-primary focus:border-primary">
                                              </div>
                                          </div>

                                          <div class="md:col-span-2">
                                              <label class="block text-xs font-semibold text-slate-600 mb-1">Observación</label>
                                              <input type="text" name="observacion" value="{{ $r->observacion }}"
                                                  class="w-full border-slate-300 rounded-md text-sm py-1.5 px-2 focus:ring-primary focus:border-primary">
                                          </div>'''
content = content.replace(search, replace)

search_js = '''        document.getElementById('upd_observacion').value = container.querySelector('input[name="observacion"]').value;'''
replace_js = '''        const obsInput = container.querySelector('input[name="observacion"]');
        if (obsInput) {
            document.getElementById('upd_observacion').value = obsInput.value;
        } else {
            document.getElementById('upd_observacion').value = '';
        }'''
content = content.replace(search_js, replace_js)


with open('C:/laragon/www/LeonPlast/LeonPlast-Laravel/resources/views/produccion/procesos/ejecucion.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
print('Done!')
