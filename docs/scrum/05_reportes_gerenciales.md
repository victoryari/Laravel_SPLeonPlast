# Historia de Usuario: Módulo de Reportes Gerenciales

**Estado actual:** Se cuenta con las tablas transaccionales (compras, inventario, órdenes de producción, consumos). Actualmente la información existe, pero está dispersa.
**Lo que se busca:** Un módulo consolidado que extraiga, cruce y totalice la data para ofrecer reportes financieros y operativos diseñados para la alta gerencia.

## Historia de Usuario
**Como** Gerente General / Administrador
**Quiero** generar, visualizar y descargar reportes gerenciales y consolidados sobre costos de producción, consumos y eficiencia
**Para** evaluar la rentabilidad operativa del negocio, detectar desviaciones presupuestarias y presentar resultados consolidados con datos exactos y respaldados por el sistema.

## Criterios de Aceptación
- **Reporte de Costos por Orden de Producción (OP):** El sistema debe permitir consultar el costo total y unitario real de las OPs terminadas, desglosando los gastos en:
  1. Costo de Materia Prima e Insumos consumidos.
  2. Costo de Horas Hombre y Maquinaria (tiempos reportados).
  3. Costos Indirectos de Fabricación (si aplica).
- **Reporte de Consumos y Valorización de Inventario:** Un listado de los insumos más consumidos en un periodo específico (Top 10), mostrando tanto la cantidad física como su valorización en la moneda principal.
- **Reporte de Eficiencia de Planta (OEE / Tiempos Muertos):** Un análisis mensual de horas productivas vs. horas muertas registradas, para medir el rendimiento del piso de planta.
- **Filtros Avanzados:** Todo reporte debe contar con selectores de Rango de Fechas (Desde - Hasta), por Producto, o por Centro de Trabajo.
- **Exportación Multi-formato:** Cada vista de reporte debe tener dos botones obligatorios:
  - **Exportar a PDF:** Para generar un documento con formato formal, logo de la empresa y gráficos resumen (ideal para reuniones).
  - **Exportar a Excel (XLSX/CSV):** Para descargar la data en crudo y permitir a la gerencia realizar sus propios cruces o tablas dinámicas.
- **Gráficos de Apoyo:** Las tablas numéricas deben estar acompañadas de al menos un gráfico (de líneas para tendencias o barras para comparativas) que facilite la interpretación rápida.

## Notas Técnicas
- **Controlador:** Crear un `ReporteGerencialController`.
- **Generación de PDF:** Implementar o utilizar la librería `dompdf/dompdf` o `barryvdh/laravel-dompdf` (previamente verificando si ya está instalada).
- **Generación de Excel:** Implementar `Maatwebsite/Laravel-Excel` para exportaciones limpias de las colecciones de datos.
- **Data Warehouse/Consultas:** Si la carga de datos es muy alta, considerar crear vistas SQL (`CREATE VIEW`) que consoliden la información por mes, para que los reportes carguen sin ralentizar el servidor en horas pico.
