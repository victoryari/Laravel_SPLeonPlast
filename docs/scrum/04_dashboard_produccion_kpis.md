# Historia de Usuario: Dashboard de Producción y KPIs

**Estado actual:** Existe un dashboard base para administradores, pero se requiere enfocar en indicadores clave de rendimiento (KPIs) específicos para la gestión y control del área de producción.
**Lo que se busca:** Centralizar la información crítica de la planta en una sola pantalla visual, dinámica y fácil de interpretar para la toma de decisiones gerenciales.

## Historia de Usuario
**Como** Gerente de Producción / Administrador
**Quiero** visualizar un panel de control (Dashboard) interactivo con los principales indicadores (KPIs) de producción en tiempo real
**Para** monitorear la eficiencia de la planta, identificar cuellos de botella, medir el nivel de merma y supervisar el cumplimiento de las órdenes de producción de forma rápida y centralizada.

## Criterios de Aceptación
- **Tarjetas de Resumen (Widgets):** La parte superior debe mostrar métricas clave actualizadas:
  - Total de Órdenes de Producción (OPs) activas.
  - OPs finalizadas en el mes actual.
  - Porcentaje de merma o rechazo general.
  - Horas máquina u horas hombre acumuladas en la semana.
- **Gráfico de Estado de Producción:** Un gráfico visual (ej. tipo dona) que muestre la distribución de las OPs según su estado actual (PENDIENTE, EN_PROCESO, PAUSADO, TERMINADO).
- **Control de Mermas:** Un gráfico de barras o líneas que analice la cantidad de merma/desperdicio reportada en los últimos 30 días, permitiendo detectar tendencias negativas.
- **Rendimiento por Centro de Trabajo:** Una tabla o gráfico que indique el volumen de producción o eficiencia comparativa entre las distintas máquinas (ej. Inyectoras, Ensambladoras).
- **Alertas de Retraso:** Una sección que liste únicamente las Órdenes de Producción que se encuentren demoradas o fuera del plazo programado, destacadas en color rojo/amarillo.
- **Filtros Temporales:** Un selector de rango de fechas (Hoy, Última Semana, Este Mes) que actualice dinámicamente los gráficos y métricas mostradas en pantalla.

## Notas Técnicas
- **Controlador:** Se recomienda crear o ampliar un `DashboardController` especializado que ejecute las consultas complejas y devuelva la data procesada.
- **Consultas a BD (Queries):** 
  - Usar métodos de agregación (`sum()`, `count()`) y agrupamiento (`groupBy()`) de Laravel/Eloquent para obtener los datos.
  - Aprovechar el `created_at` o `fecha_emision` de las tablas `orden_produccion_global` y `produccion_consumos` para los filtros de tiempo.
- **Librería de Gráficos:** Implementar una biblioteca ligera de JavaScript como `Chart.js` o `ApexCharts` para renderizar los componentes visuales de manera atractiva y responsiva.
- **Rendimiento (Caché):** Considerar el uso de `Cache::remember()` para las consultas más pesadas del dashboard, actualizándolas cada cierta cantidad de minutos para no sobrecargar el servidor si varios administradores entran al mismo tiempo.
