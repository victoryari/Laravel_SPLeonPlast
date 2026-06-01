# Épica: Kardex Valorizado (SCRUM-1)

Esta épica agrupa las historias de usuario necesarias para transformar el actual control de inventario (físico) en un **Kardex Valorizado**, permitiendo no solo conocer las existencias, sino también el valor monetario del inventario en tiempo real, aplicando métodos de costeo estandarizados y mejorando la reportería financiera.

---

## Historia de Usuario: SCRUM-4
### Registro de Costo Unitario y Total en Movimientos
**Como** Analista de Inventario / Contador
**Quiero** que el sistema registre de forma obligatoria el costo unitario y el costo total en cada movimiento de entrada y salida de inventario
**Para** tener la base de datos financiera exacta de las valorizaciones y evitar descuadres monetarios en el kardex.

**Criterios de Aceptación:**
- Cada registro en la tabla `kardex` (y `movimientos_inventario`) debe almacenar obligatoriamente `costo_unitario` y `costo_total`.
- En las recepciones de compra, el costo unitario debe extraerse automáticamente de la Orden de Compra (convertido a la moneda local usando el parámetro del Tipo de Cambio).
- En las salidas (consumo por producción o ajustes), el costo unitario debe calcularse automáticamente en base al método de costeo elegido (FIFO/PEPS o Promedio Ponderado).

---

## Historia de Usuario: SCRUM-5
### Saldo Valorizado en Tiempo Real (PP o PEPS)
**Como** Gerente Financiero / Jefe de Almacén
**Quiero** visualizar en el Kardex el saldo físico y el saldo valorizado actualizado en tiempo real
**Para** conocer el valor total de mis existencias almacenadas en cualquier instante sin tener que hacer cálculos manuales externos.

**Criterios de Aceptación:**
- La vista del Kardex debe incluir nuevas columnas: Costo Unitario Saldo y Costo Total Saldo.
- Cada vez que ocurra un movimiento, el sistema debe recalcular y mostrar el Saldo Total Valorizado del producto.
- La valorización debe soportar de forma nativa la lógica del método PEPS (Primeras Entradas, Primeras Salidas) que se emplea actualmente para los lotes, o Promedio Ponderado (PP), mostrando con precisión de qué lote y costo proviene cada sol valorizado.

---

## Historia de Usuario: SCRUM-6
### Filtros por Producto, Almacén y Rango de Fechas
**Como** Usuario del Kardex
**Quiero** poder filtrar la vista del kardex valorizado por Producto específico, Almacén y un Rango de Fechas (Desde - Hasta)
**Para** analizar los movimientos de un ítem particular en un lapso de tiempo o hacer cierres de mes contables.

**Criterios de Aceptación:**
- La pantalla principal del Kardex debe incluir selectores dinámicos:
  - `Producto`: Búsqueda predictiva o selectable.
  - `Almacén`: Selector de almacenes existentes.
  - `Rango de Fechas`: Datepicker (Desde y Hasta).
- Al aplicar los filtros, la tabla del kardex debe recargarse mostrando únicamente los registros que coinciden, y debe calcular un "Saldo Inicial" valorizado al comienzo del rango seleccionado.

---

## Historia de Usuario: SCRUM-7
### Reporte Exportable del Kardex Valorizado
**Como** Contador / Auditor Interno
**Quiero** tener un botón para exportar la vista filtrada del kardex valorizado a formatos estándar (Excel y PDF)
**Para** adjuntarlo en mis cierres contables mensuales, realizar análisis en herramientas externas y presentar evidencias en auditorías.

**Criterios de Aceptación:**
- Debe existir un botón "Exportar a Excel" que descargue los datos mostrados en pantalla (respetando los filtros aplicados) en formato `.xlsx`.
- Debe existir un botón "Exportar a PDF" que genere un documento formal y paginado con la información visible en la tabla.
- Los totales del saldo físico y valorizado final deben destacarse al final del reporte exportado.

---

## Historia de Usuario: SCRUM-8
### Considerar Fecha de Ingreso a Almacén en lugar de Fecha de Operación
**Como** Analista de Inventario
**Quiero** que la fecha principal a mostrar en el módulo de Kardex sea la fecha real en la que el producto ingresó o salió físicamente del almacén
**Para** mantener un estricto orden cronológico en el costeo, evitando distorsiones si el registro en el sistema (fecha de creación) se hace días después de que la mercadería llegó.

**Criterios de Aceptación:**
- La tabla del Kardex mostrará de forma prominente la "Fecha de Ingreso/Salida" (fecha real del movimiento) por encima de la "Fecha de Registro" (`created_at` del sistema).
- El cálculo PEPS o Promedio Ponderado para costear las salidas debe basarse estrictamente en la cronología de la "Fecha de Ingreso/Salida".
- Al registrar cualquier movimiento manual o recepción, el usuario debe poder confirmar o editar esta "Fecha de Movimiento".
