# Plan de Implementación: SCRUM-7 Reporte exportable del kardex valorizado

Este plan detalla los pasos para cumplir con la historia de usuario SCRUM-7, que exige proveer botones de exportación a Excel y PDF para el módulo de Kardex Valorizado, respetando los filtros que el usuario haya aplicado en la vista.

## Open Questions

> [!IMPORTANT]
> Para generar los reportes de manera profesional y robusta en Laravel, **necesitamos instalar dos paquetes muy estándar en la industria**. ¿Me das tu aprobación para instalar los siguientes paquetes vía Composer?
> 1. `maatwebsite/excel` (Para exportación nativa a Excel .xlsx)
> 2. `barryvdh/laravel-dompdf` (Para generación de PDFs con buen formato)
> 
> *Si prefieres no instalar paquetes, puedo hacer una exportación simple a CSV (para Excel) y una vista de "Impresión" genérica (para PDF).*

## Proposed Changes

### [NEW] Librerías (Sujeto a aprobación)
- Se ejecutará `composer require maatwebsite/excel` y `composer require barryvdh/laravel-dompdf`.

### [NEW] Clase de Exportación
#### [NEW] [KardexExport.php](file:///c:/laragon/www/LeonPlast/LeonPlast-Laravel/app/Exports/KardexExport.php)
- Se creará una clase que defina los encabezados y mapeo de datos para el archivo Excel, asegurando que las columnas de "Costo Unitario" y "Costo Total" tengan formato de moneda/número.

### [NEW] Vistas de Reporte
#### [NEW] [kardex_pdf.blade.php](file:///c:/laragon/www/LeonPlast/LeonPlast-Laravel/resources/views/inventario/pdf/kardex_pdf.blade.php)
- Se creará una plantilla Blade optimizada para impresión (A4 horizontal) con el logo de la empresa, los filtros aplicados (ej. Rango de fechas) y la tabla del kardex.

### [MODIFY] Controladores y Rutas
#### [MODIFY] [InventarioController.php](file:///c:/laragon/www/LeonPlast/LeonPlast-Laravel/app/Http/Controllers/InventarioController.php)
- Agregaremos los métodos `exportarKardexExcel(Request $request)` y `exportarKardexPdf(Request $request)`. Estos métodos reutilizarán la lógica de filtros del método `kardex` actual, pero retornarán la descarga del archivo.

#### [MODIFY] [web.php](file:///c:/laragon/www/LeonPlast/LeonPlast-Laravel/routes/web.php)
- Se registrarán las nuevas rutas GET para las exportaciones.

### [MODIFY] Interfaz de Usuario
#### [MODIFY] [kardex.blade.php](file:///c:/laragon/www/LeonPlast/LeonPlast-Laravel/resources/views/inventario/kardex.blade.php)
- Se añadirán dos botones llamativos ("Exportar Excel" y "Exportar PDF") al lado del botón de búsqueda/filtrar.
- Los botones mantendrán en su URL los parámetros actuales de búsqueda (fechas, producto, etc.) para que se exporte exactamente lo que el usuario está viendo.

## Verification Plan
1. Se aplicarán filtros en la vista de Kardex (ej. buscar un producto específico en el mes actual).
2. Se pulsará el botón "Exportar Excel" y se abrirá el archivo `.xlsx` comprobando que las sumas y datos coincidan con la web.
3. Se pulsará "Exportar PDF" y se verificará el formato visual del documento generado.
