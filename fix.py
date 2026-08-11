import re

with open('C:/laragon/www/LeonPlast/LeonPlast-Laravel/resources/views/produccion/procesos/ejecucion.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# For the @else flat list: combine the badge into the first column
search_flat = '''                                  <td class="px-3 py-2 whitespace-nowrap">
                                      <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-200 text-slate-800">
                                          {{ $r->codigo_tipo_producto }}
                                      </span>
                                  </td>'''
replace_flat = '''                                  @php
                                      $bgLabelR = 'bg-blue-100 text-blue-800';
                                      $textLabelR = 'PRODUCCIÓN';
                                      if($r->codigo_tipo_producto === 'ACT' || $r->codigo_tipo_producto === 'MANUAL') { $bgLabelR = 'bg-teal-100 text-teal-800'; $textLabelR = 'ACTIVIDAD'; }
                                      elseif($r->tipo_operacion === 'limpieza') { $bgLabelR = 'bg-red-100 text-red-800'; $textLabelR = 'LIMPIEZA'; }
                                      elseif(str_contains($r->tipo_operacion ?? '', 'merma')) { $bgLabelR = 'bg-orange-100 text-orange-800'; $textLabelR = 'MERMA'; }
                                      elseif(str_contains($r->tipo_operacion ?? '', 'molido') || str_contains($r->tipo_operacion ?? '', 'maquina')) { $bgLabelR = 'bg-purple-100 text-purple-800'; $textLabelR = 'RECICLADO'; }
                                  @endphp
                                  <td class="px-3 py-2 whitespace-nowrap">
                                      <div class="flex flex-col space-y-1 items-start">
                                          <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-200 text-slate-800">
                                              {{ $r->codigo_tipo_producto }}
                                          </span>
                                          <span class="px-2 py-1 text-[9px] font-semibold rounded-full {{ $bgLabelR }}">{{ $textLabelR }}</span>
                                      </div>
                                  </td>'''
content = content.replace(search_flat, replace_flat)

# Remove the old badge column calculation in flat list
search_flat_badge = '''                                  @php
                                      $bgLabelR = 'bg-blue-100 text-blue-800';
                                      $textLabelR = 'PRODUCCIÓN';
                                      if($r->codigo_tipo_producto === 'ACT' || $r->codigo_tipo_producto === 'MANUAL') { $bgLabelR = 'bg-teal-100 text-teal-800'; $textLabelR = 'ACTIVIDAD'; }
                                      elseif($r->tipo_operacion === 'limpieza') { $bgLabelR = 'bg-red-100 text-red-800'; $textLabelR = 'LIMPIEZA'; }
                                      elseif(str_contains($r->tipo_operacion ?? '', 'merma')) { $bgLabelR = 'bg-orange-100 text-orange-800'; $textLabelR = 'MERMA'; }
                                      elseif(str_contains($r->tipo_operacion ?? '', 'molido') || str_contains($r->tipo_operacion ?? '', 'maquina')) { $bgLabelR = 'bg-purple-100 text-purple-800'; $textLabelR = 'RECICLADO'; }
                                  @endphp
                                  <td class="px-3 py-2 text-center whitespace-nowrap">
                                      <span class="px-2 py-1 text-[10px] font-semibold rounded-full {{ $bgLabelR }}">{{ $textLabelR }}</span>
                                  </td>'''
content = content.replace(search_flat_badge.replace('PRODUCCIÓN', 'PRODUCCI\"N'), '')
content = content.replace(search_flat_badge, '')

# Same for grouped list details row
search_grouped_detail = '''                                          <td class="px-3 py-2 whitespace-nowrap pl-8 border-l-4 border-l-slate-300">
                                              <span class="px-2 py-1 inline-flex text-[10px] leading-5 font-semibold rounded-full bg-slate-200 text-slate-800">
                                                  {{ $r->codigo_tipo_producto }}
                                              </span>
                                          </td>'''
replace_grouped_detail = '''                                          @php
                                              $bgLabelR = 'bg-blue-100 text-blue-800';
                                              $textLabelR = 'PRODUCCIÓN';
                                              if($r->codigo_tipo_producto === 'ACT' || $r->codigo_tipo_producto === 'MANUAL') { $bgLabelR = 'bg-teal-100 text-teal-800'; $textLabelR = 'ACTIVIDAD'; }
                                              elseif($r->tipo_operacion === 'limpieza') { $bgLabelR = 'bg-red-100 text-red-800'; $textLabelR = 'LIMPIEZA'; }
                                              elseif(str_contains($r->tipo_operacion ?? '', 'merma')) { $bgLabelR = 'bg-orange-100 text-orange-800'; $textLabelR = 'MERMA'; }
                                              elseif(str_contains($r->tipo_operacion ?? '', 'molido') || str_contains($r->tipo_operacion ?? '', 'maquina')) { $bgLabelR = 'bg-purple-100 text-purple-800'; $textLabelR = 'RECICLADO'; }
                                          @endphp
                                          <td class="px-3 py-2 whitespace-nowrap pl-8 border-l-4 border-l-slate-300">
                                              <div class="flex flex-col space-y-1 items-start">
                                                  <span class="px-2 py-1 inline-flex text-[10px] leading-5 font-semibold rounded-full bg-slate-200 text-slate-800">
                                                      {{ $r->codigo_tipo_producto }}
                                                  </span>
                                                  <span class="px-2 py-1 text-[9px] font-semibold rounded-full {{ $bgLabelR }}">{{ $textLabelR }}</span>
                                              </div>
                                          </td>'''
content = content.replace(search_grouped_detail, replace_grouped_detail)

with open('C:/laragon/www/LeonPlast/LeonPlast-Laravel/resources/views/produccion/procesos/ejecucion.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
print('Done!')
