import re

with open('C:/laragon/www/LeonPlast/LeonPlast-Laravel/resources/views/produccion/procesos/ejecucion.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()


search_parent_badge = '''                                          @php
                                              $bgLabel = 'bg-blue-100 text-blue-800';
                                              $textLabel = 'PRODUCCIÓN';
                                              if($first->codigo_tipo_producto === 'ACT' || $first->codigo_tipo_producto === 'MANUAL') { $bgLabel = 'bg-teal-100 text-teal-800'; $textLabel = 'ACTIVIDAD'; }
                                              elseif($first->tipo_operacion === 'limpieza') { $bgLabel = 'bg-red-100 text-red-800'; $textLabel = 'LIMPIEZA'; }
                                              elseif(str_contains($first->tipo_operacion ?? '', 'merma')) { $bgLabel = 'bg-orange-100 text-orange-800'; $textLabel = 'MERMA'; }
                                              elseif(str_contains($first->tipo_operacion ?? '', 'molido') || str_contains($first->tipo_operacion ?? '', 'maquina')) { $bgLabel = 'bg-purple-100 text-purple-800'; $textLabel = 'RECICLADO'; }
                                          @endphp
                                          <td class="px-3 py-3 text-center whitespace-nowrap">
                                              <span class="px-2 py-1 text-[10px] font-semibold rounded-full {{ $bgLabel }}">{{ $textLabel }}</span>
                                          </td>'''
replace_parent_badge = '''                                          <td class="px-3 py-3 text-center whitespace-nowrap"></td>'''
content = content.replace(search_parent_badge.replace('PRODUCCIÓN', 'PRODUCCI\"N'), replace_parent_badge)
content = content.replace(search_parent_badge, replace_parent_badge)


search_parent_tipo = '''                                          <td class="px-3 py-3 whitespace-nowrap">
                                              <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                  {{ $esManual ? 'MANUAL' : 'CARGA' }}
                                              </span>
                                          </td>'''
replace_parent_tipo = '''                                          @php
                                              $bgLabel = 'bg-blue-100 text-blue-800';
                                              $textLabel = 'PRODUCCIÓN';
                                              if($first->codigo_tipo_producto === 'ACT' || $first->codigo_tipo_producto === 'MANUAL') { $bgLabel = 'bg-teal-100 text-teal-800'; $textLabel = 'ACTIVIDAD'; }
                                              elseif($first->tipo_operacion === 'limpieza') { $bgLabel = 'bg-red-100 text-red-800'; $textLabel = 'LIMPIEZA'; }
                                              elseif(str_contains($first->tipo_operacion ?? '', 'merma')) { $bgLabel = 'bg-orange-100 text-orange-800'; $textLabel = 'MERMA'; }
                                              elseif(str_contains($first->tipo_operacion ?? '', 'molido') || str_contains($first->tipo_operacion ?? '', 'maquina')) { $bgLabel = 'bg-purple-100 text-purple-800'; $textLabel = 'RECICLADO'; }
                                          @endphp
                                          <td class="px-3 py-3 whitespace-nowrap">
                                              <div class="flex flex-col space-y-1 items-start">
                                                  <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                      {{ $esManual ? 'MANUAL' : 'CARGA' }}
                                                  </span>
                                                  <span class="px-2 py-1 text-[9px] font-semibold rounded-full {{ $bgLabel }}">{{ $textLabel }}</span>
                                              </div>
                                          </td>'''
content = content.replace(search_parent_tipo, replace_parent_tipo)

with open('C:/laragon/www/LeonPlast/LeonPlast-Laravel/resources/views/produccion/procesos/ejecucion.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
print('Done!')
